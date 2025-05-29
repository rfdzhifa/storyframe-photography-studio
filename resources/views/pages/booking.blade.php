@php($hideNavbar = true)

@extends('app')

@section('title', 'Booking')

@section('content')

{{-- Menampilkan Pesan Error atau Sukses --}}
<div class="fixed top-5 right-5 z-50 max-w-sm">
    @if ($errors->any())
        <div class="mb-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-md" role="alert">
            <strong class="font-bold">Oops! Ada kesalahan:</strong>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-md" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline text-sm">{{ session('error') }}</span>
        </div>
    @endif
    @if (session('success'))
        <div class="mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-md" role="alert">
            <strong class="font-bold">Sukses!</strong>
            <span class="block sm:inline text-sm">{{ session('success') }}</span>
        </div>
    @endif
</div>

{{-- Form utama yang akan mengirim data ke route 'booking.store' --}}
<form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
    @csrf

    <section id="step1" class="full-page-section w-full min-h-screen py-16 bg-zinc-100 flex flex-col justify-between items-center px-4 md:px-40">
        <div class="w-full">
            <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-10">
                READY TO CAPTURE<br>YOUR STORY?
            </h2>
            <div class="w-full self-stretch inline-flex flex-col justify-center items-start gap-5">
                <div class="w-full space-y-2">
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full name <span class="text-red-500">*</span></label>
                    <input type="text" id="full_name" name="full_name" placeholder="What's your full name?" value="{{ old('full_name') }}" required
                        class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('full_name') border-red-500 @else border-gray-300 @enderror" />
                    @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="w-full space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" placeholder="Can we get your email?" value="{{ old('email') }}" required
                        class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('email') border-red-500 @else border-gray-300 @enderror" />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="w-full space-y-2">
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="e.g. +62 812 3456 7890" value="{{ old('phone_number') }}" required
                        class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('phone_number') border-red-500 @else border-gray-300 @enderror" />
                    @error('phone_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        <div class="w-full flex justify-end mt-8">
            <button type="button" onclick="nextStep(2)"
                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition shadow">
                Continue
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </section>

    <section id="step2" class="full-page-section w-full min-h-screen py-16 bg-zinc-100 flex flex-col justify-between items-center px-4 md:px-40 hidden">
        <div class="w-full">
            <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-10">CHOOSE YOUR<br>PACKAGE</h2>
            <div class="w-full self-stretch inline-flex flex-col justify-center items-start gap-5">
                <div class="w-full space-y-2">
                    <label for="service" class="block text-sm font-medium text-gray-700">Service <span class="text-red-500">*</span></label>
                    <select id="service" name="service" required
                        class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('service') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Select a service</option>
                        @foreach($services as $serviceItem)
                            <option value="{{ $serviceItem->id }}" {{ old('service') == $serviceItem->id ? 'selected' : '' }}>
                                {{ $serviceItem->name }}
                            </option>
                        @endforeach
                    </select>
                     @error('service') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="w-full space-y-2">
                    <label for="package" class="block text-sm font-medium text-gray-700">Package <span class="text-red-500">*</span></label>
                    <select id="package" name="package" required
                        class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('package') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Select a package</option>
                         @foreach($packages as $packageItem)
                            <option value="{{ $packageItem->id }}" {{ old('package') == $packageItem->id ? 'selected' : '' }}>
                                {{ $packageItem->name }}
                            </option>
                        @endforeach
                    </select>
                     @error('package') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="w-full space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Payment Options <span class="text-red-500">*</span></label>
                    <div class="w-full grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-2 cursor-pointer p-3 rounded-full border @error('payment') border-red-500 @else border-gray-300 @enderror bg-gray-50 has-[:checked]:bg-gray-200 has-[:checked]:border-gray-400">
                            <input type="radio" name="payment" value="dp" class="text-gray-900 focus:ring-gray-300" {{ old('payment', 'dp') == 'dp' ? 'checked' : '' }} required>
                            <span class="text-sm text-gray-700">Down Payment (50%)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer p-3 rounded-full border @error('payment') border-red-500 @else border-gray-300 @enderror bg-gray-50 has-[:checked]:bg-gray-200 has-[:checked]:border-gray-400">
                            <input type="radio" name="payment" value="full" class="text-gray-900 focus:ring-gray-300" {{ old('payment') == 'full' ? 'checked' : '' }} required>
                            <span class="text-sm text-gray-700">Full Payment</span>
                        </label>
                    </div>
                     @error('payment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        <div class="w-full flex justify-between mt-8">
            <button type="button" onclick="nextStep(1)"
                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition shadow">
                <svg class="h-4 w-4 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                Previous
            </button>
            <button type="button" onclick="nextStep(3)"
                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition shadow">
                Continue
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </section>

    <section id="step3" class="full-page-section w-full min-h-screen py-16 bg-zinc-100 flex flex-col justify-between items-center px-4 md:px-40 hidden">
        <div class="w-full">
            <h2 class="text-center text-gray-900 text-2xl md:text-4xl font-medium mb-10">SCHEDULE YOUR<br>SESSION</h2>
            <div class="w-full self-stretch inline-flex flex-col justify-center items-start gap-5">
                <div class="w-full flex flex-col md:flex-row gap-4">
                    <div class="flex-1 space-y-2">
                        <label for="preferred_date" class="block text-sm font-medium text-gray-700">Preferred Date <span class="text-red-500">*</span></label>
                        <input type="date" id="preferred_date" name="preferred_date" value="{{ old('preferred_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                            class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('preferred_date') border-red-500 @else border-gray-300 @enderror" />
                         @error('preferred_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex-1 space-y-2">
                        <label for="preferred_time" class="block text-sm font-medium text-gray-700">Preferred Time <span class="text-red-500">*</span></label>
                        <select id="preferred_time" name="preferred_time" required
                            class="w-full rounded-full border bg-gray-50 px-5 py-3 text-sm focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 @error('preferred_time') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Select time</option>
                            {{-- Idealnya, ini diisi oleh JS berdasarkan $availableSchedules dan tanggal yang dipilih --}}
                            {{-- Contoh statis: --}}
                            <option value="10:00-10:30" {{ old('preferred_time') == '10:00-10:30' ? 'selected' : '' }}>10:00 - 10:30</option>
                            <option value="10:30-11:00" {{ old('preferred_time') == '10:30-11:00' ? 'selected' : '' }}>10:30 - 11:00</option>
                            <option value="11:00-11:30" {{ old('preferred_time') == '11:00-11:30' ? 'selected' : '' }}>11:00 - 11:30</option>
                            <option value="11:30-12:00" {{ old('preferred_time') == '11:30-12:00' ? 'selected' : '' }}>11:30 - 12:00</option>
                            <option value="14:00-14:30" {{ old('preferred_time') == '14:00-14:30' ? 'selected' : '' }}>14:00 - 14:30</option>
                            <option value="14:30-15:00" {{ old('preferred_time') == '14:30-15:00' ? 'selected' : '' }}>14:30 - 15:00</option>
                            <option value="15:00-15:30" {{ old('preferred_time') == '15:00-15:30' ? 'selected' : '' }}>15:00 - 15:30</option>
                            <option value="15:30-16:00" {{ old('preferred_time') == '15:30-16:00' ? 'selected' : '' }}>15:30 - 16:00</option>
                        </select>
                        @error('preferred_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="w-full space-y-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Any special requests or additional information..."
                        class="w-full rounded-2xl border bg-gray-50 px-5 py-3 text-sm placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 resize-none @error('notes') border-red-500 @else border-gray-300 @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        <div class="w-full flex justify-between mt-8">
            <button type="button" onclick="nextStep(2)"
                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 transition shadow">
                <svg class="h-4 w-4 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                Previous
            </button>
            {{-- Tombol ini akan men-submit seluruh form --}}
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-6 py-2 text-sm font-medium text-white hover:bg-gray-800 transition shadow">
                Book Now
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </button>
        </div>
    </section>
</form>

@push('scripts')
<script src="{{ asset('../resources/js/components/booking.js') }}"></script>

@endsection