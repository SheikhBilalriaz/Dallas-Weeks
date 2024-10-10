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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            padding: 20px;
            border-bottom: 2px solid #000;
        }
        .header img {
            max-width: 75px;
            height: auto;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 24px;
        }
        .invoice-details {
            margin: 20px;
        }
        .subscription-details {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="header row">
        <img src="{{ asset('assets/images/logo.png') }}">
        <h1>Networked</h1>
    </div>
    <h1 class="invoice-header">Subscription Invoice</h1>

    <div class="invoice-details">
        <p><strong>Invoice #: </strong>{{ $invoiceId }}</p>
        <p><strong>Date of issue: </strong>{{ $invoiceDate }}</p>
        <p><strong>Due Date: </strong>{{ $dueDate }}</p>
        <p><strong>Created by: </strong>{{ $user->name }}</p>
        <p><strong>Email: </strong>{{ $user->email }}</p>
    </div>
    <hr>
    <div class="subscription-details">
        <h2>Subscription Details</h2>
        <p><strong>Seat: </strong>{{ $company_info->name }}</p>
        <p><strong>Amount: </strong>${{ number_format($invoice->amount_due / 100, 2) }}</p>
        <p><strong>Status: </strong>{{ ucfirst($invoice->status) }}</p>
        <p><strong>Starting Month: </strong>{{ $billingStart }}</p>
        <p><strong>Ending Month: </strong>{{ $billingEnd }}</p>
    </div>
    <hr>
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
