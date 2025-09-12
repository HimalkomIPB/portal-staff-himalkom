<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/himalkom_logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased dark:bg-black dark:text-white/50">
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img id="background" class="absolute w-screen h-screen object-cover" src="{{ asset('images/bg1.webp') }}"
            alt="background" />
        <div
            class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                </header>


                <main class="mt-6">
                    <div class="flex justify-center">
                        <x-application-logo width="150" height="150" />
                    </div>
                    @if (Route::has('login'))
                        <nav class="-mx-3 flex flex-col justify-center items-center py-12">
                            @auth
                                <div class="flex flex-col justify-center items-center text-center space-y-2">
                                    <p class="text-white">Hallo, {{ Auth::user()->name }} !</p>
                                    @php
                                        $dashboardUrl = Auth::user()->getDashboardRoute();
                                        $linkText = auth()
                                            ->user()
                                            ->hasAnyRole(['managing director', 'bph', 'supervisor', 'pjs'])
                                            ? 'Dashboard'
                                            : '-';
                                    @endphp
                                    <a href="{{ $dashboardUrl }}"
                                        class="block w-[120px] text-center mt-2 font-bold rounded-md px-3 py-2 bg-green-500 text-white transition hover:bg-green-700 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500">
                                        {{ $linkText }}
                                    </a>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-[120px] mt-2 font-bold rounded-md px-3 py-2 bg-red-600 text-white transition hover:bg-red-700 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                        Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                    class="font-[1000] rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 hover:scale-125 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                    Log in
                                </a>
                            @endauth
                        </nav>
                    @endif
                </main>

                @include('components.footer')
            </div>
        </div>
    </div>
</body>

</html>
