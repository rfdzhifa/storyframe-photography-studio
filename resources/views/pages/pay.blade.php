@php($hideNavbar = true)
@extends('app')

@section('title', 'Lanjutkan Pembayaran')

@section('content')
<div class="min-h-screen bg-zinc-100 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-md p-6 max-w-md w-full text-center">
    <h1 class="text-xl font-semibold text-gray-900">Mengalihkan ke Payment Gateway…</h1>
    <p class="text-sm text-gray-600 mt-2">
      Booking: <span class="font-semibold">{{ $booking->booking_code }}</span>
    </p>

    <div class="mt-6 text-sm text-gray-500">
      Jika popup tidak muncul, klik tombol di bawah.
    </div>

    <button id="btnOpen"
      class="mt-4 w-full rounded-xl bg-blue-600 text-white font-semibold py-3 hover:bg-blue-700 transition">
      Buka Pembayaran
    </button>

    <a href="{{ route('booking.success', $booking) }}"
      class="mt-3 inline-block text-sm text-gray-600 hover:underline">
      Kembali ke detail booking
    </a>
  </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
  data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
(function(){
  const token = @json($snapToken);
  const backUrl = @json(route('booking.success', $booking));

  function openSnap(){
    if (!token || typeof window.snap === 'undefined') {
      alert('Midtrans Snap belum siap. Silakan refresh halaman.');
      return;
    }

    window.snap.pay(token, {
      onSuccess: function() { window.location.href = backUrl; },
      onPending: function() { window.location.href = backUrl; },
      onError: function() { alert('Pembayaran gagal. Silakan coba lagi.'); },
      onClose: function() {
        // user nutup popup, tetap stay di halaman ini
      }
    });
  }

  document.getElementById('btnOpen')?.addEventListener('click', openSnap);

  // auto-open saat halaman kebuka
  window.addEventListener('load', function(){
    setTimeout(openSnap, 300);
  });
})();
</script>
@endsection
