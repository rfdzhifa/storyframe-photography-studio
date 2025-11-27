<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background-color:#f3f4f6; padding:24px 0;">
    <tr>
        <td align="center">
            <!-- Wrapper -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 18px rgba(15,23,42,0.08); border-collapse:collapse;">

                <!-- Header -->
                <tr>
                    <td style="padding:20px 32px; background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#ffffff; text-align:left;">
                        <div style="font-size:13px; letter-spacing:0.08em; text-transform:uppercase; opacity:0.85; margin-bottom:4px;">
                            Storyframe Studio
                        </div>
                        <div style="font-size:22px; font-weight:700; margin:0;">
                            Booking Confirmation
                        </div>
                    </td>
                </tr>

                <!-- Greeting -->
                <tr>
                    <td style="padding:24px 32px 0 32px; color:#111827;">
                        <p style="margin:0 0 6px 0; font-size:14px;">
                            Hi <strong>{{ $booking->customer_name }}</strong>,
                        </p>
                        <p style="margin:0 0 18px 0; font-size:14px; color:#4b5563;">
                            Thank you for booking a photo session with <strong>Storyframe Studio</strong>.
                            Here’s your booking summary:
                        </p>
                    </td>
                </tr>

                <!-- Booking Code Badge -->
                <tr>
                    <td style="padding:0 32px 8px 32px;">
                        <div style="display:inline-block; padding:8px 16px; border-radius:999px; background-color:#eff6ff; border:1px solid #bfdbfe; font-size:13px; color:#1d4ed8; font-weight:600;">
                            Booking Code: {{ $booking->booking_code }}
                        </div>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:16px 32px 0 32px;">
                        <hr style="border:none; height:1px; background-color:#e5e7eb; margin:0;">
                    </td>
                </tr>

                <!-- Customer Information -->
                <tr>
                    <td style="padding:16px 32px 0 32px;">
                        <p style="margin:0 0 6px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; font-weight:600;">
                            Customer Information
                        </p>
                        <p style="margin:0; font-size:14px; color:#111827;">
                            <strong>Name</strong><br>
                            {{ $booking->customer_name }}
                        </p>
                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                            <strong>Email</strong><br>
                            <a href="mailto:{{ $booking->customer_email }}" style="color:#2563eb; text-decoration:none;">
                                {{ $booking->customer_email }}
                            </a>
                        </p>
                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                            <strong>Phone / WhatsApp</strong><br>
                            {{ $booking->customer_phone }}
                        </p>
                    </td>
                </tr>

                <!-- Booking Details -->
                <tr>
                    <td style="padding:20px 32px 0 32px;">
                        <p style="margin:0 0 6px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; font-weight:600;">
                            Booking Details
                        </p>
                        <p style="margin:0; font-size:14px; color:#111827;">
                            <strong>Service</strong><br>
                            {{ $booking->service->name ?? '-' }}
                        </p>
                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                            <strong>Package</strong><br>
                            {{ $booking->package->name ?? '-' }}
                        </p>
                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                            <strong>Date</strong><br>
                            @if($booking->booking_date)
                                {{ $booking->booking_date->format('d M Y') }}
                            @else
                                -
                            @endif
                        </p>

                        @php
                            $startTime = $booking->start_time ? $booking->start_time->format('H:i') : '-';
                            $endTime   = $booking->end_time   ? $booking->end_time->format('H:i')   : '-';
                        @endphp
                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                            <strong>Time</strong><br>
                            {{ $startTime }} – {{ $endTime }}
                        </p>
                    </td>
                </tr>

                <!-- Payment Information -->
                <tr>
                    <td style="padding:20px 32px 0 32px;">
                        <p style="margin:0 0 6px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; font-weight:600;">
                            Payment Information
                        </p>
                        <p style="margin:0; font-size:14px; color:#111827;">
                            <strong>Total Price</strong><br>
                            {{ number_format($booking->total_price, 0, ',', '.') }} IDR
                        </p>

                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                            <strong>Payment Method</strong><br>
                            {{ ucfirst($booking->payment_option) }}
                        </p>

                        @if($booking->payment_option === 'down payment')
                            <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">
                                <strong>Down Payment Amount</strong><br>
                                {{ number_format($booking->down_payment_amount, 0, ',', '.') }} IDR
                            </p>
                        @endif

                        <p style="margin:10px 0 0 0; font-size:14px; color:#2563eb; font-weight:600;">
                            Status: {{ $booking->bookingStatus->name }}
                        </p>
                    </td>
                </tr>

                <!-- Optional Note -->
                @if(!empty($booking->notes))
                    <tr>
                        <td style="padding:20px 32px 0 32px;">
                            <p style="margin:0 0 6px 0; font-size:13px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; font-weight:600;">
                                Your Notes
                            </p>
                            <p style="margin:0; font-size:14px; color:#4b5563;">
                                “{{ $booking->notes }}”
                            </p>
                        </td>
                    </tr>
                @endif

                <!-- Button -->
                <tr>
                    <td style="padding:28px 32px 8px 32px; text-align:center;">
                        <a href="{{ $successUrl }}"
                           style="display:inline-block; padding:12px 28px; border-radius:999px; background-color:#2563eb; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none;">
                            View Booking Details
                        </a>
                    </td>
                </tr>

                <!-- Small info -->
                <tr>
                    <td style="padding:8px 32px 0 32px; text-align:center;">
                        <p style="margin:0; font-size:12px; color:#6b7280;">
                            If you need to reschedule or have any questions,
                            you can reply to this email or contact us via WhatsApp.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:20px 32px 24px 32px; text-align:center;">
                        <p style="margin:0; font-size:11px; color:#9ca3af;">
                            This is an automated email. Please do not reply directly.<br>
                            &copy; {{ date('Y') }} Storyframe Studio. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
            <!-- End Wrapper -->
        </td>
    </tr>
</table>

</body>
</html>
