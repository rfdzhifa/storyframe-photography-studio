<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @vite(['resources/css/app.css',])
    <title>Booking Confirmation</title>
</head>

<body class="bg-white font-sans text-gray-900" style="margin:0; padding:0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="bg-white"
        style="background-color:#ffffff; max-width:600px; margin:auto; border-collapse:collapse;">
        <tr>
            <td class="px-6 py-8" style="padding:24px;">
                <h1 class="text-2xl font-bold text-blue-600"
                    style="color:#2563eb; font-weight:bold; font-size:24px; margin:0 0 16px 0;">Booking Confirmation
                </h1>
                <p class="mb-4" style="margin-bottom:16px;">Hi <strong>{{ $booking->customer_name }}</strong>,</p>
                <p class="mb-6" style="margin-bottom:24px;">Thanks for your booking! Here’s a quick summary:</p>

                <p class="inline-block bg-blue-100 text-blue-700 font-semibold px-4 py-2 rounded mb-6"
                    style="background-color:#dbeafe; color:#1e40af; font-weight:600; padding:8px 16px; border-radius:6px; margin-bottom:24px; display:inline-block;">
                    Kode Booking: {{ $booking->booking_code }}
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="border-collapse:collapse;">
                    <tr>
                        <td style="padding-bottom:16px;">
                            <p class="font-semibold text-blue-600 mb-1"
                                style="color:#2563eb; font-weight:600; margin:0 0 4px 0;">Customer Information</p>
                            <p style="margin:0 0 4px 0;">Name: {{ $booking->customer_name }}</p>
                            <p style="margin:0 0 4px 0;">Email: {{ $booking->customer_email }}</p>
                            <p style="margin:0;">Phone: {{ $booking->customer_phone }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:16px;">
                            <p class="font-semibold text-blue-600 mb-1"
                                style="color:#2563eb; font-weight:600; margin:0 0 4px 0;">Booking Details</p>
                            <p style="margin:0 0 4px 0;">Service: {{ $booking->service->name }}</p>
                            <p style="margin:0 0 4px 0;">Package: {{ $booking->package->name }}</p>
                            <p style="margin:0 0 4px 0;">Date: {{ $booking->booking_date->format('d M Y') }}</p>
                            <p style="margin:0;">Time: {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                                - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:24px;">
                            <p class="font-semibold text-blue-600 mb-1"
                                style="color:#2563eb; font-weight:600; margin:0 0 4px 0;">Payment Information</p>
                            <p style="margin:0 0 4px 0;">Total Price:
                                {{ number_format($booking->total_price, 0, ',', '.') }} IDR
                            </p>
                            <p style="margin:0 0 4px 0;">Payment Method: {{ ucfirst($booking->payment_option) }}</p>
                            @if($booking->payment_option === 'down payment')
                                <p style="margin:0;">Down Payment Amount:
                                    {{ number_format($booking->down_payment_amount, 0, ',', '.') }} IDR
                                </p>
                            @endif
                            <p style="margin-top:8px; font-weight:bold; color:#2563eb;">Status:
                                {{ $booking->bookingStatus->name }}
                            </p>
                        </td>
                    </tr>
                </table>

                <a href="{{ $successUrl }}"
                    class="bg-blue-600 text-white font-semibold px-6 py-3 rounded block text-center no-underline"
                    style="background-color:#2563eb; color:#fff; font-weight:600; padding:12px 24px; border-radius:8px; text-decoration:none; display:block; width:max-content; margin: 0 auto;">
                    View Booking Details
                </a>

                <p class="mt-8 text-gray-500 text-xs text-center"
                    style="margin-top:32px; font-size:12px; color:#6b7280; text-align:center;">
                    This is an automated email. Please do not reply.<br />
                    &copy; {{ date('Y') }} Storyframe. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>

</html>