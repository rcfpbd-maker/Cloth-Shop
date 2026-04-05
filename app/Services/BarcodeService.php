<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\BarcodeHistory;
use Illuminate\Support\Str;

class BarcodeService
{
    /**
     * Generate a unique barcode for a product variant.
     * format: e.g. 13 digits (EAN-13 style or custom)
     */
    public function generateUniqueBarcode()
    {
        do {
            // Generate a random 12-digit number (we can add a checksum if needed)
            $barcode = '20' . Str::random(10); // Simple random string, or we can use random_int
            // Let's use numeric for better scanning compatibility
            $barcode = '20' . str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (ProductVariant::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Validate a barcode for uniqueness.
     */
    public function validateBarcode($barcode, $currentVariantId = null)
    {
        $query = ProductVariant::where('barcode', $barcode);
        if ($currentVariantId) {
            $query->where('id', '!=', $currentVariantId);
        }
        return !$query->exists();
    }

    /**
     * Register a barcode change in history.
     */
    public function registerChange($variant, $oldBarcode, $newBarcode)
    {
        if ($oldBarcode === $newBarcode) return;

        BarcodeHistory::create([
            'product_variant_id' => $variant->id,
            'old_barcode' => $oldBarcode,
            'new_barcode' => $newBarcode,
            'changed_by' => auth()->id() ?? 1, // Fallback to system user if needed
        ]);
    }
}
