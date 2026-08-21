@props(['title' => config('app.name', 'Portal Himalkom')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/himalkom_logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/js/utils/formSubmitHandler.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

    <!-- FilePond -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <!-- Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        /* Prevent sidebar flash on mobile while keeping it visible on desktop before Alpine loads */
        @media (min-width: 1024px) {
            aside[x-cloak] { display: flex !important; }
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#eef3f8] text-slate-900">
    @php
        $authUser = Auth::user();

        // Homepage href — filament admin, department dashboard, or welcome
        $homeHref = route('welcome');
        if ($authUser->can('user.manage') && Route::has('filament.superadmin.pages.dashboard')) {
            $homeHref = route('filament.superadmin.pages.dashboard');
        } elseif ($authUser->department) {
            $homeHref = route('dashboard', $authUser->department);
        }

        // Program Kerja
        $programHref = $authUser->department && $authUser->can('work-program.view')
            ? route('dashboard.workProgram.index', ['department' => $authUser->department])
            : null;

        // Documents / Archives
        $archiveHref = $authUser->can('archive.view')
            ? route('dashboard.archive.department.index')
            : null;

        // Supervisi / Mod-View
        $modviewHref = $authUser->can('archive.view-all')
            ? route('dashboard.modview.department.index')
            : null;

        // Performance
        $performanceHref = route('dashboard.performance.index');

        // Calendar
        $calendarHref = $authUser->can('agenda.view')
            ? route('dashboard.calendar.index')
            : null;

        // Unread notifications count
        $unreadCount = $authUser->unreadNotifications()->count();

        // Sidebar CSS helpers
        $sidebarItem     = 'flex items-center gap-3 rounded-full px-4 py-3 text-sm font-medium transition';
        $sidebarDefault  = 'text-slate-500 hover:bg-slate-100 hover:text-slate-900';
        $sidebarActive   = 'bg-[#0b5bd3] text-white shadow-md shadow-blue-700/20';
        $sidebarDisabled = 'text-slate-400 cursor-default opacity-70';

        // Helper closure: returns item + active/default class based on route pattern
        $navClass = function (string $routePattern) use ($sidebarItem, $sidebarActive, $sidebarDefault): string {
            return $sidebarItem . ' ' . (request()->routeIs($routePattern) ? $sidebarActive : $sidebarDefault);
        };

        // Avatar initials
        $avatarInitials = \App\Helpers\AvatarHelper::getInitials($authUser->name);
    @endphp

    {{-- ============================================================
         Alpine.js root — wraps the entire page
         ============================================================ --}}
    <div
        x-data="{ sidebarOpen: false }"
        class="min-h-screen lg:flex relative"
    >

    {{-- ===================== SIDEBAR ===================== --}}
    <aside x-cloak
        class="fixed z-40 flex flex-col transform bg-white shadow-2xl ring-1 ring-slate-900/5 transition-all duration-300
               top-4 left-4 right-4 max-h-[calc(100vh-2rem)] rounded-2xl
               lg:sticky lg:top-0 lg:left-auto lg:right-auto lg:h-screen lg:max-h-screen lg:w-64 lg:rounded-none lg:translate-x-0 lg:translate-y-0 lg:opacity-100 lg:scale-100 lg:shadow-none lg:ring-0"
        :class="sidebarOpen ? 'translate-y-0 opacity-100 scale-100' : '-translate-y-8 opacity-0 scale-95 pointer-events-none lg:pointer-events-auto'">
        <div class="flex flex-col min-h-0 lg:h-full lg:border-r lg:border-slate-200">

            {{-- Logo + close button --}}
            <div class="flex h-20 shrink-0 items-center justify-between px-5 lg:px-6">
                <a href="{{ $homeHref }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/himalkom_logo.svg') }}" alt="Portal Himalkom" class="h-8 w-8">
                    <span class="text-base font-bold text-[#0b5bd3] tracking-tight">Portal Himalkom</span>
                </a>
                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                    @click="sidebarOpen = false">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="px-6 pb-4 shrink-0">
                <label class="relative block">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    <input type="search" placeholder="Cari Menu..."
                        class="h-10 w-full rounded-full border border-slate-300 bg-[#eef2ff] pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                </label>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-6 pb-4">

                {{-- Homepage --}}
                @php
                    $homeClass = $sidebarItem . ' ' . (request()->routeIs('dashboard', 'welcome') ? $sidebarActive : $sidebarDefault);
                @endphp
                <div class="space-y-1">
                    <a href="{{ $homeHref }}" class="{{ $homeClass }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 11 9-8 9 8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M5 10v10h14V10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Homepage
                    </a>
                </div>

                <p class="mt-7 px-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Workspace</p>
                <div class="mt-3 space-y-1">
                    {{-- Program Kerja --}}
                    @if ($programHref)
                        <a href="{{ $programHref }}" class="{{ $navClass('dashboard.workProgram.*') }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 6h13M8 12h13M8 18h13" stroke-linecap="round" />
                                <path d="M3 6h.01M3 12h.01M3 18h.01" stroke-linecap="round" />
                            </svg>
                            Program Kerja
                        </a>
                    @else
                        <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 6h13M8 12h13M8 18h13" stroke-linecap="round" />
                                <path d="M3 6h.01M3 12h.01M3 18h.01" stroke-linecap="round" />
                            </svg>
                            Program Kerja
                        </span>
                    @endif

                    {{-- Layanan Antar Divisi --}}
                    @if ($authUser->department_id)
                        <a href="{{ route('dashboard.services.index') }}" class="{{ $navClass('dashboard.services.*') }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Layanan Antar Divisi
                        </a>
                    @else
                        <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Layanan Antar Divisi
                        </span>
                    @endif

                    {{-- Calendar --}}
                    @if ($calendarHref)
                        <a href="{{ $calendarHref }}" class="{{ $navClass('dashboard.calendar.*') }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 2v4M16 2v4M3 10h18" stroke-linecap="round" />
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                            </svg>
                            Calendar
                        </a>
                    @else
                        <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 2v4M16 2v4M3 10h18" stroke-linecap="round" />
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                            </svg>
                            Calendar
                        </span>
                    @endif
                </div>

                <p class="mt-7 px-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Administration</p>
                <div class="mt-3 space-y-1">
                    {{-- Documents / Archives --}}
                    @if ($archiveHref)
                        <a href="{{ $archiveHref }}" class="{{ $navClass('dashboard.archive.*') }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2h9l5 5v15H6z" stroke-linejoin="round" />
                                <path d="M14 2v6h6M9 13h6M9 17h6" stroke-linecap="round" />
                            </svg>
                            Documents
                        </a>
                    @else
                        <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2h9l5 5v15H6z" stroke-linejoin="round" />
                                <path d="M14 2v6h6M9 13h6M9 17h6" stroke-linecap="round" />
                            </svg>
                            Documents
                        </span>
                    @endif

                    {{-- Finance — always disabled --}}
                    <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="12" rx="2" />
                            <circle cx="12" cy="12" r="2" />
                            <path d="M6 12h.01M18 12h.01" stroke-linecap="round" />
                        </svg>
                        Finance
                    </span>
                </div>

                <p class="mt-7 px-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Organization</p>
                <div class="mt-3 space-y-1">
                    {{-- Members — always disabled --}}
                    <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 11a4 4 0 1 0-8 0M3 21a7 7 0 0 1 14 0" stroke-linecap="round" />
                            <path d="M18 8a3 3 0 0 1 0 6M21 21a5 5 0 0 0-4-4.9" stroke-linecap="round" />
                        </svg>
                        Members
                    </span>

                    {{-- Supervisi (Mod-View) — NEW item not in performance/index --}}
                    @if ($modviewHref)
                        <a href="{{ $modviewHref }}" class="{{ $navClass('dashboard.modview.*') }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Supervisi
                        </a>
                    @else
                        <span class="{{ $sidebarItem }} {{ $sidebarDisabled }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Supervisi
                        </span>
                    @endif

                    {{-- Performance --}}
                    <a href="{{ $performanceHref }}" class="{{ $navClass('dashboard.performance.*') }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19V5M9 19v-6M14 19V9M19 19V3" stroke-linecap="round" />
                            <path d="M3 19h18" stroke-linecap="round" />
                        </svg>
                        Performance
                    </a>
                </div>

            </nav>

            {{-- Bottom nav: Notifications, Settings, Logout --}}
            <div class="border-t border-slate-200 px-6 pt-4 pb-6 lg:pb-4 space-y-1 shrink-0">
                {{-- Notifications --}}
                <a href="{{ route('dashboard.notifications.index') }}"
                    class="{{ $navClass('dashboard.notifications.*') }}">
                    <span class="relative">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke-linecap="round" />
                        </svg>
                        @if ($unreadCount > 0)
                            <span class="absolute -right-1 -top-1 h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </span>
                    Notifications
                    @if ($unreadCount > 0)
                        <span class="ml-auto rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-600">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>

                {{-- Settings --}}
                <a href="{{ route('profile.edit') }}" class="{{ $navClass('profile.edit') }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 8.6 15a1.7 1.7 0 0 0-.6-1l-.06-.06A2 2 0 1 1 5.1 11.1l.06.06A1.7 1.7 0 0 0 8.6 9a1.7 1.7 0 0 0 .6-1l-.06-.06A2 2 0 1 1 12 5.1l.06.06a1.7 1.7 0 0 0 2 .44 1.7 1.7 0 0 0 1-.6l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 .6 1l.06.06a2 2 0 1 1-.66 4.94Z" />
                    </svg>
                    Settings
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-full px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 17 15 12 10 7" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M15 12H3M21 3v18" stroke-linecap="round" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"
        x-show="sidebarOpen" x-cloak
        @click="sidebarOpen = false">
    </div>

    {{-- ===================== MAIN ===================== --}}
    <main class="min-w-0 flex-1">

        {{-- Sticky Header --}}
        <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-8">
            <div class="flex items-center gap-3">
                {{-- Hamburger (mobile) --}}
                <button type="button"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                    @click="sidebarOpen = true">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                    </svg>
                </button>

                {{-- Page title / breadcrumb area --}}
                @isset($header)
                    <div>
                        {{ $header }}
                    </div>
                @endisset
            </div>

            {{-- Right side: notification bell + user avatar --}}
            <div class="flex items-center gap-3">
                {{-- Notification bell --}}
                <a href="{{ route('dashboard.notifications.index') }}"
                    class="relative rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke-linecap="round" />
                    </svg>
                    @if ($unreadCount > 0)
                        <span class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    @endif
                </a>

                {{-- User info + avatar --}}
                <a href="{{ route('profile.edit') }}"
                    class="hidden items-center gap-3 border-l border-slate-200 pl-4 sm:flex">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-800">{{ $authUser->name }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            {{ $authUser->getRoleNameForTitle() }}
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#0b5bd3] bg-blue-50 text-sm font-bold text-[#0b5bd3]">
                        {{ $avatarInitials }}
                    </div>
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @php
            $successFlash = session('success');
            $successMessage = is_array($successFlash) ? ($successFlash['message'] ?? null) : $successFlash;
        @endphp

        @if ($successMessage)
            <div class="mx-4 mt-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 sm:mx-8">
                <svg class="h-5 w-5 shrink-0 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ $successMessage }}
            </div>
        @endif

        @php
            $errorFlash = session('error');
            $errorMessage = is_array($errorFlash) ? ($errorFlash['message'] ?? null) : $errorFlash;
        @endphp

        @if ($errorMessage)
            <div class="mx-4 mt-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 sm:mx-8">
                <svg class="h-5 w-5 shrink-0 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" stroke-linecap="round" />
                </svg>
<<<<<<< HEAD
                {{ is_array(session('error')) ? session('error')['message'] : session('error') }}
=======
                {{ $errorMessage }}
>>>>>>> b8b2ed0 (refactor: improve flash message handling and update notification links for clarity)
            </div>
        @endif

        {{-- Main content slot --}}
        <div class="px-4 py-8 sm:px-8">
            {{ $slot }}
        </div>

    </main>

    </div>{{-- end Alpine x-data wrapper --}}

    @include('components.sweet-alert')
    @stack('scripts')

</body>

</html>
