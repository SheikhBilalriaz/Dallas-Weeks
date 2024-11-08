<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            position: relative;
            padding-bottom: 50px; /* Space for footer */
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            height: 40px;
            text-align: center;
            border-bottom: 1px solid #000;
            display: flex;
            align-items: center;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
        }
        .footer {
            background-color: #333;
            padding: 20px;
            color: #fff;
            text-align: center;
        }
        
        .footer-link {
            color: #00bfff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        
        .footer-link:hover {
            color: #ffcc00;
            text-decoration: underline;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-link {
            color: #00bfff;
            text-decoration: none;
            font-weight: bold;
            padding: 15px;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .footer-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .footer {
                padding: 15px;
            }
        
            .footer-content {
                flex-direction: column;
                align-items: center;
            }
        }
        
        .logo {
            width: 100px;
        }
        
        .header {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #000;
            display: flex;
            align-items: center;
            margin-bottom: 50px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('assets/images/logo.png') }}" class="logo" style="height: 40px;">
        <span>Networked</span>
    </div>
    @foreach($reportData as $monthData)
        <h1>{{ $monthData['month'] }}</h1>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invites</th>
                    <th>Emails</th>
                    <th>Profile Views</th>
                    <th>Follows</th>
                    <th>Messages</th>
                    <th>In Mails</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthData['data'] as $dayData)
                    <tr>
                        <td>{{ $dayData['date'] }}</td>
                        <td>{{ $dayData['invite_count'] }}</td>
                        <td>{{ $dayData['email_count'] }}</td>
                        <td>{{ $dayData['view_count'] }}</td>
                        <td>{{ $dayData['follow_count'] }}</td>
                        <td>{{ $dayData['message_count'] }}</td>
                        <td>{{ $dayData['in_mail_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
    <footer class="footer">
        <div class="footer-content">
            <p>© 2024 Your Company</p>
            <a href="{{ route('homePage') }}" class="footer-link">Visit our website</a>
        </div>
    </footer>
</body>
</html>
