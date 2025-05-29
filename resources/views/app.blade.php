<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Storyframe')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/components/booking.js'])
</head>

<body class="font-sans text-gray-900"> {{-- Dihapus: flex flex-col min-h-screen --}}

    @if (!isset($hideNavbar))
        <x-navbar />
    @endif

    <main>
        @yield('content')
    </main>

</body>

</html>