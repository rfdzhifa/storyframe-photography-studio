<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout Midtrans</title>
</head>

<body>
    <h3>Checkout Midtrans Sandbox</h3>
    <p>Order ID: {{ $orderId }}</p>
    <p>Total: Rp {{ number_format($amount, 0, ',', '.') }}</p>

    <button id="pay-button">Bayar Sekarang</button>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>

    <script>
        let payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function (result) {
                    fetch('{{ route("booking.payment.success", $booking->booking_code) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            booking_code: '{{ $booking->booking_code }}',
                            transaction_id: result.transaction_id,
                            status: result.transaction_status
                        })
                    }).then(() => {
                        alert("Pembayaran sukses!");
                        // ✅ Ganti ke route binding yang benar
                        window.location.href = '{{ route("booking.success", $booking->booking_code) }}';
                    });
                },
                onPending: function (result) {
                    console.log('pending', result);
                    alert("Pembayaran pending.");
                },
                onError: function (result) {
                    console.log('error', result);
                    alert("Terjadi error.");
                },
                onClose: function () {
                    alert('Kamu menutup popup pembayaran.');
                }
            });
        });
    </script>
</body>

</html>