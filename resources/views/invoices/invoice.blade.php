<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; margin: 0; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; }
        .totals { float: right; width: 250px; }
        .totals div { display: flex; justify-content: space-between; padding: 5px 0; }
        .total-bold { font-weight: bold; border-top: 1px solid #000; }
        .footer { clear: both; margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
        @media print {
            .no-print { display: none; }
            .invoice-box { border: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="no-print" style="text-align: right; margin-bottom: 15px;">
            <button onclick="window.print()" style="padding: 10px; cursor: pointer;">Print Invoice</button>
        </div>

        <div class="header">
            <div>
                <h1 style="margin: 0; color: #333;">CLOTH SHOP ERP</h1>
                <p>123 Fashion Street, Avenue 5<br>Phone: +880 1234-567890</p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0; color: #666;">INVOICE</h2>
                <p>#{{ $invoice->invoice_number }}<br>Date: {{ $invoice->created_at->format('d M, Y') }}</p>
            </div>
        </div>

        <div class="info">
            <div>
                <strong>Bill To:</strong><br>
                @if($invoice->type == 'purchase')
                    {{ $invoice->supplier->name ?? 'N/A' }}<br>
                    {{ $invoice->supplier->address ?? '' }}
                @else
                    {{ $invoice->customer->name ?? 'N/A' }}<br>
                    {{ $invoice->customer->address ?? '' }}
                @endif
            </div>
            <div style="text-align: right;">
                <strong>Type:</strong> {{ ucfirst($invoice->type) }}<br>
                <strong>Status:</strong> {{ $invoice->due_amount > 0 ? 'Partial/Due' : 'Paid' }}
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->variant->product->name ?? $item->variant->sku }} ({{ $item->variant->size }}/{{ $item->variant->color }})</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ number_format($item->discount, 2) }}</td>
                    <td>{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal:</span> <span>{{ number_format($invoice->total_amount + $invoice->discount, 2) }}</span></div>
            <div><span>Invoice Discount:</span> <span>-{{ number_format($invoice->discount, 2) }}</span></div>
            <div class="total-bold"><span>Total:</span> <span>{{ number_format($invoice->total_amount, 2) }}</span></div>
            <div><span>Paid:</span> <span>{{ number_format($invoice->paid_amount, 2) }}</span></div>
            <div style="color: red;"><span>Due Amount:</span> <span>{{ number_format($invoice->due_amount, 2) }}</span></div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Signature: __________________________</p>
        </div>
    </div>
</body>
</html>
