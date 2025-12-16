@php($hideNavbar = true)

@extends('app')

@section('title', content: 'Booking')

@section('content')

<form id="booking-form" data-url="{{ route('booking.checkout.store') }}"  action="{{ route('booking.checkout.store') }}"  method="POST"
  class="w-full min-h-screen pt-10 pb-16 bg-zinc-100 flex flex-col items-center px-4 md:px-10">
  @csrf

  {{-- DATA DARI HALAMAN SEBELUMNYA --}}
  <input type="hidden" name="service" value="{{ $booking['service_id'] ?? '' }}">
  <input type="hidden" name="package" value="{{ $booking['package_id'] ?? '' }}">
  <input type="hidden" name="price" value="{{ $booking['price'] ?? '' }}">
  <input type="hidden" name="booking_date" value="{{ $booking['booking_date'] ?? '' }}">
  <input type="hidden" name="preferred_time" value="{{ $booking['preferred_time'] ?? '' }}">


  <div class="w-full max-w-6xl -mt-6">
    <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-8">
      COMPLETE YOUR BOOKING
    </h2>

    {{-- GRID: FORM KIRI – RINGKASAN KANAN --}}
    <div class="bg-white rounded-3xl shadow-md p-6 md:p-10 grid grid-cols-1 lg:grid-cols-3 gap-8">

      {{-- FORM --}}
      <div class="lg:col-span-2 space-y-6">

        {{-- Full Name --}}
        <div class="w-full space-y-2">
          <label for="full_name" class="block text-sm font-medium text-gray-700">
            Full name <span class="text-red-500">*</span>
          </label>
          <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
            placeholder="What's your full name?"
            class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300" />
          @error('full_name')
            <p class="text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Email --}}
        <div class="w-full space-y-2">
          <label for="email" class="block text-sm font-medium text-gray-700">
            Email <span class="text-red-500">*</span>
          </label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Can we get your email?"
            class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300" />
          @error('email')
            <p class="text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Phone Number --}}
        <div class="w-full space-y-2">
          <label for="phone_number" class="block text-sm font-medium text-gray-700">
            Phone Number <span class="text-red-500">*</span>
          </label>
          <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
            placeholder="e.g. +62 812 3456 7890"
            class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300" />
          @error('phone_number')
            <p class="text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Payment Options --}}
        <div id="payment-wrapper" class="w-full space-y-2">
          <span class="block text-sm font-medium text-gray-700">
            Payment Options <span class="text-red-500">*</span>
          </span>

          <div class="grid grid-cols-2 gap-4">
            <label class="cursor-pointer">
              <input type="radio" name="payment" value="dp" class="hidden peer" {{ old('payment', 'dp') === 'dp' ? 'checked' : '' }}>
              <div
                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white transition">
                <p class="font-medium">Down Payment (50%)</p>
                <p class="text-xs mt-1 opacity-80">
                  Bayar 50% dulu, sisanya sebelum / saat sesi foto.
                </p>
              </div>
            </label>

            <label class="cursor-pointer">
              <input type="radio" name="payment" value="full" class="hidden peer" {{ old('payment') === 'full' ? 'checked' : '' }}>
              <div
                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white transition">
                <p class="font-medium">Full Payment</p>
                <p class="text-xs mt-1 opacity-80">
                  Bayar lunas sekarang, biar nanti tinggal foto aja.
                </p>
              </div>
            </label>
          </div>

          @error('payment')
            <p class="text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Notes --}}
        <div class="w-full space-y-2">
          <label for="notes" class="block text-sm font-medium text-gray-700">
            Notes
          </label>
          <textarea id="notes" name="notes" rows="4" placeholder="Any special requests or additional information..."
            class="w-full rounded-2xl border border-gray-300 bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 resize-none">{{ old('notes') }}</textarea>
        </div>

        {{-- BUTTON BAYAR SEKARANG (MOBILE / TABLET) --}}
        <div class="w-full flex justify-end pt-4 lg:hidden">
          <button id="submit-button" type="submit"
            class="inline-flex items-center gap-2 rounded-full bg-blue-500 px-8 py-3 text-sm font-medium text-white hover:bg-blue-600 transition">
            Bayar Sekarang
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>

      {{-- RINGKASAN BOOKING --}}
      <aside class="bg-zinc-50 border border-zinc-200 rounded-3xl p-5 md:p-6 h-fit">
        <h3 class="text-base font-semibold text-gray-900 mb-4">
          Booking Summary
        </h3>

        <div class="space-y-3 text-sm text-gray-800">
          {{-- Service / Package / Tanggal / Jam / Price --}}
          <div class="flex justify-between">
            <span class="text-gray-500">Service</span>
            <span id="summaryService" class="font-medium text-right">
              {{ $booking['service_name'] ?? '-' }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Package</span>
            <span id="summaryPackage" class="font-medium text-right">
              {{ $booking['package_name'] ?? '-' }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Date</span>
            <span id="summaryDate" class="font-medium text-right">
              @if(!empty($booking['booking_date']))
                {{ \Carbon\Carbon::parse($booking['booking_date'])->translatedFormat('d F Y') }}
              @else
                -
              @endif
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Time</span>
            <span id="summaryTime" class="font-medium text-right">
              {{ $booking['preferred_time'] ?? '-' }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Price</span>
            <span id="summaryPrice" class="font-semibold text-right">
              @php($price = $booking['price'] ?? 0)
              Rp {{ number_format($price, 0, ',', '.') }}
            </span>
          </div>

          <hr class="my-3">

          {{-- DETAIL USER --}}
          <div class="flex justify-between">
            <span class="text-gray-500">Full name</span>
            <span id="summaryFullName" class="font-medium text-right truncate">
              {{ old('full_name', '-') }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Email</span>
            <span id="summaryEmail" class="font-medium text-right truncate">
              {{ old('email', '-') }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Phone</span>
            <span id="summaryPhone" class="font-medium text-right truncate">
              {{ old('phone_number', '-') }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-gray-500">Payment</span>
            <span id="summaryPayment" class="font-medium text-right">
              @if(old('payment') === 'full')
                Full Payment
              @elseif(old('payment', 'dp') === 'dp')
                Down Payment (50%)
              @else
                -
              @endif
            </span>
          </div>

          <div>
            <span class="text-gray-500 text-xs">Notes</span>
            <p id="summaryNotes" class="text-xs text-gray-700 mt-1 line-clamp-3">
              {{ old('notes', '-') }}
            </p>
          </div>

          <hr class="my-3">

          {{-- TOTAL / DP INFO --}}
          <div class="space-y-1">
            <p class="text-xs uppercase tracking-wide text-gray-400">
              Total Amount
            </p>
            <p class="text-xl font-semibold text-gray-900">
              Rp {{ number_format($price, 0, ',', '.') }}
            </p>

            @if(old('payment', 'dp') === 'dp' && $price)
            @php($dp = $price * 0.5)
              <p id="summaryDpRow" class="text-xs text-gray-600">
                DP 50%: <span id="summaryDpAmount" class="font-semibold">
                  Rp {{ number_format($dp, 0, ',', '.') }}
                </span>
              </p>
            @else
            <p id="summaryDpRow" class="text-xs text-gray-600 hidden">
              DP 50%: <span id="summaryDpAmount" class="font-semibold">-</span>
            </p>
            @endif
          </div>
        </div>
        {{-- CTA DI SUMMARY (DESKTOP) --}}
        <div class="hidden lg:block pt-4 mt-4 border-t border-zinc-200">
          <button type="button" onclick="document.getElementById('booking-form').requestSubmit();"
            class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-blue-500 px-8 py-3 text-sm font-medium text-white hover:bg-blue-600 transition">
            Bayar Sekarang
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

      </aside>

    </div>
  </div>
</form>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}"></script>

{{-- MODAL KONFIRMASI (PAKAI PUNYA KAMU) --}}
<div id="confirmModal" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <!-- Backdrop -->
  <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

  <!-- Modal wrapper -->
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
    <div
      class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:max-w-lg w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div
            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 9v3m0 4h.01m-6.938 4h13.856c1.121 0 1.993-1.006 1.791-2.105l-1.72-9.591A2 2 0 0017.01 8H6.99a2 2 0 00-1.978 1.695l-1.72 9.59A2 2 0 004.07 20z" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg font-medium text-gray-900" id="modal-title">Konfirmasi Pemesanan</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Yakin mau lanjutkan pemesanan ini? Pastikan semua datanya udah bener ya!
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
        <button id="confirmYes" type="button"
          class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto">
          Ya, lanjut
        </button>
        <button id="confirmNo" type="button"
          class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 sm:mt-0 sm:w-auto">
          Batal
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // helper
    function setText(id, value, fallback = '-') {
      const el = document.getElementById(id);
      if (!el) return;
      el.textContent = value && value.trim() !== '' ? value : fallback;
    }

    // === Full name ===
    const fullNameInput = document.getElementById('full_name');
    if (fullNameInput) {
      const syncFullName = () => setText('summaryFullName', fullNameInput.value);
      syncFullName();
      fullNameInput.addEventListener('input', syncFullName);
    }

    // === Email ===
    const emailInput = document.getElementById('email');
    if (emailInput) {
      const syncEmail = () => setText('summaryEmail', emailInput.value);
      syncEmail();
      emailInput.addEventListener('input', syncEmail);
    }

    // === Phone ===
    const phoneInput = document.getElementById('phone_number');
    if (phoneInput) {
      const syncPhone = () => setText('summaryPhone', phoneInput.value);
      syncPhone();
      phoneInput.addEventListener('input', syncPhone);
    }

    // === Payment (DP / Full) + DP Amount ===
    const paymentRadios = document.querySelectorAll('input[name="payment"]');
    const priceRaw = {{ (int) ($booking['price'] ?? 0) }}; // dari PHP ke JS number
    const dpRow = document.getElementById('summaryDpRow');
    const dpAmount = document.getElementById('summaryDpAmount');

    function formatRupiah(num) {
      return 'Rp ' + num.toLocaleString('id-ID');
    }

    function updatePaymentSummary() {
      let selected = 'dp';
      paymentRadios.forEach(r => {
        if (r.checked) selected = r.value;
      });

      if (selected === 'full') {
        setText('summaryPayment', 'Full Payment');
        if (dpRow) dpRow.classList.add('hidden');
      } else {
        setText('summaryPayment', 'Down Payment (50%)');
        if (dpRow && dpAmount) {
          dpRow.classList.remove('hidden');
          const dp = Math.round(priceRaw * 0.5);
          dpAmount.textContent = formatRupiah(dp);
        }
      }
    }

    if (paymentRadios.length) {
      updatePaymentSummary();
      paymentRadios.forEach(r => r.addEventListener('change', updatePaymentSummary));
    }

    // === Notes ===
    const notesInput = document.getElementById('notes');
    if (notesInput) {
      const syncNotes = () => setText('summaryNotes', notesInput.value, '-');
      syncNotes();
      notesInput.addEventListener('input', syncNotes);
    }

    // ================================
    //  FORM SUBMIT + MODAL + VALIDASI
    // ================================
    const form = document.getElementById('booking-form');
    const modal = document.getElementById('confirmModal');
    const confirmYesBtn = document.getElementById('confirmYes');
    const confirmNoBtn = document.getElementById('confirmNo');

    function openModal() {
      if (!modal) return;
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    // Hapus error-error front-end sebelumnya
    function clearClientErrors() {
      document.querySelectorAll('.client-error').forEach(el => el.remove());
    }

    // Tampilkan error di bawah input text
    function showFieldError(inputEl, message) {
      if (!inputEl) return;
      const wrapper = inputEl.closest('.w-full') || inputEl.parentElement;
      if (!wrapper) return;

      let errorEl = wrapper.querySelector('.client-error');
      if (!errorEl) {
        errorEl = document.createElement('p');
        errorEl.className = 'client-error text-sm text-red-500 mt-1';
        wrapper.appendChild(errorEl);
      }
      errorEl.textContent = message;
    }

    // Error khusus payment (radio)
    function showPaymentError(message) {
      const paymentWrapper = document.getElementById('payment-wrapper');
      if (!paymentWrapper) return;

      let errorEl = paymentWrapper.querySelector('.client-error');
      if (!errorEl) {
        errorEl = document.createElement('p');
        errorEl.className = 'client-error text-sm text-red-500 mt-1';
        paymentWrapper.appendChild(errorEl);
      }
      errorEl.textContent = message;
    }

    // Validasi front-end
    function validateForm() {
      clearClientErrors();
      let valid = true;

      const fullName = document.getElementById('full_name');
      const email = document.getElementById('email');
      const phone = document.getElementById('phone_number');

      if (!fullName || !email || !phone) return false;

      if (!fullName.value.trim()) {
        showFieldError(fullName, 'Full name wajib diisi.');
        valid = false;
      }

      if (!email.value.trim()) {
        showFieldError(email, 'Email wajib diisi.');
        valid = false;
      }

      if (!phone.value.trim()) {
        showFieldError(phone, 'Phone Number wajib diisi.');
        valid = false;
      }

      let paymentSelected = false;
      paymentRadios.forEach(r => {
        if (r.checked) paymentSelected = true;
      });
      if (!paymentSelected) {
        showPaymentError('Pilih salah satu opsi pembayaran.');
        valid = false;
      }

      return valid;
    }

    // Intercept submit form → validasi → kalau lolos, baru buka modal
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!validateForm()) {
          const firstError = document.querySelector('.client-error');
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          return;
        }

        openModal();
      });
    }

    // Tombol "Ya, lanjut" → tutup modal + submit ke server untuk dapat snap_token
    if (confirmYesBtn && form) {
      confirmYesBtn.addEventListener('click', function () {
        closeModal();

        // Submit form via AJAX untuk mendapatkan snap_token
        const formData = new FormData(form);
        const actionUrl = form.getAttribute('data-url') || form.action;

        fetch(actionUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.snap_token) {
            // Trigger Midtrans Snap
            snap.pay(data.snap_token, {
              onSuccess: function(result) {
                window.location.href = data.redirect_url || '/success';
              },
              onPending: function(result) {
                alert('Menunggu pembayaran. Silakan selesaikan pembayaran Anda.');
                window.location.href = data.redirect_url || '/success';
              },
              onError: function(result) {
                alert('Pembayaran gagal. Silakan coba lagi.');
              },
              onClose: function() {
                alert('Anda menutup popup pembayaran sebelum menyelesaikan transaksi.');
              }
            });
          } else if (data.redirect_url) {
            // Jika tidak ada snap_token (full payment atau error), redirect langsung
            window.location.href = data.redirect_url;
          } else {
            alert('Terjadi kesalahan. Silakan coba lagi.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
        });
      });
    }

    // Tombol "Batal" → cuma tutup modal
    if (confirmNoBtn) {
      confirmNoBtn.addEventListener('click', function () {
        closeModal();
      });
    }
  });
</script>

@endsection
