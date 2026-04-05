<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view'],
            ['name' => 'View Low Stock Alert', 'slug' => 'dashboard.low_stock'],
            ['name' => 'View Dashboard Profit', 'slug' => 'dashboard.profit_view'],
            
            // Products
            ['name' => 'View Products', 'slug' => 'products.view'],
            ['name' => 'Create Products', 'slug' => 'products.create'],
            ['name' => 'Edit Products', 'slug' => 'products.edit'],
            ['name' => 'Delete Products', 'slug' => 'products.delete'],
            ['name' => 'View Product Purchase Price', 'slug' => 'products.view_purchase_price'],
            ['name' => 'View Product Wholesale Price', 'slug' => 'products.view_wholesale_price'],
            ['name' => 'Adjust Product Stock', 'slug' => 'products.adjust_stock'],
            
            // Categories
            ['name' => 'Manage Categories', 'slug' => 'categories.manage'],
            
            // Suppliers
            ['name' => 'Manage Suppliers', 'slug' => 'suppliers.manage'],
            
            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view'],
            ['name' => 'Manage Customers', 'slug' => 'customers.manage'],
            ['name' => 'View Customer Credit', 'slug' => 'customers.view_credit'],
            ['name' => 'View Customer Ledger', 'slug' => 'customers.view_ledger'],
            ['name' => 'Make Customer Payment', 'slug' => 'customers.make_payment'],
            ['name' => 'Halkhata Yearly Reset', 'slug' => 'customers.halkhata_reset'],
            ['name' => 'Set Customer Credit Limit', 'slug' => 'customers.set_credit_limit'],
            
            // Purchases
            ['name' => 'View Purchases', 'slug' => 'purchases.view'],
            ['name' => 'Manage Purchases', 'slug' => 'purchases.manage'],
            ['name' => 'Manage Purchase Returns', 'slug' => 'purchases.returns'],
            ['name' => 'Approve Purchases', 'slug' => 'purchases.approve'],
            
            // Sales
            ['name' => 'View Sales', 'slug' => 'sales.view'],
            ['name' => 'Create Sale', 'slug' => 'sales.create'],
            ['name' => 'Apply Discount', 'slug' => 'sales.discount_apply'],
            ['name' => 'Process Return', 'slug' => 'sales.return'],
            ['name' => 'Add Payment', 'slug' => 'sales.payment'],
            ['name' => 'Manage Returns', 'slug' => 'sales.returns'],
            ['name' => 'View Sale Profit', 'slug' => 'sales.view_profit'],
            
            // Accounts & Finance
            ['name' => 'View High Level Accounts', 'slug' => 'accounts.view'],
            ['name' => 'View Cashbook', 'slug' => 'accounts.cashbook'],
            ['name' => 'Manage Expenses', 'slug' => 'accounts.expense_manage'],
            ['name' => 'Daily Closing', 'slug' => 'accounts.daily_closing'],
            ['name' => 'View Detailed Profit', 'slug' => 'accounts.profit_view'],
            
            // Payments
            ['name' => 'Manage Payments', 'slug' => 'payments.manage'],
            
            // Reports
            ['name' => 'View Reports Dashboard', 'slug' => 'reports.view'],
            ['name' => 'View Sales Reports', 'slug' => 'reports.sales'],
            ['name' => 'View Inventory Reports', 'slug' => 'reports.inventory'],
            ['name' => 'View Finance Reports', 'slug' => 'reports.finance'],
            ['name' => 'Export Reports', 'slug' => 'reports.export'],
            
            // Profile
            ['name' => 'Edit Profile', 'slug' => 'profile.edit'],
            ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view'],
            ['name' => 'View Backups', 'slug' => 'backups.view'],
            ['name' => 'Create Backup', 'slug' => 'backups.create'],
            ['name' => 'Download Backup', 'slug' => 'backups.download'],
            ['name' => 'Restore Backup', 'slug' => 'backups.restore'],
            ['name' => 'Delete Backup', 'slug' => 'backups.delete'],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
