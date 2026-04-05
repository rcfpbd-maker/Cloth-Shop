<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * List all invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'supplier', 'creator']);

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $invoices = $query->latest()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($invoices);
        }

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Return the master view (for SPA-like feel, if needed)
     */
    public function indexView()
    {
        return view('invoices.index');
    }

    /**
     * Show invoice details
     */
    public function show($id)
    {
        $invoice = Invoice::with(['items.variant.product', 'customer', 'supplier', 'creator', 'paymentMethod'])->findOrFail($id);
        return response()->json($invoice);
    }

    /**
     * Render a print-friendly view of the invoice
     */
    public function print($id)
    {
        $invoice = Invoice::with(['items.variant.product', 'customer', 'supplier', 'creator', 'paymentMethod'])->findOrFail($id);
        
        return view('invoices.invoice', compact('invoice'));
    }

    /**
     * Download Invoice as PDF (Fallback to print view if package missing)
     */
    public function download($id)
    {
        $invoice = Invoice::with(['items.variant.product', 'customer', 'supplier', 'creator', 'paymentMethod'])->findOrFail($id);
        
        // Check if DomPDF is available
        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.invoice', compact('invoice'));
            return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
        }

        // Fallback to print-friendly HTML view
        return view('invoices.invoice', compact('invoice'));
    }
}
