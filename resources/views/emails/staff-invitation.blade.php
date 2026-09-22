<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Invitation — LeaveFlow</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    <tr>
                        <td style="background-color:#4a0e18;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#d4b36a;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#fdf7f4;font-weight:700;">LeaveFlow Staff Invitation</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 32px 0;">
                            <p style="margin:0 0 12px;font-size:16px;color:#2b070d;">Hello {{ $invitee->name }},</p>
                            <p style="margin:0 0 8px;font-size:15px;line-height:1.7;color:#4a0e18;">
                                <strong>{{ $invitedByName }}</strong> has invited you to join <strong>LeaveFlow</strong> as
                                <strong>{{ $invitee->role->label() }}</strong>@if($invitee->department) in <strong>{{ $invitee->department->name }}</strong>@endif.
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#6b1424;">
                                Click the button below to set up your password and activate your account.
                                This link expires in <strong>48 hours</strong>.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 28px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto;">
                                <tr>
                                    <td align="center" bgcolor="#6b1424" style="border-radius:10px;">
                                        <a href="{{ $setupUrl }}" target="_blank"
                                           style="display:inline-block;padding:14px 28px;font-size:16px;font-weight:700;color:#fdf7f4;text-decoration:none;">
                                            Set Up My Account
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 20px;">
                            <p style="margin:0;font-size:12px;color:#8a1c30;">If the button does not work, copy and paste this link into your browser:</p>
                            <p style="margin:6px 0 0;font-size:12px;word-break:break-all;color:#6b1424;">
                                --------------------------------------------------<br>
                                {{ $setupUrl }}<br>
                                --------------------------------------------------
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 28px;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8a1c30;">
                                If you did not expect this invitation, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#fdf7f4;padding:16px 32px;border-top:1px solid #f6ebe6;">
                            <p style="margin:0;font-size:12px;color:#8a1c30;">LeaveFlow · Eastern Visayas State University — Ormoc Campus</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
