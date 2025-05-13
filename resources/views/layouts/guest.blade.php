<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/himalkom_logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

</head>

<body class=class="font-sans text-gray-900 antialiased bg-cover bg-center min-h-screen"
    style="background-image: url('{{ asset('images/bg1.webp') }}');">
    <div class="px-4 py-4 min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0">
        <div>
            <a href="/">
                <x-application-logo width="125" height="125" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 mx-auto px-6 py-4 bg-white shadow-xl overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>

    </div>
</body>

</html>
