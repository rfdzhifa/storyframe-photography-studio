@extends('app')

@section('title', $service->name . ' - Detail Paket')

@section('content')
<section class="w-full bg-zinc-100 min-h-[60vh] flex flex-col justify-center py-20 md:py-28 px-4 md:px-8 lg:px-40">
  <div class="w-full max-w-6xl mx-auto">

    <a href="{{ route('booking.catalog') }}"
       class="text-sm px-4 py-2 rounded-full border border-gray-300 hover:bg-gray-100 inline-block mb-8">
      ← Kembali ke Catalog
    </a>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-start mb-10">
      <img src="{{ $service->thumbnail ?? 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop' }}"
           class="rounded-2xl shadow-md object-cover w-full h-80 md:h-[450px]"
           alt="{{ $service->name }}" />

      <div class="flex flex-col gap-5">
        <h1 class="text-3xl md:text-5xl font-semibold text-gray-900">{{ $service->name }}</h1>
        <p class="text-gray-600 text-sm md:text-base leading-relaxed">
          {{ $service->description ?? 'Nikmati pengalaman fotografi profesional dengan hasil terbaik untuk setiap momen Anda.' }}
        </p>

        <!-- Pilih Paket -->
<div class="mt-1">
  <h2 class="text-sm md:text-base font-semibold text-gray-900 mb-2">Pilih Paket</h2>

<div id="packageGrid" class="grid grid-cols-3 gap-3">
  @foreach ($service->packages as $pkg)
    <button type="button"
      class="pkg-option flex justify-center items-center px-4 py-2 rounded-xl border border-gray-300 hover:border-blue-500 transition bg-white text-sm font-medium"
      data-id="{{ $pkg->id }}"                     {{-- <<< WAJIB --}}
      data-name="{{ $pkg->name }}"                 {{-- <<< WAJIB --}}
      data-price="{{ $pkg->pivot->price }}">       {{-- <<< WAJIB --}}
      {{ $pkg->name }}
    </button>
  @endforeach
</div>

  {{-- ===== TANGGAL & JAM (berdampingan) ===== --}}
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block text-sm md:text-base font-semibold text-gray-900 mb-2">Pilih Tanggal</label>
    <input
      type="date"
      id="bookingDate"
      class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-300"
      min="{{ now()->toDateString() }}"
      max="{{ now()->addDays(30)->toDateString() }}"
    />
    <p id="dateHint" class="mt-2 text-xs text-gray-500">Pilih tanggal setelah memilih paket.</p>
  </div>

  <div>
    <label class="block text-sm md:text-base font-semibold text-gray-900 mb-2">Pilih Jam</label>
    <select
      id="bookingTimeSelect"
      class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-300"
      disabled
    >
      <option value="">Pilih tanggal dulu…</option>
    </select>
    <p id="slotHint" class="mt-2 text-xs text-gray-500">Slot akan muncul setelah pilih paket & tanggal.</p>
  </div>
</div>

<button id="btnBook" type ="submit"
          class="mt-4 w-full px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold disabled:opacity-40 disabled:cursor-not-allowed">
    Lanjut Booking
  </button>

{{-- hidden untuk redirect ke form booking --}}
<input type="hidden" id="inputServiceId" value="{{ $service->id }}">
<input type="hidden" id="inputPackageId">
<input type="hidden" id="inputPackagePrice">
<input type="hidden" id="inputStartTime">

@push('scripts')
<script>
(() => {
  // ===== Utils =====
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(n || 0));
  const fmtDate = v => !v ? '—' : new Date(v + 'T00:00:00').toLocaleDateString(
    'id-ID',{ weekday:'long', day:'2-digit', month:'long', year:'numeric' }
  );
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ===== Elements (null-safe) =====
  const els = {
    pkgBtns: $$('.pkg-option'),
    info:    $('#pkgInfo'),
    nameEl:  $('#pkgName'),
    priceEl: $('#pkgPrice'),

    sumName:  $('#summaryName'),
    sumPrice: $('#summaryPrice'),
    sumDate:  $('#summaryDate'),
    sumTime:  $('#summaryTime'),

    inputSvc:   $('#inputServiceId'),
    inputPkg:   $('#inputPackageId'),
    inputPrice: $('#inputPackagePrice'),
    inputTime:  $('#inputStartTime'),

    dateEl:  $('#bookingDate'),
    timeSel: $('#bookingTimeSelect'),    // << DIUBAH: dropdown jam
    slotHint: $('#slotHint'),
    btnBook: $('#btnBook'),
  };

  // guard minimal
  if (!els.inputSvc || !els.btnBook) return;

  // ===== State =====
  let slotsAbort = null;

  const setBookButtonState = () => {
    const ok = !!(els.inputPkg?.value && els.dateEl?.value && els.inputTime?.value);
    els.btnBook.disabled = !ok;
  };

  // reset dropdown jam
  const clearSlots = (msg = 'Slot akan muncul setelah pilih paket & tanggal.') => {
    if (els.timeSel) {
      els.timeSel.innerHTML = `<option value="">${msg}</option>`;
      els.timeSel.disabled = true;
      els.timeSel.value = '';
    }
    if (els.inputTime) els.inputTime.value = '';
    if (els.sumTime) els.sumTime.textContent = '—';
    els.slotHint && (els.slotHint.textContent = msg);
    setBookButtonState();
  };

  // ===== Slots Loader (with cancellation) =====
  const loadSlots = async () => {
    clearSlots('Memuat slot…');

    const date = els.dateEl?.value;
    const pkg  = els.inputPkg?.value;
    if (!date || !pkg) { clearSlots(); return; }

    // cancel req sebelumnya
    if (slotsAbort) slotsAbort.abort();
    slotsAbort = new AbortController();

    try {
      const res = await fetch("{{ route('booking.slots') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf(),
        },
        body: JSON.stringify({ date, package: pkg }),
        signal: slotsAbort.signal,
      });

      if (!res.ok) {
        clearSlots(`Gagal memuat slot (HTTP ${res.status}).`);
        return;
      }

      const slots = await res.json(); // [{start,end}, ...]
      if (!Array.isArray(slots) || !slots.length) {
        clearSlots('Tidak ada slot tersedia pada tanggal ini.');
        return;
      }

      els.slotHint && (els.slotHint.textContent = 'Pilih salah satu jam:');
      els.timeSel.disabled = false;
      els.timeSel.innerHTML = `<option value="">Pilih jam…</option>`;

      slots.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.start;
        opt.textContent = `${s.start} - ${s.end}`;
        els.timeSel.appendChild(opt);
      });

    } catch (err) {
      if (err.name === 'AbortError') return; // di-cancel, no-op
      clearSlots('Gagal memuat slot.');
    }
  };

  // ===== Package Select =====
  const selectPackage = (btn) => {
    if (!btn) return;

    // visual
    els.pkgBtns.forEach(b => b.classList.remove('ring-2','ring-blue-600','bg-blue-50','border-blue-500'));
    btn.classList.add('ring-2','ring-blue-600','bg-blue-50','border-blue-500');

    // data
    const id    = btn.dataset.id || btn.dataset.pkgId || '';
    const name  = btn.dataset.name || '';
    const price = Number(btn.dataset.price || 0);

    if (els.inputPkg)   els.inputPkg.value   = id;
    if (els.inputPrice) els.inputPrice.value = String(price);

    els.info   && els.info.classList.remove('hidden');
    els.nameEl && (els.nameEl.textContent  = name);
    els.priceEl&& (els.priceEl.textContent = fmtRp(price));

    els.sumName  && (els.sumName.textContent  = name);
    els.sumPrice && (els.sumPrice.textContent = fmtRp(price));

    // reset jam bila paket berubah
    clearSlots();

    // reload slot jika tanggal sudah ada
    if (els.dateEl?.value) loadSlots();
    setBookButtonState();
  };

  // ===== Events =====
  els.pkgBtns.forEach(btn => btn.addEventListener('click', () => selectPackage(btn)));

  els.dateEl?.addEventListener('change', () => {
    els.sumDate && (els.sumDate.textContent = fmtDate(els.dateEl.value));
    loadSlots();
  });

  // ketika user memilih jam di dropdown
  els.timeSel?.addEventListener('change', () => {
    const v = els.timeSel.value;
    if (els.inputTime) els.inputTime.value = v || '';
    if (els.sumTime) els.sumTime.textContent = v || '—';
    setBookButtonState();
  });

  // preselect: paket termurah (aman kalau data-price ada)
  if (els.pkgBtns.length) {
    const sorted = [...els.pkgBtns].sort((a,b) => Number(a.dataset.price||0) - Number(b.dataset.price||0));
    selectPackage(sorted[0]);
  }

  // CTA → redirect ke halaman booking dengan query lengkap
  els.btnBook.addEventListener('click', () => {
    const svc  = els.inputSvc?.value;
    const pkg  = els.inputPkg?.value;
    const date = els.dateEl?.value;
    const time = els.inputTime?.value;
    const price= els.inputPrice?.value || '0';

    if (!svc || !pkg || !date || !time) return;

    const url = new URL("{{ route('booking.checkout') }}", window.location.origin);
    url.searchParams.set('service', svc);
    url.searchParams.set('package', pkg);
    url.searchParams.set('date',    date);
    url.searchParams.set('time',    time);
    url.searchParams.set('price',   price);

    window.location.assign(url.toString());
  });
})();
</script>
@endpush
