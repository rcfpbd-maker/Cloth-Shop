<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create a notification for specific users or roles
     */
    public function notify($module, $type, $message, $userIds = [])
    {
        if (empty($userIds)) {
            // Default: notify all admins/managers if no specific user IDs provided
            $userIds = User::whereHas('role', function($q) {
                $q->whereIn('name', ['Admin', 'Manager']);
            })->pluck('id')->toArray();
        }

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'module' => $module,
                'type' => $type,
                'message' => $message,
            ]);
        }
    }

    /**
     * Notify about low stock
     */
    public function notifyLowStock($variant)
    {
        $message = "Low stock alert: " . ($variant->product->name ?? $variant->sku) . " has only " . $variant->stock_quantity . " items left.";
        $this->notify('inventory', 'low_stock', $message);
    }

    /**
     * Notify about customer due
     */
    public function notifyCustomerDue($customer, $amount)
    {
        $message = "Customer due: " . $customer->name . " has a new due of " . number_format($amount, 2) . ".";
        $this->notify('sales', 'due_customer', $message);
    }

    /**
     * Notify about supplier due
     */
    public function notifySupplierDue($supplier, $amount)
    {
        $message = "Supplier due: " . $supplier->name . " has a new payable of " . number_format($amount, 2) . ".";
        $this->notify('purchases', 'due_supplier', $message);
    }
}
