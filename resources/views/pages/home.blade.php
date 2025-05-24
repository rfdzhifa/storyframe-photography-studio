@extends('app')

@section('title', 'Home')

@section('content')
    <section id="home" class="full-page-section w-full h-screen flex justify-center items-center"
         style="background-image: url('{{ asset('assets/images/bg-hero-image.png') }}'); background-size: cover; background-position: center;">

        <div class="flex flex-col justify-center items-center gap-2.5">
            <div class="flex flex-col justify-start items-center gap-6">
                <div class="py-3 flex flex-col justify-start items-center gap-5">

                    <div class="w-[627px] text-center text-white text-5xl font-medium">CAPTURING MOMENTS FRAMING STORIES</div>

                    <div class="w-[627px] text-center text-neutral-100 text-base font-normal">Storyframe adalah studio fotografi profesional yang berdedikasi untuk menjadikan setiap momen Anda sebagai kisah visual yang tak terlupakan.</div>
                </div>

                <a href="/booking" class="h-12 px-8 bg-white text-black text-sm font-medium rounded-full hover:bg-blue-100 transition flex justify-center items-center">
                    BOOKING
                </a>
            </div>
        </div>
    </section>

    <section id="about" class="full-page-section w-full h-screen bg-zinc-100 px-8 md:px-40 py-24 md:py-36 flex flex-col justify-center items-center gap-14">
        
        {{-- Title --}}
        <h2 class="text-center text-gray-900 text-5xl font-medium">WHY CHOOSE US?</h2>

        {{-- Cards Container --}}
        <div class="self-stretch flex flex-col md:flex-row justify-center items-stretch gap-8">

            {{-- Card 1 --}}
            <div class="flex-1 px-10 py-14 bg-white rounded-[40px] shadow-lg flex flex-col items-start gap-10">
                <div class="size-20 bg-zinc-100 rounded-full flex justify-center items-center">
                    <span class="text-2xl font-medium">🔥</span>
                </div>
                <div class="flex flex-col items-start gap-3">
                    <h3 class="text-gray-900 text-2xl font-medium">PROFESSIONAL TEAM</h3>
                    <p class="text-gray-600 text-base font-normal leading-relaxed">Fotografer kami memiliki keahlian teknis tinggi dan sensitivitas artistik untuk menangkap esensi setiap momen.</p>
                </div>
            </div>

            {{-- Card 2 (Blue) --}}
            <div class="flex-1 px-10 py-14 bg-blue-600 rounded-[40px] shadow-lg flex flex-col items-start gap-10 text-white"> {{-- Added text-white here --}}
                <div class="size-20 bg-white rounded-full flex justify-center items-center">
                    <span class="text-2xl font-medium">📷</span>
                </div>
                <div class="flex flex-col items-start gap-3">
                    <h3 class="text-white text-2xl font-medium">COMFORTABLE PLACE</h3>
                    <p class="text-gray-200 text-base font-normal leading-relaxed">Dirancang dengan desain yang estetis dan fasilitas lengkap, studio kami siap menunjang sesi foto apapun.</p>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="flex-1 px-10 py-14 bg-white rounded-[40px] shadow-lg flex flex-col items-start gap-10">
                <div class="size-20 bg-zinc-100 rounded-full flex justify-center items-center">
                    <span class="text-2xl font-medium">⚡️</span>
                </div>
                <div class="flex flex-col items-start gap-3">
                    <h3 class="text-gray-900 text-2xl font-medium">FAST DIGITAL PROCESS</h3>
                    <p class="text-gray-600 text-base font-normal leading-relaxed">Mulai dari pemesanan hingga pengiriman hasil akhir—semuanya terintegrasi secara digital untuk pengalaman yang efisien dan mudah.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Tempat untuk section lain jika ada --}}

@endsection