<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Storyframe')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-['Inter_Tight'] text-gray-900 flex flex-col min-h-screen">

    <x-navbar />

    <main>
        @yield('content')
    </main>

</body>
</html>