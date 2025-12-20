@extends('app')

@section('title', $service->name . ' - Detail Paket')

@section('content')
@php
  $fallback = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop';
@endphp

<section class="w-full bg-zinc-100 min-h-[60vh] py-20 md:py-28 px-4 md:px-8 lg:px-40">
  <div class="w-full max-w-6xl mx-auto">

    <a href="{{ route('booking.catalog') }}"
       class="text-sm px-4 py-2 rounded-full border border-gray-300 hover:bg-white hover:shadow-sm transition inline-flex items-center gap-2 mb-8">
      ← Kembali ke Catalog
    </a>

    {{-- CARD BESAR: FOTO + KONTEN DALAM 1 KOTAK --}}
    <div class="bg-white rounded-[24px] shadow-md overflow-hidden">
      <div class="grid grid-cols-1 md:grid-cols-2">

        {{-- FOTO --}}
        <div class="group overflow-hidden bg-zinc-200">
          <img
            src="{{ route('booking.services.thumb', $service->id) }}"
            onerror="this.onerror=null; this.src='{{ $fallback }}';"
            alt="{{ $service->name }}"
            class="w-full h-72 md:h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
        </div>

        {{-- KONTEN --}}
        <div class="p-6 md:p-8 flex flex-col gap-6">

          {{-- JUDUL SERVICE --}}
          <div class="flex flex-col gap-2">
            <h1 class="text-3xl md:text-5xl font-semibold text-gray-900">
              {{ $service->name }}
            </h1>
            <p class="text-gray-600 text-sm md:text-base leading-relaxed">
              {{ $service->description ?? 'Nikmati pengalaman fotografi profesional dengan hasil terbaik.' }}
            </p>
          </div>

          {{-- DETAIL PAKET --}}
          <div class="flex flex-col gap-2">
            <h2 class="text-sm md:text-base font-semibold text-gray-900">
              Detail Paket
            </h2>

            <p id="pkgDescHint" class="text-xs text-gray-500">
              Pilih paket untuk melihat detailnya.
            </p>

            <div id="pkgDescWrap" class="hidden">
              <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-3">
                <p id="pkgDesc" class="text-sm text-gray-700 leading-relaxed"></p>
              </div>
            </div>
          </div>

          {{-- PILIH PAKET --}}
          <div>
            <h2 class="text-sm md:text-base font-semibold text-gray-900 mb-2">
              Pilih Paket
            </h2>

            <div id="packageGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              @foreach ($service->packages as $pkg)
                <button
                  type="button"
                  class="pkg-option px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm font-medium hover:border-blue-500 transition"
                  data-id="{{ $pkg->id }}"
                  data-name="{{ $pkg->name }}"
                  data-price="{{ $pkg->pivot->price }}"
                  data-description="{{ e($pkg->pivot->description ?? '') }}"
                >
                  {{ $pkg->name }}
                </button>
              @endforeach
            </div>
          </div>

          {{-- TANGGAL & JAM --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">
                Pilih Tanggal
              </label>
              <input
                type="date"
                id="bookingDate"
                class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-300"
                min="{{ now()->toDateString() }}"
                max="{{ now()->addDays(30)->toDateString() }}"
              />
              <p class="mt-2 text-xs text-gray-500">Pilih tanggal setelah memilih paket.</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">
                Pilih Jam
              </label>
              <select
                id="bookingTimeSelect"
                class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-300"
                disabled
              >
                <option value="">Pilih tanggal dulu…</option>
              </select>
              <p id="slotHint" class="mt-2 text-xs text-gray-500">
                Slot akan muncul setelah pilih paket & tanggal.
              </p>
            </div>
          </div>

          {{-- CTA --}}
          <button
            id="btnBook"
            type="button"
            class="w-full px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold disabled:opacity-40 disabled:cursor-not-allowed"
          >
            Lanjut Booking
          </button>

          {{-- HIDDEN --}}
          <input type="hidden" id="inputServiceId" value="{{ $service->id }}">
          <input type="hidden" id="inputPackageId">
          <input type="hidden" id="inputPackagePrice">
          <input type="hidden" id="inputStartTime">

        </div>
      </div>
    </div>

  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ===== Elements =====
  const els = {
    pkgBtns: $$('.pkg-option'),
    pkgDesc: $('#pkgDesc'),
    pkgDescWrap: $('#pkgDescWrap'),
    pkgDescHint: $('#pkgDescHint'),

    inputSvc:   $('#inputServiceId'),
    inputPkg:   $('#inputPackageId'),
    inputPrice: $('#inputPackagePrice'),
    inputTime:  $('#inputStartTime'),
    dateEl:  $('#bookingDate'),
    timeSel: $('#bookingTimeSelect'),
    slotHint: $('#slotHint'),
    btnBook: $('#btnBook'),
  };

  if (!els.inputSvc || !els.btnBook) return;

  let slotsAbort = null;

  const setBookButtonState = () => {
    const ok = !!(els.inputPkg.value && els.dateEl.value && els.inputTime.value);
    els.btnBook.disabled = !ok;
  };

  const clearSlots = (msg = 'Slot akan muncul setelah pilih paket & tanggal.') => {
    if (els.timeSel) {
      els.timeSel.innerHTML = `<option value="">${msg}</option>`;
      els.timeSel.disabled = true;
      els.timeSel.value = '';
    }
    if (els.inputTime) els.inputTime.value = '';
    els.slotHint && (els.slotHint.textContent = msg);
    setBookButtonState();
  };

  const loadSlots = async () => {
    clearSlots('Memuat slot…');

    const date = els.dateEl?.value;
    const pkg  = els.inputPkg?.value;
    const svc  = els.inputSvc?.value;

    if (!date || !pkg || !svc) { clearSlots(); return; }

    if (slotsAbort) slotsAbort.abort();
    slotsAbort = new AbortController();

    try {
      const res = await fetch(SLOTS_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf(),
        },
        body: JSON.stringify({ date, service: els.inputSvc.value, package: pkg }),
        signal: slotsAbort.signal,
      });

      if (!res.ok) {
        clearSlots(`Gagal memuat slot (HTTP ${res.status}).`);
        return;
      }

      const slots = await res.json();
      if (!Array.isArray(slots) || !slots.length) {
        clearSlots('Tidak ada slot tersedia pada tanggal ini.');
        return;
      }

      els.slotHint.textContent = 'Pilih salah satu jam:';
      els.timeSel.disabled = false;
      els.timeSel.innerHTML = `<option value="">Pilih jam…</option>`;

      slots.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.start;
        opt.textContent = `${s.start} - ${s.end}`;
        els.timeSel.appendChild(opt);
      });

    } catch (err) {
      if (err.name === 'AbortError') return;
      clearSlots('Gagal memuat slot.');
    }
  };

  const selectPackage = (btn) => {
    if (!btn) return;

    // visual active state
    els.pkgBtns.forEach(b => b.classList.remove('ring-2','ring-blue-600','bg-blue-50','border-blue-500'));
    btn.classList.add('ring-2','ring-blue-600','bg-blue-50','border-blue-500');

    // data
    const id    = btn.dataset.id || '';
    const price = Number(btn.dataset.price || 0);
    const desc  = btn.dataset.description || '';

    if (els.inputPkg)   els.inputPkg.value   = id;
    if (els.inputPrice) els.inputPrice.value = String(price);

    // description box
    if (els.pkgDesc) els.pkgDesc.textContent = desc || '—';
    if (els.pkgDescWrap) els.pkgDescWrap.classList.toggle('hidden', !desc);
    if (els.pkgDescHint) els.pkgDescHint.classList.toggle('hidden', !!desc);

    // reset slots when package changes
    clearSlots();
    if (els.dateEl?.value) loadSlots();
    setBookButtonState();
  };

  // events
  els.pkgBtns.forEach(btn => btn.addEventListener('click', () => selectPackage(btn)));

  els.dateEl?.addEventListener('change', () => {
    loadSlots();
    setBookButtonState();
  });

  els.timeSel?.addEventListener('change', () => {
    const v = els.timeSel.value;
    if (els.inputTime) els.inputTime.value = v || '';
    setBookButtonState();
  });

  // auto select cheapest package
  if (els.pkgBtns.length) {
    const sorted = [...els.pkgBtns].sort((a,b) => Number(a.dataset.price||0) - Number(b.dataset.price||0));
    selectPackage(sorted[0]);
  }

  // CTA redirect
  els.btnBook.addEventListener('click', () => {
    const svc  = els.inputSvc.value;
    const pkg  = els.inputPkg.value;
    const date = els.dateEl.value;
    const time = els.inputTime.value;
    const price= els.inputPrice.value || '0';
    if (!svc || !pkg || !date || !time) return;

    const url = new URL("{{ route('booking.checkout') }}", window.location.origin);
    url.searchParams.set('service', svc);
    url.searchParams.set('package', pkg);
    url.searchParams.set('date', date);
    url.searchParams.set('time', time);
    url.searchParams.set('price', price);

    window.location.assign(url.toString());
  });

  // preselect first
  selectPackage(els.pkgBtns[0]);
})();
</script>
@endpush
