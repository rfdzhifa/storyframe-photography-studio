<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Storyframe')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/components/booking.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans text-gray-900"> {{-- Dihapus: flex flex-col min-h-screen --}}

    @if (!isset($hideNavbar))
        <x-navbar />
    @endif

    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>