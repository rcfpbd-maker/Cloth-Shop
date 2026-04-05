<?php

namespace App\Console\Commands\ERP;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\ReturnItem;
use App\Services\InvoiceService;

class SyncInvoices extends Command
{
    protected $signature = 'ERP:sync-invoices';
    protected $description = 'Sync existing sales, purchases, and returns to the centralized invoices table';

    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
    }

    public function handle()
    {
        $this->info('Starting Invoice Sync...');

        // Sync Sales
        $sales = Sale::all();
        $this->info('Syncing ' . $sales->count() . ' Sales...');
        foreach ($sales as $sale) {
            $this->invoiceService->createFromSale($sale);
        }

        // Sync Purchases
        $purchases = Purchase::all();
        $this->info('Syncing ' . $purchases->count() . ' Purchases...');
        foreach ($purchases as $purchase) {
            $this->invoiceService->createFromPurchase($purchase);
        }

        // Sync Returns
        $returns = ReturnItem::all();
        $this->info('Syncing ' . $returns->count() . ' Returns...');
        foreach ($returns as $return) {
            $this->invoiceService->createFromReturn($return);
        }

        $this->info('Invoice Sync Completed successfully!');
    }
}
