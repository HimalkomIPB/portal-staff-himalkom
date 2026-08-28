@props(['navigation' => 'layouts.navigation'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/himalkom_logo_bw.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/js/utils/formSubmitHandler.js']) <!-- prevent spamming form submit -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

    <!-- FilePond -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <!-- Select 2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</head>

<body class="font-sans antialiased">
    @include($navigation)

    <div class="flex flex-col bg-cover bg-center bg-no-repeat min-h-[92dvh]"
        style="background-image: url('{{ asset('images/bg4.jpg') }}');">

        <!-- Page Heading -->
        <div class="flex-grow ">
            @php
                $pageHeader = $header ?? trim($__env->yieldContent('header'));
            @endphp

            @if (!empty($pageHeader))
                <header class="">
                    <div class="max-w-[90dvw] mx-auto px-2 pt-4 pb-2 md:pt-6 md:px-3 ">
                        {!! $pageHeader !!}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-grow">
                {!! $slot ?? $__env->yieldContent('content') !!}
            </main>
        </div>

        @include('components.footer')
    </div>
</body>


</html>
