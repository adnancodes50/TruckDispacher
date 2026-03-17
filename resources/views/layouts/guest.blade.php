<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Truck Dispatcher') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/shiping_icon1.png') }}">
</head>

<body class="font-sans antialiased bg-brand-teal">

    <div class="min-h-screen flex flex-col justify-center items-center bg-[radial-gradient(1200px_circle_at_20%_10%,rgba(255,255,255,0.14),transparent_40%),radial-gradient(900px_circle_at_90%_20%,rgba(239,68,68,0.14),transparent_45%),linear-gradient(180deg,#0b6b6b_0%,#064949_100%)]">

        <!-- Logo -->
        <div class="mb-4">
            <a href="/">
                <img src="{{ asset('images/shiping_icon1.png') }}"
                     alt="Truck Dispatch Logo"
                     class="w-24 h-24 object-contain">
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-full sm:max-w-md px-6 py-6 bg-white/95 backdrop-blur shadow-xl rounded-2xl ring-1 ring-black/5">
            {{ $slot }}
        </div>

    </div>

</body>

</html>

