<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\BarcodeHistory;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use App\Services\ActivityLogService;
use App\Services\BarcodeService;

class ProductController extends Controller
{
    protected $activityLogService;
    protected $barcodeService;

    public function __construct(ActivityLogService $activityLogService, BarcodeService $barcodeService)
    {
        $this->activityLogService = $activityLogService;
        $this->barcodeService = $barcodeService;
    }
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'variants']);

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('variants', function($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereHas('variants', function ($q) {
                $q->lowStock();
            });
        }

        $products = $query->latest()->paginate(20);

        if ($request->ajax()) {
            return response()->json($products);
        }

        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'nullable|string',
            'variants.*.color' => 'nullable|string',
            'variants.*.purchase_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'required|numeric|min:0',
            'variants.*.minimum_sale_price' => 'required|numeric|min:0',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.sku' => 'required|unique:product_variants,sku',
            'image' => 'nullable|image|max:2048',
        ]);

        return DB::transaction(function () use ($request) {
            $imagePath = $request->file('image') ? $request->file('image')->store('products', 'public') : null;

            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'brand' => $request->brand,
                'fabric_type' => $request->fabric_type,
                'description' => $request->description,
                'image' => $imagePath,
                'status' => 1,
            ]);

            foreach ($request->variants as $variantData) {
                if ($variantData['sale_price'] < $variantData['minimum_sale_price']) {
                    throw ValidationException::withMessages([
                        'variants' => "Sale price for SKU {$variantData['sku']} cannot be less than minimum sale price."
                    ]);
                }

                // Auto-generate barcode if empty
                if (empty($variantData['barcode'])) {
                    $variantData['barcode'] = $this->barcodeService->generateUniqueBarcode();
                }

                $product->variants()->create($variantData);
            }

            // Log activity
            $this->activityLogService->log('inventory', 'create', "Created product: {$product->name} with " . count($request->variants) . " variants", $product->id);

            return response()->json($product->load('variants'), 201);
        });
    }

    public function show(Product $product)
    {
        return response()->json($product->load('variants'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.size' => 'nullable|string',
            'variants.*.color' => 'nullable|string',
            'variants.*.purchase_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'required|numeric|min:0',
            'variants.*.minimum_sale_price' => 'required|numeric|min:0',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.sku' => 'required|string|max:100',
        ]);

        // Manually check SKU uniqueness to allow existing SKUs for this product but block others
        $skus = collect($request->variants)->pluck('sku');
        if ($skus->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['variants' => 'Duplicate SKUs found in the request.']);
        }

        foreach ($request->variants as $vData) {
            $exists = ProductVariant::where('sku', $vData['sku'])
                ->where('product_id', '!=', $product->id)
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages(['variants' => "SKU {$vData['sku']} is already taken by another product."]);
            }
        }

        return DB::transaction(function () use ($request, $product) {
            $product->update($request->only(['name', 'category_id', 'subcategory_id', 'brand', 'fabric_type', 'description', 'status']));

            if ($request->file('image')) {
                if ($product->image) Storage::disk('public')->delete($product->image);
                $product->update(['image' => $request->file('image')->store('products', 'public')]);
            }

            $existingIds = $product->variants()->pluck('id')->toArray();
            $updatedIds = [];

            foreach ($request->variants as $variantData) {
                if ($variantData['sale_price'] < $variantData['minimum_sale_price']) {
                    throw ValidationException::withMessages([
                        'variants' => "Sale price for SKU {$variantData['sku']} cannot be less than minimum sale price."
                    ]);
                }

                if (isset($variantData['id'])) {
                    $variant = ProductVariant::findOrFail($variantData['id']);
                    $oldBarcode = $variant->barcode;
                    
                    // If barcode is empty string, regenerate it
                    if (isset($variantData['barcode']) && $variantData['barcode'] === '') {
                        $variantData['barcode'] = $this->barcodeService->generateUniqueBarcode();
                    }

                    // Validate barcode if provided and changed
                    if (!empty($variantData['barcode']) && $variantData['barcode'] !== $oldBarcode) {
                        if (!$this->barcodeService->validateBarcode($variantData['barcode'], $variant->id)) {
                            throw ValidationException::withMessages(['variants' => "Barcode {$variantData['barcode']} is already in use."]);
                        }
                    }

                    $variant->update($variantData);

                    // Track barcode change
                    if (!empty($variantData['barcode']) && $variantData['barcode'] !== $oldBarcode) {
                        $this->barcodeService->registerChange($variant, $oldBarcode, $variantData['barcode']);
                    }
                    
                    $updatedIds[] = $variant->id;
                } else {
                    // Auto-generate barcode for new variant if empty
                    if (empty($variantData['barcode'])) {
                        $variantData['barcode'] = $this->barcodeService->generateUniqueBarcode();
                    }

                    $newVariant = $product->variants()->create($variantData);
                    $updatedIds[] = $newVariant->id;
                }
            }

            // Remove variants not in the update request
            $toDelete = array_diff($existingIds, $updatedIds);
            ProductVariant::whereIn('id', $toDelete)->delete();

            // Log activity
            $this->activityLogService->log('inventory', 'update', "Updated product: {$product->name}. Updates: " . count($updatedIds) . " variants, " . count($toDelete) . " variants removed.", $product->id);

            return response()->json($product->load('variants'));
        });
    }

    public function destroy(Product $product)
    {
        $productName = $product->name;
        $product->delete(); // Soft delete
        $product->variants()->delete(); // Soft delete variants

        // Log activity
        $this->activityLogService->log('inventory', 'delete', "Deleted product: {$productName}", $product->id);

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function showPurchasePrice($id)
    {
        $variant = ProductVariant::findOrFail($id);
        return response()->json([
            'sku' => $variant->sku,
            'purchase_price' => $variant->purchase_price
        ]);
    }

    /**
     * Return the Product Catalog HTML view (data loaded client-side).
     */
    public function indexView()
    {
        $products = Product::latest()->paginate(20);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Return the Barcode Management HTML view.
     */
    public function barcodeView()
    {
        return view('barcodes.index');
    }

    /**
     * Return barcode change history as JSON.
     */
    public function barcodeHistory(Request $request)
    {
        $history = BarcodeHistory::with(['variant.product'])
            ->latest()
            ->paginate(30);
        return response()->json($history);
    }

    /**
     * Return the Barcode Label Print HTML view.
     */
    public function barcodePrintView()
    {
        return view('barcodes.print');
    }

    /**
     * Return all variants with barcode info as JSON for the Barcode Manager.
     */
    public function apiBarcodesIndex(Request $request)
    {
        $query = ProductVariant::with(['product.category'])
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'));

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $variants = $query->latest()->paginate(30);
        return response()->json($variants);
    }
}
