<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $owner = \App\Models\Role::updateOrCreate(['id' => 1], ['name' => 'Owner', 'description' => 'Global Administrator']);
        $manager = \App\Models\Role::updateOrCreate(['id' => 2], ['name' => 'Manager', 'description' => 'Store Manager']);
        $salesman = \App\Models\Role::updateOrCreate(['id' => 3], ['name' => 'Salesman', 'description' => 'Sales Staff']);
        $accountant = \App\Models\Role::updateOrCreate(['id' => 4], ['name' => 'Accountant', 'description' => 'Financial Staff']);

        // Get Permissions
        $allPermissions = \App\Models\Permission::all();
        
        // Assign All to Owner
        $owner->permissions()->sync($allPermissions->pluck('id'));

        // Assign to Manager (Most but not sensitive profit/delete)
        $managerPerms = \App\Models\Permission::whereNotIn('slug', [
            'reports.finance',
            'sales.view_profit'
        ])->pluck('id');
        $manager->permissions()->sync($managerPerms);

        // Assign to Salesman (POS, View products/customers/purchases only)
        $salesmanPerms = \App\Models\Permission::whereIn('slug', [
            'dashboard.view',
            'sales.create',
            'sales.view',
            'sales.discount_apply',
            'sales.return',
            'sales.payment',
            'products.view',
            'customers.view',
            'customers.manage',
            'customers.view_ledger',
            'customers.make_payment',
            'purchases.view',
            'profile.edit'
        ])->pluck('id');
        $salesman->permissions()->sync($salesmanPerms);

        // Assign to Accountant (Finance, Expenses, Reports)
        $accountantPerms = \App\Models\Permission::whereIn('slug', [
            'dashboard.view',
            'expenses.manage',
            'payments.manage',
            'purchases.approve',
            'accounts.view',
            'accounts.cashbook',
            'accounts.expense_manage',
            'accounts.daily_closing',
            'accounts.profit_view',
            'reports.view',
            'reports.sales',
            'reports.inventory',
            'reports.finance',
            'profile.edit'
        ])->pluck('id');
        $accountant->permissions()->sync($accountantPerms);
    }
}
