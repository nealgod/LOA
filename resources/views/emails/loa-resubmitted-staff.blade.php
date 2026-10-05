<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOA Resubmitted</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#92400e;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#fde68a;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#ffffff;font-weight:700;">LOA Resubmitted — Action Required</h1>
                        </td>
                    </tr>

                    {{-- Control number --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#92400e;text-transform:uppercase;letter-spacing:0.1em;">Control Number</p>
                            <p style="margin:0;font-family:monospace;font-size:22px;font-weight:700;color:#4a0e18;letter-spacing:0.05em;">{{ $loa->control_number }}</p>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr><td style="padding:20px 32px 0;"><hr style="border:none;border-top:1px solid #f6ebe6;"></td></tr>

                    {{-- Message --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">
                                Dear <strong>{{ $recipient->name }}</strong>,
                            </p>
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">
                                A student has <strong>resubmitted</strong> their Leave of Absence application after it was previously not approved.
                                The application is now back in the pipeline and is awaiting your review.
                            </p>
                        </td>
                    </tr>

                    {{-- Application details --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fef3c7;border-radius:8px;padding:16px 20px;">
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#92400e;width:38%;vertical-align:top;">Student</td>
                                    <td style="padding:4px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->full_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#92400e;vertical-align:top;">Department</td>
                                    <td style="padding:4px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->department?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#92400e;vertical-align:top;">Leave period</td>
                                    <td style="padding:4px 0;font-size:13px;color:#2b070d;font-weight:600;">
                                        {{ $loa->start_date?->format('M j, Y') }} — {{ $loa->return_date?->format('M j, Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#92400e;vertical-align:top;">Resubmission</td>
                                    <td style="padding:4px 0;font-size:13px;color:#2b070d;font-weight:600;">#{{ $loa->resubmit_count }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 12px;font-size:13px;line-height:1.7;color:#4a0e18;">
                                Please log in to the LeaveFlow portal to review and take action on this application.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
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
