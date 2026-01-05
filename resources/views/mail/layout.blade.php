<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aaleyat' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
        }

        table {
            border-spacing: 0;
            width: 100%;
        }

        img {
            border: 0;
            display: block;
        }

        .email-wrapper {
            max-width: 600px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
        }

        .email-header {
            background: #F6A31C;
            text-align: center;
            padding: 35px 20px;
            color: #ffffff;
            font-size: 26px;
            font-weight: bold;
        }

        .email-content {
            padding: 35px 30px;
        }

        .email-content p {
            color: #4a5568;
            line-height: 1.8;
            font-size: 15px;
            margin-bottom: 18px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            color: #ffffff;
            font-size: 40px;
            font-weight: bold;
            text-align: center;
            line-height: 80px;
            margin: 0 auto 25px;
        }

        .details-box {
            background: #f7fafc;
            border-left: 4px solid #F6A31C;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            font-size: 14px;
        }

        .details-box p {
            margin: 6px 0;
            color: #2d3748;
        }

        .code-display {
            background: #F6A31C;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .code-display .code {
            font-size: 34px;
            letter-spacing: 6px;
            font-weight: bold;
            color: #ffffff;
            font-family: 'Courier New', monospace;
        }

        .btn-primary {
            display: inline-block;
            background: #F6A31C;
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 15px;
        }

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 18px;
            border-radius: 8px;
            margin: 20px 0;
            color: #78350f;
            font-size: 14px;
        }

        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 30px 0;
            width: 100%;
        }

        .email-footer {
            background: #f7fafc;
            padding: 25px 20px;
            text-align: center;
            color: #718096;
            font-size: 13px;
            line-height: 1.6;
        }

        .email-footer a {
            color: #F6A31C;
            text-decoration: none;
        }

        /* RTL support */
        .email-rtl {
            direction: rtl;
            text-align: right;
        }

        .email-rtl .details-box,
        .email-rtl .warning-box {
            border-left: none;
            border-right: 4px solid #F6A31C;
        }
    </style>
</head>

<body>

<table role="presentation" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding: 30px 15px;">

            <table class="email-wrapper" role="presentation">
                {{-- Header --}}
                <tr>
                    <td class="email-header">
                        Aaleyat
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td>
                        @yield('content')
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td class="email-footer">
                        <strong>Aaleyat</strong> – Driving Innovation Forward<br>
                        © {{ date('Y') }} Aaleyat. All rights reserved.<br>
                        <a href="https://www.aaleyat.com">www.aaleyat.com</a>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
