{{-- Container Luar: Mengatur Posisi Absolute & Padding --}}
<div id="main-navbar-wrapper" class="fixed w-full top-0 left-0 z-50 p-6 transition-transform duration-300 ease-in-out">

  {{-- Navbar Internal: Mengatur Background, Border-Radius & Shadow --}}
  <nav class="w-full px-6 py-2 flex justify-between items-center bg-white shadow-xl rounded-full">
      
      <div class="flex items-center gap-2">
          <img src="{{ asset('assets/svg/logo.svg') }}" alt="Storyframe Logo" class="w-10 h-10">
          <span class="text-blue-600 text-lg font-semibold">Storyframe</span>
      </div>    
    
      <div class="flex items-center gap-6">
        <a href="#home" class="text-gray-500 text-sm hover:text-blue-600 transition">HOME</a>
        <a href="#home" class="text-gray-500 text-sm hover:text-blue-600 transition">ABOUT</a>
        <a href="#gallery" class="text-gray-500 text-sm hover:text-blue-600 transition">GALLERY</a>
        <a href="#services" class="text-gray-500 text-sm hover:text-blue-600 transition">SERVICES</a>
        <a href="#contact" class="text-gray-500 text-sm hover:text-blue-600 transition">CONTACT</a>
      </div>
    
  <a href="{{ route('booking.form') }}" class="h-12 px-8 bg-blue-600 text-white text-sm font-medium rounded-full hover:bg-blue-700 transition flex justify-center items-center">
        BOOKING
    </a>
  </nav>

</div>