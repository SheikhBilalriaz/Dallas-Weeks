<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Subscription is Successful!</title>
</head>

<body style="background:#f9f9f9">
    <div style="max-width:640px;margin:0 auto;background:transparent;">
        <table style="width:100%;background:transparent;" align="center" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding:40px 15px;">
                    <a href="{{ route('homePage') }}" target="_blank">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" style="width:138px;">
                    </a>
                </td>
            </tr>
        </table>
        <div style="max-width:640px;margin:0 auto;background:#e3c935">
            <table style="width:100%;font-size:0px;background:#e3c935;" align="center" border="0" cellpadding="0"
                cellspacing="0">
                <tr>
                    <td
                        style="text-align:center;color:white;font-family:Arial;font-size:36px;font-weight:600;padding:15px;">
                        Seat Subscription!
                    </td>
                </tr>
            </table>
        </div>
        <div style="max-width:640px;margin:0 auto;background:#ffffff;padding:25px;">
            <table style="width:100%;background:#ffffff" align="center" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td
                        style="padding:40px 70px;text-align:left;color:#737f8d;font-family:Arial;font-size:16px;line-height:24px">
                        <img src="{{ asset('assets/images/emailLogo.png') }}"
                            style="display:block;margin:0 auto;width:50%">
                        <h2 style="font-weight:800;font-size:20px;color:#000">Hey {{ $user->name }},</h2>
                        <p>Thank you for subscribing to your seat on Networked!</p>
                        <p>Your subscription information:</p>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #000; font-weight:600;">Seat name</span>
                            <br>
                            {{ $company_info->name }}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #000; font-weight:600;">Created by</span>
                            <br>
                            {{ $creator->name }}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #000; font-weight:600;">Email</span>
                            <br>
                            {{ $seat_info->email }}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #000; font-weight:600;">Plan Price</span>
                            <br>
                            {{ '$' . $price->unit_amount_decimal / 100 . '.00/month' }}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #000; font-weight:600;">Payment</span>
                            <br>
                            {{ '**** **** **** ' . $paymentMethod->card->last4 }}
                        </div>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #000; font-weight:600;">Card Brand</span>
                            <br>
                            {{ $paymentMethod->card->brand }}
                        </div>
                        <p>We are excited to have you onboard!</p>
                    </td>
                </tr>
            </table>
        </div>
        <div
            style="max-width:640px;margin:20px auto;text-align:center;color:#99aab5;font-family:Arial;font-size:12px;padding:26px;">
            Sent by Networked •
            <a href="{{ route('homePage') }}" style="color:#1eb0f4;text-decoration:none">
                Check our website
            </a>
        </div>
    </div>
</body>

</html>
