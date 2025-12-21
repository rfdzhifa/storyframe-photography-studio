@extends('app')

@section('title', 'Booking Catalog')

@section('content')

    <!-- Header Section -->
    <section class="w-full min-h-[50vh] bg-zinc-100 flex items-center justify-center px-4 md:px-8 lg:px-40 pt-28 pb-20 md:pt-36 md:pb-24">
        <div class="w-full max-w-5xl flex flex-col items-center text-center gap-5">
            <h1 class="text-3xl md:text-5xl font-semibold text-gray-900 tracking-tight">
                BOOKING CATALOG
            </h1>
            <p class="text-gray-600 text-sm md:text-base max-w-2xl leading-relaxed">
                Pilih layanan fotografi yang sesuai dengan kebutuhan Anda.
                Setiap paket kami dirancang untuk memberikan hasil terbaik
                dan pengalaman profesional bersama <strong>Storyframe</strong>.
            </p>
            <a href="{{ url('/') }}"
                class="mt-4 text-sm px-5 py-2.5 rounded-full border border-gray-300 hover:bg-gray-100 transition">
                ← Kembali ke Beranda
            </a>
        </div>
    </section>

    <!-- Catalog Grid Section -->
    <section class="w-full bg-zinc-100 -mt-10 md:-mt-16 px-4 md:px-8 lg:px-40 pb-20 md:pb-28">
  <div class="w-full max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">

    @php
      $fallback = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop';
    @endphp

    @forelse($services as $svc)
      @php
        // kalau pakai pivot harga:
        $minPrice = optional($svc->packages)->pluck('pivot.price')
                    ->filter(fn($v) => $v && $v > 0)
                    ->min();
      @endphp

      <a href="{{ route('booking.detail', $svc->id) }}"
         class="group bg-white rounded-[20px] shadow-md hover:shadow-xl transition-all overflow-hidden flex flex-col">
        <div class="overflow-hidden">
          <img src="{{ route('booking.services.thumb', $svc->id) }}" class="h-56 w-full object-cover transition-transform duration-500 group-hover:scale-105"alt="{{ $svc->name }}" />
        </div>
        <div class="p-6 flex flex-col gap-3 flex-1">
          <h3 class="text-lg md:text-xl font-semibold text-gray-900">{{ $svc->name }}</h3>
          @if($svc->description)
            <p class="text-gray-600 text-sm leading-relaxed">{{ $svc->description }}</p>
          @endif

          @if(!is_null($minPrice))
            <span class="mt-auto text-blue-600 font-semibold text-base">
              Mulai Rp {{ number_format($minPrice, 0, ',', '.') }}
            </span>
          @endif
        </div>
      </a>
    @empty
      <div class="col-span-full text-center text-gray-500">
        Belum ada layanan aktif. (Cek seeder & kolom <code>is_active</code>)
      </div>
    @endforelse

  </div>
</section>

    @endsection
