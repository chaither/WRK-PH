<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Password Reset</title>
    <style type="text/css">
        /* Basic Reset */
        body, td, div, p, a { font-family: 'Inter', Helvetica, Arial, sans-serif; }
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #f2f2f2; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        p { margin: 0; padding: 0; }
        
        /* Custom Styles */
        .container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #ffffff;
        }
        .header img {
            max-height: 75px;
            width: auto;
            border: none;
        }
        .content-cell {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
            font-size: 15px;
        }
        .button-wrapper {
            text-align: center;
            padding: 20px 0;
        }
        .button {
            background-color: #007bff; /* A nice blue */
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none; /* Important for email clients */
            font-weight: bold;
            font-size: 16px;
            display: inline-block; /* Essential for padding and width */
        }
        .footer {
            background-color: #f2f2f2;
            padding: 20px;
            border-top: 1px solid #eaeaec;
            text-align: center;
            color: #777777;
            font-size: 12px;
        }

        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; border-radius: 0; }
        }
    </style>
</head>
<body>
    <table class="body-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f2f2f2;">
        <tr>
            <td align="center">
                <table class="container" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <a href="{{ config('app.url') }}">
                                <img src="{{ asset('limehills.png') }}" alt="{{ config('app.name') }} Logo">
                            </a>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td class="content-cell">
                            <p style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">Hello!</p>
                            <p style="margin-bottom: 15px;">You are receiving this email because we received a password reset request for your account.</p>
                            
                            <div class="button-wrapper">
                                <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}" class="button">
                                    Reset Password
                                </a>
                            </div>

                            <p style="margin-top: 15px; margin-bottom: 15px;">This password reset link will expire in {{ config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') }} minutes.</p>
                            <p style="margin-bottom: 15px;">If you did not request a password reset, no further action is required.</p>
                            <p style="margin-top: 25px;">Regards,</p>
                            <p>{{ config('app.name') }}</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
