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

    <section class="full-page-section w-full h-screen bg-zinc-100 px-8 md:px-40 py-24 md:py-24 flex flex-col justify-center items-center gap-14">
        <!-- Judul -->
        <h2 class="text-3xl md:text-6xl font-semibold text-center text-gray-900 font-['Inter_Tight']"> 
          SEE<span class="mx-1">👀</span> OUR <br class="md:hidden" />PORTFOLIOEYE</span>
        </h2>
      
        <div class="w-0.80 flex-1 flex flex-col md:flex-row gap-6 min-h-0">
    
            <div class="flex-1 flex flex-col gap-6 min-h-0">
                <img src="{{ asset('assets/images/img1.png')}}" class="w-full h-full object-cover rounded-[40px]" />
            </div>
     
          <div class="flex-1 flex flex-col gap-6 min-h-0">
            <div class="flex-[0.5] min-h-0">
              <img src="{{ asset('assets/images/img2.png')}}" class="w-full h-full object-cover rounded-[40px]" />
            </div>
            <div class="flex-[0.5] min-h-0">
              <img src="{{ asset('assets/images/img3.png')}}" class="w-full h-full object-cover rounded-[40px]" />
            </div>
          </div>
      
          <div class="flex-1 flex flex-col gap-6 min-h-0">
            <div class="flex-[0.6] min-h-0">
              <img src="{{ asset('assets/images/img4.png')}}" class="w-full h-full object-cover rounded-[40px]" />
            </div>
            <div class="flex-[0.4] min-h-0">
              <img src="{{ asset('assets/images/img5.png')}}" class="w-full h-full object-cover rounded-[40px]" />
            </div>
          </div>
      
        </div>
      </section>
      
      <section id="services-section" class="full-page-section w-full h-screen bg-zinc-100 px-4 py-16 md:py-24 flex flex-col items-center gap-8 md:gap-12 overflow-hidden">
        <h2 class="text-3xl md:text-5xl font-semibold text-center text-gray-900 flex-shrink-0">
            OUR SERVICES
        </h2>
    
        {{-- Tabs Container --}}
        <div id="service-tabs-container" class="flex flex-wrap justify-center gap-3 md:gap-4 px-4">
            {{-- Tabs akan digenerate oleh JavaScript dari data JSON --}}
        </div>
    
        {{-- Gallery Viewport --}}
        <div class="w-full flex-1 relative flex justify-center items-center overflow-hidden group">
            {{-- Gallery Slider (yang akan di-scroll) --}}
            <div id="gallery-slider" class="absolute top-0 left-0 w-full h-full flex items-center transition-transform duration-500 ease-in-out" style="transform: translateX(0%);">
                {{-- Gambar akan digenerate oleh JavaScript --}}
            </div>
    
            {{-- Tombol Navigasi Galeri (Opsional) --}}
            <button id="prev-image" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-2 bg-black/30 hover:bg-black/50 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button id="next-image" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-2 bg-black/30 hover:bg-black/50 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </section>

@endsection