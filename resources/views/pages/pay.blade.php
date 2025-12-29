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

    <p id="payStatus" class="mt-4 text-sm text-gray-600"></p>

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
  const bookingId = @json($booking->id);

  const statusEl = document.getElementById('payStatus');
  const btn = document.getElementById('btnOpen');

  function setStatus(msg) {
    if (statusEl) statusEl.textContent = msg || '';
  }
  function setBusy(busy) {
    if (!btn) return;
    btn.disabled = !!busy;
    btn.style.opacity = busy ? '0.7' : '1';
    btn.style.cursor = busy ? 'not-allowed' : 'pointer';
  }

  async function syncOnce() {
    // hit sync endpoint (update DB dari Midtrans)
    const r = await fetch(`/booking/${bookingId}/sync-payment`, { method: 'GET' });
    // kalau endpoint kamu selalu json, ini aman:
    const j = await r.json().catch(() => null);
    return j;
  }

  async function getDbStatus() {
    const r = await fetch(`/booking/${bookingId}/status`, { method: 'GET' });
    const j = await r.json();
    return j;
  }

  async function syncUntilDone({ maxTry = 12, delayMs = 1000 } = {}) {
    for (let i = 1; i <= maxTry; i++) {
      setStatus(`Mengecek pembayaran... (${i}/${maxTry})`);
      try {
        await syncOnce();
        const db = await getDbStatus();
        if (db?.success && db.payment_state !== 'pending') {
          return { done: true, db };
        }
      } catch (e) {
        // abaikan, lanjut retry
        console.error(e);
      }
      await new Promise(res => setTimeout(res, delayMs));
    }
    return { done: false };
  }

  async function handleAfterSnap(resultLabel) {
    setBusy(true);
    setStatus(`${resultLabel}. Memverifikasi status...`);

    // retry beberapa detik biar ngejar settlement yang kadang telat
    const r = await syncUntilDone({ maxTry: 12, delayMs: 1000 });

    // selalu redirect, tapi pakai cache-buster supaya blade ga ke-cache
    const url = backUrl + '?t=' + Date.now();
    window.location.href = url;
  }

  function openSnap(){
    if (!token || typeof window.snap === 'undefined') {
      alert('Midtrans Snap belum siap. Silakan refresh halaman.');
      return;
    }

    window.snap.pay(token, {
      onSuccess: function() { handleAfterSnap('Pembayaran sukses'); },
      onPending: function() { handleAfterSnap('Pembayaran diproses'); },
      onError: function() {
        setBusy(false);
        setStatus('');
        alert('Pembayaran gagal. Silakan coba lagi.');
      },
      onClose: function() {
        setBusy(false);
        setStatus('Popup ditutup. Klik "Buka Pembayaran" untuk mencoba lagi.');
      }
    });
  }

  btn?.addEventListener('click', openSnap);

  // auto-open saat halaman kebuka
  window.addEventListener('load', function(){
    setTimeout(openSnap, 300);
  });
})();
</script>
@endsection
