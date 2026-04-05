<!DOCTYPE html>
<html>
<head>
    <title>Expense Report</title>
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
        <h1>Expense Report</h1>
        <p>Generated on: {{ date('d M, Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date }}</td>
                <td>{{ $expense->category->name ?? 'N/A' }}</td>
                <td>{{ number_format($expense->amount, 2) }}</td>
                <td>{{ $expense->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
