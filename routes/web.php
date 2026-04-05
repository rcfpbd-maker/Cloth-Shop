<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\DayClosingController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\HalkhataController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');
    Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('dashboard.charts')->middleware('permission:dashboard.view');

    // Inventory & Catalog
    Route::resource('categories', CategoryController::class)->middleware('permission:categories.manage');

    // Product HTML views (data loaded client-side via the JSON API below)
    Route::get('products', [ProductController::class, 'indexView'])->name('products.index')->middleware('permission:products.view');

    // Product JSON API (used by the frontend JS)
    Route::get('api/products', [ProductController::class, 'index'])->middleware('permission:products.view');
    Route::post('api/products', [ProductController::class, 'store'])->middleware('permission:products.manage');
    Route::get('api/products/{product}', [ProductController::class, 'show'])->middleware('permission:products.view');
    Route::put('api/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.manage');
    Route::delete('api/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.manage');
    Route::get('products/{product}/purchase-price', [ProductController::class, 'showPurchasePrice'])
        ->middleware('permission:products.view_purchase_price');

    // Barcode Management
    Route::get('barcodes', [ProductController::class, 'barcodeView'])->name('barcodes.index')->middleware('permission:products.view');
    Route::get('barcodes/history', [ProductController::class, 'barcodeHistory'])->name('barcodes.history')->middleware('permission:products.view');
    Route::get('barcodes/print', [ProductController::class, 'barcodePrintView'])->name('barcodes.print')->middleware('permission:products.view');

    // Barcode inline update (via product update endpoint)
    Route::get('api/barcodes', [ProductController::class, 'apiBarcodesIndex'])->middleware('permission:products.view');

    // People
    Route::resource('suppliers', SupplierController::class)->middleware('permission:suppliers.manage');
    Route::resource('customers', CustomerController::class)->middleware('permission:customers.view');
    Route::get('customers/{customer}/ledger', [CustomerLedgerController::class, 'index'])->middleware('permission:customers.view_ledger');
    Route::post('customers/payment', [CustomerLedgerController::class, 'storePayment'])->middleware('permission:customers.make_payment');
    Route::get('halkhata/reset', [HalkhataController::class, 'index'])->name('halkhata.index')->middleware('permission:customers.halkhata_reset');
    Route::post('halkhata/reset', [HalkhataController::class, 'reset'])->middleware('permission:customers.halkhata_reset');

    // Reports
    Route::group(['prefix' => 'reports', 'middleware' => 'permission:reports.view'], function () {
        Route::get('summary', [ReportController::class, 'summary']);
        
        // Sales Reports
        Route::get('sales/daily', [SalesReportController::class, 'daily'])->middleware('permission:reports.sales');
        Route::get('sales/monthly', [SalesReportController::class, 'monthly'])->middleware('permission:reports.sales');
        Route::get('sales/top-products', [SalesReportController::class, 'topProducts'])->middleware('permission:reports.sales');
        Route::get('sales/returns', [SalesReportController::class, 'returns'])->middleware('permission:reports.sales');
        
        // Inventory Reports
        Route::get('inventory/stock', [InventoryReportController::class, 'stockReport'])->middleware('permission:reports.inventory');
        Route::get('inventory/low-stock', [InventoryReportController::class, 'lowStock'])->middleware('permission:reports.inventory');
        Route::get('inventory/dead-stock', [InventoryReportController::class, 'deadStock'])->middleware('permission:reports.inventory');
        
        // Finance Reports
        Route::get('finance/profit-loss', [FinanceReportController::class, 'profitLoss'])->middleware('permission:reports.finance');
        Route::get('finance/customer-dues', [FinanceReportController::class, 'customerDues'])->middleware('permission:reports.finance');
        Route::get('finance/payment-methods', [FinanceReportController::class, 'paymentMethods'])->middleware('permission:reports.finance');
    });

    // Transactions
    Route::get('api/purchases/init', [PurchaseController::class, 'initData'])->middleware('permission:purchases.manage');
    Route::resource('purchases', PurchaseController::class)->middleware('permission:purchases.view');
    
    Route::post('purchase-returns', [\App\Http\Controllers\PurchaseReturnController::class, 'store'])->middleware('permission:purchases.returns');
    Route::get('purchase-returns', [\App\Http\Controllers\PurchaseReturnController::class, 'index'])->middleware('permission:purchases.view');

    Route::resource('sales', SaleController::class)->middleware('permission:sales.view');
    Route::get('pos', [POSController::class, 'indexView'])->middleware('permission:sales.create')->name('pos.index');
    Route::get('api/pos/init', [POSController::class, 'index'])->middleware('permission:sales.create');
    Route::get('pos/search', [POSController::class, 'search'])->middleware('permission:sales.create');
    Route::post('sales/return', [SaleReturnController::class, 'store'])->middleware('permission:sales.return');

    // Finance & Accounts
    Route::get('cashbook', [CashbookController::class, 'index'])->middleware('permission:accounts.cashbook');
    Route::resource('expenses', ExpenseController::class)->middleware('permission:accounts.expense_manage');
    Route::get('expenses-categories', [ExpenseController::class, 'categories'])->middleware('permission:accounts.expense_manage');
    
    Route::get('day-closing/preview', [DayClosingController::class, 'preview'])->middleware('permission:accounts.daily_closing');
    Route::resource('day-closing', DayClosingController::class)->only(['index', 'store'])->middleware('permission:accounts.daily_closing');
    
    Route::get('accounts/summary', [AccountController::class, 'summary'])->middleware('permission:accounts.view');
    Route::resource('payments', PaymentController::class)->middleware('permission:payments.manage');

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->middleware('permission:reports.sales')->name('reports.sales');
        Route::get('/stock', [ReportController::class, 'stockReport'])->middleware('permission:reports.stock')->name('reports.stock');
        Route::get('/finance', [ReportController::class, 'financeReport'])->middleware('permission:reports.finance')->name('reports.finance');
    });

    // Invoices
    Route::group(['prefix' => 'invoices', 'middleware' => 'permission:sales.view|purchases.view'], function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/{id}', [InvoiceController::class, 'show']);
        Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    });

    // Notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/all', [NotificationController::class, 'all']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Backup System
    Route::get('/backups', [BackupController::class, 'index'])->middleware('permission:backups.view');
    Route::post('/backups', [BackupController::class, 'store'])->middleware('permission:backups.create');
    Route::get('/backups/{id}/download', [BackupController::class, 'download'])->middleware('permission:backups.download');
    Route::post('/backups/{id}/restore', [BackupController::class, 'restore'])->middleware('permission:backups.restore');
    Route::delete('/backups/{id}', [BackupController::class, 'destroy'])->middleware('permission:backups.delete');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('permission:activity_logs.view');

    // Advanced Search
    Route::get('/search/global', [SearchController::class, 'globalSearch']);
    Route::get('/search/products', [SearchController::class, 'searchProducts']);

    // Exports
    Route::group(['prefix' => 'exports', 'middleware' => 'permission:reports.export'], function () {
        Route::get('/sales', [ExportController::class, 'exportSales']);
        Route::get('/purchases', [ExportController::class, 'exportPurchases']);
        Route::get('/expenses', [ExportController::class, 'exportExpenses']);
        Route::get('/history', [ExportController::class, 'history']);
    });

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
