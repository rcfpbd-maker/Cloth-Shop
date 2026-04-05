<!DOCTYPE html>
<html>
<head>
    <title>Purchase Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Report</h1>
        <p>Generated on: {{ date('d M, Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
            <tr>
                <td>{{ $purchase->invoice_no }}</td>
                <td>{{ $purchase->purchase_date }}</td>
                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                <td>{{ number_format($purchase->total_amount, 2) }}</td>
                <td>{{ number_format($purchase->paid_amount, 2) }}</td>
                <td>{{ number_format($purchase->due_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
