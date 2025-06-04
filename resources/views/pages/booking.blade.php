@php($hideNavbar = true)

@extends('app')

@section('title', content: 'Booking')

@section('content')

  <form id="booking-form" data-url="{{ route('booking.store') }}" method="POST">
    @csrf

    <!-- Step 1: Contact Information -->
    <section id="step1"
    class="full-page-section w-full min-h-screen py-16 bg-zinc-100 flex flex-col justify-between items-center px-4 md:px-40">

    <div class="w-full">
      <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-8">
      READY TO CAPTURE<br>YOUR STORY?
      </h2>

      <div class="w-full self-stretch inline-flex flex-col justify-center items-start gap-5">
      <!-- Full Name -->
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

      <!-- Email -->
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

      <!-- Phone Number -->
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
      </div>
    </div>

    <div class="w-full flex justify-end">
      <button onclick="nextStep(2)" type="button"
      class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition">
      Continue
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      </button>
    </div>
    </section>

    <!-- Step 2: Service & Package -->
    <section id="step2"
    class="full-page-section w-full min-h-screen py-16 bg-zinc-100 flex flex-col justify-between items-center px-4 md:px-40 hidden">

    <div class="w-full">
      <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-8">
      CHOOSE YOUR<br>PACKAGE
      </h2>

      <div class="w-full self-stretch inline-flex flex-col justify-center items-start gap-5">
      <!-- Service -->
      <div class="w-full space-y-2">
        <label for="service" class="block text-sm font-medium text-gray-700">
        Service <span class="text-red-500">*</span>
        </label>
        <select id="service" name="service" onchange="loadPackages()"
        class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300">
        <option value="">Select a service</option>
        @foreach($services as $service)
      <option value="{{ $service->id }}">
        {{ $service->name }}
      </option>
      @endforeach
        </select>
        @error('service')
      <p class="text-sm text-red-500">{{ $message }}</p>
      @enderror
      </div>

      <!-- Package -->
      <div class="w-full space-y-2">
        <label for="package" class="block text-sm font-medium text-gray-700">
        Package <span class="text-red-500">*</span>
        </label>
        <select id="package" name="package" onchange="updatePrice()"
        class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300">
        <option value="">Select a package</option>
        <!-- Will be populated by JavaScript -->
        </select>
        @error('package')
      <p class="text-sm text-red-500">{{ $message }}</p>
      @enderror
      </div>

      <!-- Price Display -->
      <div id="price-display" class="w-full space-y-2 hidden">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
        <div class="flex justify-between items-center">
          <span class="text-sm font-medium text-gray-700">Total Price:</span>
          <span id="total-price" class="text-lg font-bold text-gray-900">-</span>
        </div>
        <div id="dp-info" class="mt-2 text-sm text-gray-600 hidden">
          <div class="flex justify-between">
          <span>Down Payment (50%):</span>
          <span id="dp-amount">-</span>
          </div>
        </div>
        </div>
      </div>

      <!-- Payment Options -->
      <div class="w-full space-y-2">
        <label class="block text-sm font-medium text-gray-700">
        Payment Options <span class="text-red-500">*</span>
        </label>
        <div class="w-full grid grid-cols-2 gap-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="payment" value="dp" {{ old('payment') == 'dp' ? 'checked' : '' }}
          onchange="toggleDpInfo()" class="text-gray-900 focus:ring-gray-300">
          <span class="text-sm text-gray-700">Down Payment (50%)</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="payment" value="full" {{ old('payment') == 'full' ? 'checked' : '' }}
          onchange="toggleDpInfo()" class="text-gray-900 focus:ring-gray-300">
          <span class="text-sm text-gray-700">Full Payment</span>
        </label>
        </div>
        @error('payment')
      <p class="text-sm text-red-500">{{ $message }}</p>
      @enderror
      </div>
      </div>
    </div>

    <div class="w-full flex justify-between">
      <button onclick="nextStep(1)" type="button"
      class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition">
      <svg class="h-4 w-4 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      Previous
      </button>
      <button onclick="nextStep(3)" type="button"
      class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition">
      Continue
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      </button>
    </div>
    </section>

    <!-- Step 3: Date & Notes -->
    <section id="step3"
    class="full-page-section w-full min-h-screen py-16 bg-zinc-100 flex flex-col justify-between items-center px-4 md:px-40 hidden">

    <div class="w-full">
      <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-8">
      SCHEDULE YOUR<br>SESSION
      </h2>

      <div class="w-full self-stretch inline-flex flex-col justify-center items-start gap-5">
      <div class="w-full flex gap-4">
        <!-- Preferred Date -->
        <div class="flex-1 space-y-2">
        <label for="booking_date" class="block text-sm font-medium text-gray-700">
          Preferred Date <span class="text-red-500">*</span>
        </label>
        <input type="date" id="booking_date" name="booking_date" value="{{ old('booking_date') }}"
          onchange="loadAvailableSchedules()" min="{{ date('Y-m-d') }}"
          class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300" />
        @error('booking_date')
      <p class="text-sm text-red-500">{{ $message }}</p>
      @enderror
        </div>

        <!-- Preferred Time -->
        <div class="flex-1 space-y-2">
        <label for="preferred_time" class="block text-sm font-medium text-gray-700">
          Preferred Time <span class="text-red-500">*</span>
        </label>
        <select id="preferred_time" name="preferred_time"
          class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300">
          <option value="">Select date first</option>
        </select>
        @error('preferred_time')
      <p class="text-sm text-red-500">{{ $message }}</p>
      @enderror
        </div>
      </div>

      <!-- Notes -->
      <div class="w-full space-y-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">
        Notes
        </label>
        <textarea id="notes" name="notes" rows="4" placeholder="Any special requests or additional information..."
        class="w-full rounded-2xl border border-gray-300 bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 resize-none">{{ old('notes') }}</textarea>
      </div>
      </div>
    </div>

    <div class="w-full flex justify-between">
      <button onclick="nextStep(2)" type="button"
      class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition">
      <svg class="h-4 w-4 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      Previous
      </button>
      <button id="submit-button" type="submit"
      class="inline-flex items-center gap-2 rounded-full bg-blue-500 px-6 py-2 text-sm font-medium text-white hover:bg-gray-800 transition">
      Book Now
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
      </button>
    </div>
    </section>

  </form>

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
          <p class="text-sm text-gray-500">Yakin mau lanjutkan pemesanan ini? Pastikan semua datanya udah bener ya!
          </p>
        </div>
        </div>
      </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
      <button id="confirmYes" type="button"
        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto">Ya,
        lanjut</button>
      <button id="confirmNo" type="button"
        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 sm:mt-0 sm:w-auto">Batal</button>
      </div>
    </div>
    </div>
  </div>

@endsection