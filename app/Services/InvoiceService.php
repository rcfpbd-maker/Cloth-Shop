<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\ReturnItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Generate a unique invoice number
     */
    public function generateInvoiceNumber($type)
    {
        $prefix = match ($type) {
            'sale' => 'SL',
            'purchase' => 'PR',
            'return' => 'RT',
            default => 'INV'
        };

        $date = Carbon::now()->format('Ymd');
        $count = Invoice::whereDate('created_at', Carbon::today())->count() + 1;
        
        $number = sprintf("%s-%s-%04d", $prefix, $date, $count);
        
        // Ensure uniqueness (in case of race conditions, though unlikely with serialized sequences)
        while (Invoice::where('invoice_number', $number)->exists()) {
            $count++;
            $number = sprintf("%s-%s-%04d", $prefix, $date, $count);
        }

        return $number;
    }

    /**
     * Map a Sale to an Invoice
     */
    public function createFromSale(Sale $sale)
    {
        return DB::transaction(function () use ($sale) {
            // Check if already exists
            $existing = Invoice::where('type', 'sale')->where('reference_id', $sale->id)->first();
            if ($existing) return $existing;

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber('sale'),
                'type' => 'sale',
                'reference_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'total_amount' => $sale->total_amount,
                'discount' => $sale->discount,
                'paid_amount' => $sale->paid_amount,
                'due_amount' => $sale->due_amount,
                'payment_method_id' => $sale->payment_method_id,
                'created_by' => $sale->created_by,
            ]);

            foreach ($sale->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'total' => $item->total,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Map a Purchase to an Invoice
     */
    public function createFromPurchase(Purchase $purchase)
    {
        return DB::transaction(function () use ($purchase) {
            $existing = Invoice::where('type', 'purchase')->where('reference_id', $purchase->id)->first();
            if ($existing) return $existing;

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber('purchase'),
                'type' => 'purchase',
                'reference_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'total_amount' => $purchase->total_amount,
                'discount' => $purchase->discount,
                'paid_amount' => $purchase->paid_amount,
                'due_amount' => $purchase->due_amount,
                'payment_method_id' => $purchase->payment_method_id,
                'created_by' => $purchase->created_by,
            ]);

            foreach ($purchase->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'total' => $item->total,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Map a Return to an Invoice
     */
    public function createFromReturn(ReturnItem $return)
    {
        return DB::transaction(function () use ($return) {
            $existing = Invoice::where('type', 'return')->where('reference_id', $return->id)->first();
            if ($existing) return $existing;

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber('return'),
                'type' => 'return',
                'reference_id' => $return->id,
                'customer_id' => $return->sale->customer_id ?? null,
                'supplier_id' => $return->purchase->supplier_id ?? null,
                'total_amount' => $return->refund_amount,
                'discount' => 0,
                'paid_amount' => $return->refund_amount,
                'due_amount' => 0,
                'created_by' => $return->created_by,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_variant_id' => $return->product_variant_id,
                'quantity' => $return->quantity,
                'price' => $return->refund_amount / ($return->quantity ?: 1),
                'discount' => 0,
                'total' => $return->refund_amount,
            ]);

            return $invoice;
        });
    }
}
