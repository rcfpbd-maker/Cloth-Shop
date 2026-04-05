<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Services\BarcodeService;

class ProductVariantObserver
{
    protected $barcodeService;

    public function __construct(BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $productVariant): void
    {
        if ($productVariant->wasChanged('barcode')) {
            $this->barcodeService->registerChange(
                $productVariant,
                $productVariant->getOriginal('barcode'),
                $productVariant->barcode
            );
        }
    }
}
