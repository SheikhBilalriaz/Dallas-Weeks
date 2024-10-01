<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
        .invoice-header {
            text-align: center;
        }
        .invoice-details {
            margin-top: 20px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .items-table th, .items-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1 class="invoice-header">Subscription Invoice</h1>

    <div class="invoice-details">
        <p><strong>Invoice #: </strong>{{ $invoice->id }}</p>
        <p><strong>Date: </strong>{{ $invoiceDate }}</p>
        <p><strong>Due Date: </strong>{{ $dueDate }}</p>
        <p><strong>Client Name: </strong>{{ $user->name }}</p>
        <p><strong>Email: </strong>{{ $user->email }}</p>
    </div>

    <h2>Subscription Details</h2>
    <p><strong>Subscription Plan: </strong>{{ $subscription->plan->nickname }}</p>
    <p><strong>Amount: </strong>${{ number_format($invoice->amount_due / 100, 2) }}</p>
    <p><strong>Status: </strong>{{ ucfirst($invoice->status) }}</p>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->amount / 100, 2) }}</td>
                    <td>${{ number_format(($item->amount / 100) * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td>${{ number_format($invoice->amount_due / 100, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
