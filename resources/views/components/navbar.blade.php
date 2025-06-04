{{-- Container Luar: Mengatur Posisi Absolute & Padding --}}
<div id="main-navbar-wrapper" class="fixed w-full top-0 left-0 z-50 p-3 md:p-6 transition-transform duration-300 ease-in-out">

  {{-- Navbar Internal: Mengatur Background, Border-Radius & Shadow --}}
  <nav class="w-full px-4 md:px-6 py-2 flex justify-between items-center bg-white shadow-xl rounded-full">

    {{-- Logo Section --}}
    <div class="flex items-center gap-2">
      <img src="{{ asset('assets/svg/logo.svg') }}" alt="Storyframe Logo" class="w-8 h-8 md:w-10 md:h-10">
      <span class="text-blue-600 text-base md:text-lg font-semibold">Storyframe</span>
    </div>

    {{-- Desktop Menu --}}
    <div class="hidden lg:flex items-center gap-6">
      <a href="#home" class="text-gray-500 text-sm hover:text-blue-600 transition">HOME</a>
      <a href="#about" class="text-gray-500 text-sm hover:text-blue-600 transition">ABOUT</a>
      <a href="#gallery" class="text-gray-500 text-sm hover:text-blue-600 transition">GALLERY</a>
    </div>

    {{-- Desktop Booking Button --}}
    <a href="{{ route('booking.index') }}"
      class="hidden lg:flex h-10 lg:h-12 px-4 lg:px-8 bg-blue-600 text-white text-xs lg:text-sm font-medium rounded-full hover:bg-blue-700 transition justify-center items-center">
      BOOKING
    </a>

    {{-- Mobile Menu Button --}}
    <button
      id="mobile-menu-toggle"
      class="lg:hidden flex flex-col justify-center items-center w-8 h-8 space-y-1 group">
      <span class="w-5 h-0.5 bg-gray-600 transition-all duration-300 group-hover:bg-blue-600"></span>
      <span class="w-5 h-0.5 bg-gray-600 transition-all duration-300 group-hover:bg-blue-600"></span>
      <span class="w-5 h-0.5 bg-gray-600 transition-all duration-300 group-hover:bg-blue-600"></span>
    </button>
  </nav>

  {{-- Mobile Menu Dropdown --}}
  <div id="mobile-menu" class="lg:hidden hidden mt-2 bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="py-4 px-6 space-y-3">
      <a href="#home" class="block text-gray-600 text-sm hover:text-blue-600 transition py-2">HOME</a>
      <a href="#about" class="block text-gray-600 text-sm hover:text-blue-600 transition py-2">ABOUT</a>
      <a href="#gallery" class="block text-gray-600 text-sm hover:text-blue-600 transition py-2">GALLERY</a>
      <a href="#contact" class="block text-gray-600 text-sm hover:text-blue-600 transition py-2">CONTACT</a>

      <div class="pt-3 border-t border-gray-100">
        <a href="{{ route('booking.index') }}"
          class="block w-full h-12 px-6 bg-blue-600 text-white text-sm font-medium rounded-full hover:bg-blue-700 transition flex justify-center items-center">
          BOOKING
        </a>
      </div>
    </div>
  </div>
</div>

{{-- JavaScript untuk Mobile Menu Toggle --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    mobileMenuToggle.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');

        // Animate hamburger menu
        const spans = mobileMenuToggle.querySelectorAll('span');
        if (mobileMenu.classList.contains('hidden')) {
            spans[0].style.transform = 'rotate(0deg) translateY(0px)';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'rotate(0deg) translateY(0px)';
        } else {
            spans[0].style.transform = 'rotate(45deg) translateY(6px)';
            spans[1].style.opacity = '0';
            spans[2].style.transform = 'rotate(-45deg) translateY(-6px)';
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!mobileMenuToggle.contains(event.target) && !mobileMenu.contains(event.target)) {
            mobileMenu.classList.add('hidden');

            // Reset hamburger menu
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans[0].style.transform = 'rotate(0deg) translateY(0px)';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'rotate(0deg) translateY(0px)';
        }
    });

    // Close mobile menu when window is resized to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            mobileMenu.classList.add('hidden');

            // Reset hamburger menu
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans[0].style.transform = 'rotate(0deg) translateY(0px)';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'rotate(0deg) translateY(0px)';
        }
    });
});
</script>
