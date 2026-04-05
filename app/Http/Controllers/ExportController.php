<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\ExportLog;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export Sales
     */
    public function exportSales(Request $request)
    {
        $request->validate(['format' => 'required|in:pdf,excel']);

        $query = Sale::with(['customer', 'items']);
        $this->applyDateFilters($query, $request);

        if ($request->format == 'excel') {
            $data = $query->get()->map(function($sale) {
                return [
                    'Invoice' => $sale->invoice_no,
                    'Date' => $sale->sale_date,
                    'Customer' => $sale->customer->name ?? 'Walking Customer',
                    'Subtotal' => $sale->subtotal,
                    'Discount' => $sale->discount,
                    'Total' => $sale->total_amount,
                    'Paid' => $sale->paid_amount,
                    'Due' => $sale->due_amount,
                ];
            });
            return $this->exportService->exportToCsv($data, 'sales_report_' . date('Ymd'), 'sales', $request->all());
        }

        $sales = $query->get();
        return $this->exportService->exportToPdf('exports.sales', compact('sales'), 'sales_report_' . date('Ymd'), 'sales', $request->all());
    }

    /**
     * Export Purchases
     */
    public function exportPurchases(Request $request)
    {
        $request->validate(['format' => 'required|in:pdf,excel']);

        $query = Purchase::with(['supplier']);
        $this->applyDateFilters($query, $request);

        if ($request->format == 'excel') {
            $data = $query->get()->map(function($purchase) {
                return [
                    'Invoice' => $purchase->invoice_no,
                    'Date' => $purchase->purchase_date,
                    'Supplier' => $purchase->supplier->name ?? 'N/A',
                    'Total' => $purchase->total_amount,
                    'Paid' => $purchase->paid_amount,
                    'Due' => $purchase->due_amount,
                ];
            });
            return $this->exportService->exportToCsv($data, 'purchase_report_' . date('Ymd'), 'purchases', $request->all());
        }

        $purchases = $query->get();
        return $this->exportService->exportToPdf('exports.purchases', compact('purchases'), 'purchase_report_' . date('Ymd'), 'purchases', $request->all());
    }

    /**
     * Export Expenses
     */
    public function exportExpenses(Request $request)
    {
        $request->validate(['format' => 'required|in:pdf,excel']);

        $query = Expense::with(['category']);
        $this->applyDateFilters($query, $request);

        if ($request->format == 'excel') {
            $data = $query->get()->map(function($expense) {
                return [
                    'Date' => $expense->expense_date,
                    'Category' => $expense->category->name ?? 'N/A',
                    'Amount' => $expense->amount,
                    'Description' => $expense->description,
                ];
            });
            return $this->exportService->exportToCsv($data, 'expense_report_' . date('Ymd'), 'expenses', $request->all());
        }

        $expenses = $query->get();
        return $this->exportService->exportToPdf('exports.expenses', compact('expenses'), 'expense_report_' . date('Ymd'), 'expenses', $request->all());
    }

    /**
     * Export Customers
     */
    public function exportCustomers(Request $request)
    {
        $request->validate(['format' => 'required|in:pdf,excel']);

        $query = Customer::query();

        if ($request->format == 'excel') {
            $data = $query->get()->map(function($customer) {
                return [
                    'Name' => $customer->name,
                    'Phone' => $customer->phone,
                    'Address' => $customer->address,
                    'Credit Limit' => $customer->credit_limit,
                    'Due Amount' => $customer->previous_due,
                ];
            });
            // ExportService uses 'exports.reports' prefix or dynamically generating CSV 
            return $this->exportService->exportToCsv($data, 'customers_report_' . date('Ymd'), 'customers', $request->all());
        }

        $items = $query->get();
        // Fallback to basic data view if specific template doesn't exist
        return $this->exportService->exportToPdf('exports.customers', compact('items'), 'customers_report_' . date('Ymd'), 'customers', $request->all());
    }

    /**
     * Export Products
     */
    public function exportProducts(Request $request)
    {
        $request->validate(['format' => 'required|in:pdf,excel']);

        $query = ProductVariant::with('product');

        if ($request->format == 'excel') {
            $data = $query->get()->map(function($variant) {
                return [
                    'Product Name' => $variant->product->name,
                    'Category' => $variant->product->category->name ?? 'N/A',
                    'SKU' => $variant->sku,
                    'Barcode' => $variant->barcode,
                    'Price' => $variant->price,
                    'Stock' => $variant->stock_quantity,
                ];
            });
            return $this->exportService->exportToCsv($data, 'products_report_' . date('Ymd'), 'products', $request->all());
        }

        $items = $query->get();
        return $this->exportService->exportToPdf('exports.products', compact('items'), 'products_report_' . date('Ymd'), 'products', $request->all());
    }

    /**
     * Get Export History
     */
    public function history()
    {
        $history = ExportLog::with('user')->latest()->limit(10)->get();
        return response()->json($history);
    }

    /**
     * Helper to apply common filters
     */
    protected function applyDateFilters($query, $request)
    {
        if ($request->has('from_date')) {
            $query->whereDate($request->column ?? 'created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate($request->column ?? 'created_at', '<=', $request->to_date);
        }
    }
}
