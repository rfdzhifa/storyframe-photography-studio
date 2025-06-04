@extends('app')

@section('title', 'Home')

<style>
    @keyframes scrollLeft {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .animate-scrollLeft {
        animation: scrollLeft 20s linear infinite;
        width: max-content;
    }

    /* Responsive font sizes */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem !important;
            line-height: 1.2 !important;
        }

        .hero-subtitle {
            font-size: 0.875rem !important;
        }

        .section-title {
            font-size: 2rem !important;
        }

        .card-title {
            font-size: 1.25rem !important;
        }
    }

    @media (max-width: 480px) {
        .hero-title {
            font-size: 1.5rem !important;
        }

        .section-title {
            font-size: 1.75rem !important;
        }
    }
</style>

@section('content')
    <!-- Hero Section -->
    <section id="home" class="full-page-section w-full min-h-screen flex justify-center items-center px-4"
        style="background-image: url('{{ asset('assets/images/bg-hero-image.png') }}'); background-size: cover; background-position: center;">

        <div class="flex flex-col justify-center items-center gap-2.5 max-w-4xl mx-auto">
            <div class="flex flex-col justify-start items-center gap-6">
                <div class="py-3 flex flex-col justify-start items-center gap-5">

                    <div class="w-full max-w-2xl text-center text-white hero-title text-3xl md:text-5xl font-medium px-4">
                        CAPTURING MOMENTS FRAMING STORIES
                    </div>

                    <div
                        class="w-full max-w-2xl text-center text-neutral-100 hero-subtitle text-sm md:text-base font-normal px-4">
                        Storyframe adalah studio fotografi profesional yang berdedikasi untuk menjadikan setiap momen Anda
                        sebagai kisah visual yang tak terlupakan.
                    </div>
                </div>

                <a href="/booking"
                    class="h-10 md:h-12 px-6 md:px-8 bg-white text-black text-xs md:text-sm font-medium rounded-full hover:bg-blue-100 transition flex justify-center items-center">
                    BOOKING
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about"
        class="full-page-section w-full min-h-screen bg-zinc-100 px-4 md:px-8 lg:px-40 py-16 md:py-24 lg:py-36 flex flex-col justify-center items-center gap-8 md:gap-14">

        <!-- Title -->
        <h2 class="text-center text-gray-900 section-title text-3xl md:text-5xl font-medium">WHY CHOOSE US?</h2>

        <!-- Cards Container -->
        <div class="w-full max-w-7xl flex flex-col lg:flex-row justify-center items-stretch gap-4 md:gap-8">

            <!-- Card 1 -->
            <div
                class="flex-1 px-6 md:px-10 py-8 md:py-14 bg-white rounded-[20px] md:rounded-[40px] shadow-lg flex flex-col items-start gap-6 md:gap-10">
                <div class="size-16 md:size-20 bg-zinc-100 rounded-full flex justify-center items-center">
                    <span class="text-xl md:text-2xl font-medium">🔥</span>
                </div>
                <div class="flex flex-col items-start gap-3">
                    <h3 class="text-gray-900 card-title text-xl md:text-2xl font-medium">PROFESSIONAL TEAM</h3>
                    <p class="text-gray-600 text-sm md:text-base font-normal leading-relaxed">Fotografer kami memiliki
                        keahlian teknis tinggi dan sensitivitas artistik untuk menangkap esensi setiap momen.</p>
                </div>
            </div>

            <!-- Card 2 (Blue) -->
            <div
                class="flex-1 px-6 md:px-10 py-8 md:py-14 bg-blue-600 rounded-[20px] md:rounded-[40px] shadow-lg flex flex-col items-start gap-6 md:gap-10 text-white">
                <div class="size-16 md:size-20 bg-white rounded-full flex justify-center items-center">
                    <span class="text-xl md:text-2xl font-medium">📷</span>
                </div>
                <div class="flex flex-col items-start gap-3">
                    <h3 class="text-white card-title text-xl md:text-2xl font-medium">COMFORTABLE PLACE</h3>
                    <p class="text-gray-200 text-sm md:text-base font-normal leading-relaxed">Dirancang dengan desain yang
                        estetis dan fasilitas lengkap, studio kami siap menunjang sesi foto apapun.</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div
                class="flex-1 px-6 md:px-10 py-8 md:py-14 bg-white rounded-[20px] md:rounded-[40px] shadow-lg flex flex-col items-start gap-6 md:gap-10">
                <div class="size-16 md:size-20 bg-zinc-100 rounded-full flex justify-center items-center">
                    <span class="text-xl md:text-2xl font-medium">⚡️</span>
                </div>
                <div class="flex flex-col items-start gap-3">
                    <h3 class="text-gray-900 card-title text-xl md:text-2xl font-medium">FAST DIGITAL PROCESS</h3>
                    <p class="text-gray-600 text-sm md:text-base font-normal leading-relaxed">Mulai dari pemesanan hingga
                        pengiriman hasil akhir—semuanya terintegrasi secara digital untuk pengalaman yang efisien dan mudah.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="gallery"
    section class="full-page-section w-full min-h-screen bg-zinc-100 px-4 md:px-8 lg:px-40 py-16 md:py-24 flex flex-col justify-center items-center gap-8 md:gap-14">
        <!-- Title -->
        <h2 class="text-2xl md:text-4xl lg:text-6xl font-semibold text-center text-gray-900 font-['Inter_Tight'] px-4">
            SEE<span class="mx-1">👀</span> OUR <br class="sm:hidden" />PORTFOLIO
        </h2>

        <div class="w-full max-w-7xl flex-1 flex flex-col lg:flex-row gap-4 md:gap-6 min-h-0">

            <div class="flex-1 flex flex-col gap-4 md:gap-6 min-h-0 h-64 md:h-auto">
                <img src="{{ asset('assets/images/img1.png') }}"
                    class="w-full h-full object-cover rounded-[20px] md:rounded-[40px]" />
            </div>

            <div class="flex-1 flex flex-col gap-4 md:gap-6 min-h-0">
                <div class="flex-[0.5] min-h-0 h-32 md:h-auto">
                    <img src="{{ asset('assets/images/img2.png') }}"
                        class="w-full h-full object-cover rounded-[20px] md:rounded-[40px]" />
                </div>
                <div class="flex-[0.5] min-h-0 h-32 md:h-auto">
                    <img src="{{ asset('assets/images/img3.png') }}"
                        class="w-full h-full object-cover rounded-[20px] md:rounded-[40px]" />
                </div>
            </div>

            <div class="flex-1 flex flex-col gap-4 md:gap-6 min-h-0">
                <div class="flex-[0.6] min-h-0 h-40 md:h-auto">
                    <img src="{{ asset('assets/images/img4.png') }}"
                        class="w-full h-full object-cover rounded-[20px] md:rounded-[40px]" />
                </div>
                <div class="flex-[0.4] min-h-0 h-24 md:h-auto">
                    <img src="{{ asset('assets/images/img5.png') }}"
                        class="w-full h-full object-cover rounded-[20px] md:rounded-[40px]" />
                </div>
            </div>

        </div>
    </section>

    <!-- Location Section -->
    <section id="location"
        class="full-page-section w-full min-h-screen bg-zinc-100 px-4 md:px-8 lg:px-40 py-16 md:py-24 lg:py-36 flex flex-col justify-center items-center gap-8">
        <h2 class="text-2xl md:text-3xl lg:text-5xl font-semibold text-center text-gray-900 mb-8 px-4">LOCATION</h2>

        <div class="w-full max-w-6xl shadow-lg rounded-xl overflow-hidden">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.270756540308!2d109.24651767484053!3d-7.43526309257557!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655ea49d9f9885%3A0x62be0b6159700ec9!2sTelkom%20Purwokerto%20University!5e0!3m2!1sid!2sid!4v1748779946343!5m2!1sid!2sid"
                width="100%" height="300" class="md:h-[450px]" style="border:0" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

    </section>

    <!-- Footer Section -->
    <section id="footer"
        class="w-full bg-blue-600 py-12 md:py-16 flex flex-col items-center gap-8 md:gap-12 text-white min-h-screen">

        <div class="max-w-5xl flex flex-col items-center gap-6 px-4 text-center">
            <h2 class="text-2xl md:text-3xl lg:text-5xl font-semibold leading-snug">
                WANT TO TAKE A <br /> PHOTO? JUST LET <br /> US KNOW!
            </h2>

            <div class="overflow-hidden relative w-full max-w-4xl mx-auto">
                <div class="flex gap-2 md:gap-4 animate-scrollLeft" style="width: 200%">
                    <img src="{{ asset('assets/images/img1.png') }}" alt="img1"
                        class="w-48 md:w-80 lg:w-[408px] h-32 md:h-48 lg:h-[319px] object-cover rounded-lg md:rounded-2xl flex-shrink-0" />
                    <img src="{{ asset('assets/images/img2.png') }}" alt="img2"
                        class="w-48 md:w-80 lg:w-[408px] h-32 md:h-48 lg:h-[319px] object-cover rounded-lg md:rounded-2xl flex-shrink-0" />
                    <img src="{{ asset('assets/images/img3.png') }}" alt="img3"
                        class="w-48 md:w-80 lg:w-[408px] h-32 md:h-48 lg:h-[319px] object-cover rounded-lg md:rounded-2xl flex-shrink-0" />
                    <img src="{{ asset('assets/images/img4.png') }}" alt="img4"
                        class="w-48 md:w-80 lg:w-[408px] h-32 md:h-48 lg:h-[319px] object-cover rounded-lg md:rounded-2xl flex-shrink-0" />
                    <img src="{{ asset('assets/images/img5.png') }}" alt="img5"
                        class="w-48 md:w-80 lg:w-[408px] h-32 md:h-48 lg:h-[319px] object-cover rounded-lg md:rounded-2xl flex-shrink-0" />
                </div>
            </div>

            <a href="/booking"
                class="mt-6 bg-white text-black text-xs md:text-sm font-semibold px-4 md:px-6 py-2 rounded-full hover:bg-blue-100 transition">BOOKING</a>
        </div>

        <div
            class="w-full max-w-5xl px-4 py-8 mx-auto flex flex-col items-center text-xs text-white font-light tracking-widest space-y-6">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('assets/svg/logo-white.svg') }}" alt="Storyframe Logo"
                        class="w-5 h-5 md:w-6 md:h-6" />
                    <span class="text-sm tracking-widest font-medium">Storyframe</span>
                </div>
                <a href="mailto:hello@storyframe"
                    class="text-white underline text-xs tracking-wide mt-1">HELLO@STORYFRAME</a>
            </div>

            <div class="w-full flex flex-col md:flex-row justify-between items-center gap-4 text-xs">
                <div class="flex flex-wrap justify-center gap-4 md:gap-6">
                    <a href="https://www.instagram.com/telkomuniversity_purwokerto?igsh=N2llNWdodTdna3Q5"
                        class="hover:underline">INSTAGRAM</a>
                    <a href="https://www.tiktok.com/@smbtelkom.purwokerto?_t=ZS-8wuIzQktHm5&_r=1"
                        class="hover:underline">TIKTOK</a>
                    <a href="https://youtube.com/@telkomuniversitypurwokerto?si=dwnuDaxP0swsYLvv"
                        class="hover:underline">YOUTUBE</a>
                </div>

                <div class="flex gap-4">
                    <a href="#home" class="hover:underline tracking-wide">BACK TO TOP ↑</a>
                </div>
            </div>
        </div>

    </section>

@endsection
