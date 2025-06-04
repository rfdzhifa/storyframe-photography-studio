<section id="services" class="bg-white py-16">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">

        {{-- Judul Section --}}
        <div class="mb-10 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-wide mb-4">OUR <span
                    class="block">SERVICES</span></h2>
        </div>

        {{-- Kategori layanan --}}
        <div class="flex flex-wrap justify-center gap-3 mb-10" id="category-buttons">
            @php
                $categories = [
                    'GRADUATION',
                    'WEDDING PHOTO',
                    'PERSONAL BRANDING',
                    'EVENT COVERAGE',
                    'FAMILY PORTRAITS',
                ];
            @endphp

            @foreach ($categories as $index => $category)
                <button data-category="{{ strtolower(str_replace(' ', '-', $category)) }}"
                    class="category-button {{ $index === 2 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }} px-5 py-2 rounded-full text-sm font-medium hover:bg-blue-500 hover:text-white transition">
                    {{ $category }}
                </button>
            @endforeach
        </div>

        {{-- Gambar layanan horizontal --}}
        <div id="image-gallery" class="flex flex-wrap gap-6 overflow-x-auto">
            {{-- GRADUATION --}}
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="graduation">
                <img src="{{ asset('assets/images/graduation1.jpg') }}" alt="Graduation 1" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="graduation">
                <img src="{{ asset('assets/images/graduation2.jpg') }}" alt="Graduation 2" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="graduation">
                <img src="{{ asset('assets/images/graduation3.jpg') }}" alt="Graduation 3" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="graduation">
                <img src="{{ asset('assets/images/graduation4.jpg') }}" alt="Graduation 4" class="w-full h-72 object-cover">
            </div>

            {{-- WEDDING --}}
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="wedding-photo">
                <img src="{{ asset('assets/images/wedding1.jpg') }}" alt="Wedding 1" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="wedding-photo">
                <img src="{{ asset('assets/images/wedding2.jpg') }}" alt="Wedding 2" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="wedding-photo">
                <img src="{{ asset('assets/images/wedding3.jpg') }}" alt="Wedding 3" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="wedding-photo">
                <img src="{{ asset('assets/images/wedding4.jpg') }}" alt="Wedding 4" class="w-full h-72 object-cover">
            </div>

            {{-- BRANDING --}}
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="personal-branding">
                <img src="{{ asset('assets/images/branding1.jpeg') }}" alt="Branding 1" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="personal-branding">
                <img src="{{ asset('assets/images/branding2.jpeg') }}" alt="Branding 2" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="personal-branding">
                <img src="{{ asset('assets/images/branding3.jpeg') }}" alt="Branding 3" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="personal-branding">
                <img src="{{ asset('assets/images/branding4.jpeg') }}" alt="Branding 4" class="w-full h-72 object-cover">
            </div>

            {{-- EVENT --}}
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="event-coverage">
                <img src="{{ asset('assets/images/event1.jpg') }}" alt="Event" class="w-full h-72 object-cover">
            </div>

            {{-- FAMILY --}}
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="family-portraits">
                <img src="{{ asset('assets/images/family1.jpg') }}" alt="Family 1" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="family-portraits">
                <img src="{{ asset('assets/images/family2.jpg') }}" alt="Family 2" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="family-portraits">
                <img src="{{ asset('assets/images/family3.jpg') }}" alt="Family 3" class="w-full h-72 object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden min-w-[300px]" data-category="family-portraits">
                <img src="{{ asset('assets/images/family4.jpg') }}" alt="Family 4" class="w-full h-72 object-cover">
            </div>
        </div>


    </div>
</section>

{{-- Script Filter --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.category-button');
        const images = document.querySelectorAll('#image-gallery [data-category]');

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const selectedCategory = button.getAttribute('data-category');

                // Update button styles
                buttons.forEach(btn => btn.classList.remove('bg-blue-600', 'text-white'));
                button.classList.add('bg-blue-600', 'text-white');

                // Show/hide images
                images.forEach(image => {
                    if (image.getAttribute('data-category') === selectedCategory) {
                        image.style.display = 'block';
                    } else {
                        image.style.display = 'none';
                    }
                });
            });
        });

        // Set default selected category (e.g. Personal Branding)
        document.querySelector('.category-button[data-category="personal-branding"]').click();
    });
</script>
