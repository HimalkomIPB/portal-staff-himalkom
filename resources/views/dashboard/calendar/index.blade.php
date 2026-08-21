{{--
    Calendar / Agenda — Portal Staff Himalkom
    Layout: x-sidebar-layout
    Tech: Alpine.js + Tailwind (via Vite)
--}}
<x-sidebar-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">WORKSPACE</p>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Calendar</h1>
        </div>
    </x-slot>

    {{-- =========================================================
         Alpine.js root — Calendar page state
         ========================================================= --}}
    <div
        x-data="calendarApp()"
        x-init="init()"
        class="w-full"
    >

        {{-- ============== PAGE HEADER ============== --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Calendar</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola dan pantau seluruh agenda kegiatan Himalkom.</p>
            </div>
            {{-- Tombol Tambah Agenda — hanya muncul jika punya permission --}}
            @canany(['agenda.create-dept', 'agenda.create-org'])
                <button
                    @click="openModal()"
                    class="hidden sm:inline-flex items-center gap-2 rounded-xl bg-[#0b5bd3] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-blue-700/30 transition hover:bg-blue-700 active:scale-95"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                    Tambah Agenda
                </button>
            @endcanany
        </div>

        {{-- ============== CALENDAR CARD ============== --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden mb-8">

            {{-- Toolbar: navigasi bulan + filter + view toggle --}}
            <div class="flex flex-col gap-3 px-4 sm:px-5 py-4 border-b border-slate-100 sm:flex-row sm:flex-wrap sm:items-center">

                {{-- Navigasi bulan --}}
                <div class="flex items-center gap-2 min-w-0">
                    <button @click="prevPeriod()"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18 9 12l6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button @click="nextPeriod()"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18 15 12 9 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <span class="min-w-0 text-sm font-bold text-slate-800 sm:text-base sm:min-w-[180px]" x-text="viewMode === 'month' ? monthYearLabel : weekRangeLabel"></span>
                    <button @click="goToToday()"
                        class="ml-1 rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Today
                    </button>
                </div>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Filter Semua / Departemen / General --}}
                <div class="flex w-full items-center rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-semibold sm:w-auto">
                    <button @click="activeFilter = 'all'"
                        :class="activeFilter === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="rounded-lg px-3 py-1.5 transition">Semua</button>
                    <button @click="activeFilter = 'departemen'"
                        :class="activeFilter === 'departemen' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition">
                        <span class="h-2 w-2 rounded-full bg-[#0b5bd3]"></span>
                        Departemen
                    </button>
                    <button @click="activeFilter = 'general'"
                        :class="activeFilter === 'general' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                        General
                    </button>
                </div>

                {{-- Toggle Month / Week --}}
                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-semibold">
                    <button @click="viewMode = 'month'"
                        :class="viewMode === 'month' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="w-1/2 rounded-lg px-3 py-1.5 transition sm:w-auto">Month</button>
                    <button @click="viewMode = 'week'"
                        :class="viewMode === 'week' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="w-1/2 rounded-lg px-3 py-1.5 transition sm:w-auto">Week</button>
                </div>
            </div>

            {{-- ===== MONTH VIEW ===== --}}
            <div x-show="viewMode === 'month'" class="overflow-x-auto">
                {{-- Header hari --}}
                <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50/80">
                    <template x-for="day in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']">
                        <div class="py-2 sm:py-3 text-center text-[10px] sm:text-[11px] font-semibold text-slate-500 uppercase tracking-[0.15em] sm:tracking-[0.2em]" x-text="day"></div>
                    </template>
                </div>

                {{-- Grid tanggal --}}
                <div class="grid grid-cols-7 bg-white">
                    <template x-for="(cell, idx) in calendarCells" :key="idx">
                        <div
                            class="min-h-[108px] border-b border-r border-slate-100 p-1.5 sm:min-h-[140px] sm:p-2 last:border-r-0 transition hover:bg-slate-50/70 cursor-pointer"
                            :class="{
                                'bg-slate-50 text-slate-300': !cell.currentMonth,
                                'bg-white': cell.currentMonth,
                            }"
                            @click="focusWeekDay(cell.date)"
                        >
                            {{-- Nomor tanggal --}}
                            <div class="flex items-start justify-between mb-1.5 sm:mb-2">
                                <span
                                    class="inline-flex h-6 w-6 sm:h-7 sm:w-7 items-center justify-center rounded-full text-[11px] sm:text-xs font-semibold ring-1 ring-transparent"
                                    :class="{
                                        'bg-[#0b5bd3] text-white shadow-md shadow-blue-700/20': cell.isToday,
                                        'text-slate-800': cell.currentMonth && !cell.isToday,
                                        'text-slate-300': !cell.currentMonth,
                                    }"
                                    x-text="cell.day"
                                ></span>
                            </div>

                            {{-- Events --}}
                            <template x-for="(event, ei) in cell.events.slice(0, 2)" :key="ei">
                                <div
                                    class="mb-1 cursor-pointer rounded-md border px-1.5 py-1 text-[10px] sm:px-2 sm:py-1 text-slate-800 font-medium leading-tight truncate transition hover:shadow-sm"
                                    :class="event.skala === 'departemen'
                                        ? 'border-blue-100 bg-blue-50 text-blue-800'
                                        : 'border-slate-200 bg-slate-100 text-slate-700'"
                                    :title="event.title"
                                    @click="openDetailModal(event)"
                                    x-text="event.title"
                                ></div>
                            </template>
                            <template x-if="cell.events.length > 2">
                                <div class="text-[10px] font-semibold text-slate-400 mt-0.5 pl-1">+<span x-text="cell.events.length - 2"></span> lainnya</div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ===== WEEK VIEW ===== --}}
            <div x-show="viewMode === 'week'" class="bg-white">
                <div class="flex flex-col gap-3 px-4 sm:px-5 py-4 border-b border-slate-100 bg-white sm:flex-row sm:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Week View</p>
                        <h3 class="text-sm sm:text-base font-bold text-slate-800" x-text="weekRangeLabel"></h3>
                    </div>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <button @click="moveWeek(-1)"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition"
                            aria-label="Previous week">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18 9 12l6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button @click="goToToday()"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Hari Ini
                        </button>
                        <button @click="moveWeek(1)"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition"
                            aria-label="Next week">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18 15 12 9 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                <div class="px-3 py-3 sm:hidden">
                    <template x-for="(wd, i) in weekDays" :key="'mobile-' + i">
                        <div class="mb-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400" x-text="wd.label"></div>
                                    <div class="mt-1 text-sm font-bold text-slate-800" x-text="wd.day + ' ' + wd.monthShort + ' ' + wd.year"></div>
                                </div>
                                <div class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide" :class="wd.isToday ? 'bg-[#0b5bd3] text-white' : 'bg-slate-100 text-slate-500'" x-text="wd.isToday ? 'Today' : (wd.events.length + ' event')"></div>
                            </div>

                            <div class="p-3 space-y-2">
                                <template x-if="wd.events.length === 0">
                                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-center text-sm text-slate-400">
                                        Tidak ada jadwal rapat
                                    </div>
                                </template>

                                <template x-for="(event, ei) in wd.events" :key="ei">
                                    <div
                                        class="cursor-pointer rounded-xl border-l-4 bg-white px-3 py-3 shadow-sm transition active:scale-[0.99]"
                                        :class="event.skala === 'departemen' ? 'border-l-blue-500' : 'border-l-orange-500'"
                                        @click="openDetailModal(event)"
                                    >
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400" x-text="event.skala === 'departemen' ? 'Departemen' : 'General'"></div>
                                                <div class="mt-1 text-sm font-semibold text-slate-800 line-clamp-2" x-text="event.title"></div>
                                            </div>
                                            <div class="shrink-0 text-right text-xs font-semibold text-slate-500" x-text="formatTime(event.start_time) + ' - ' + formatTime(event.end_time)"></div>
                                        </div>
                                        <template x-if="event.lokasi">
                                            <div class="mt-2 text-xs text-slate-500 line-clamp-1" x-text="event.lokasi"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="hidden sm:grid border-b border-slate-200 bg-slate-50/90" style="grid-template-columns: 72px repeat(7, minmax(0, 1fr));">
                    <div class="flex h-[84px] items-end justify-end border-r border-slate-200 px-3 pb-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-slate-400">
                        GMT+1
                    </div>
                    <template x-for="(wd, i) in weekDays" :key="i">
                        <div class="py-3 text-center border-r border-slate-200 last:border-r-0">
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.22em]" x-text="wd.label"></div>
                            <div
                                class="mx-auto mt-1 inline-flex h-9 min-w-9 items-center justify-center rounded-full px-2 text-sm font-bold"
                                :class="wd.isToday ? 'bg-[#0b5bd3] text-white shadow-md shadow-blue-700/20' : 'bg-slate-100 text-slate-700'"
                                x-text="wd.day"
                            ></div>
                            <div class="mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400" x-text="wd.monthShort"></div>
                        </div>
                    </template>
                </div>
                <div class="hidden sm:grid" style="grid-template-columns: 72px repeat(7, minmax(0, 1fr));">
                    <div class="relative border-r border-slate-200 bg-white" style="height: 1536px;">
                        <template x-for="hour in 24" :key="hour">
                            <div class="absolute left-0 right-0 border-t border-slate-100" :style="'top: ' + ((hour - 1) * 64) + 'px'"></div>
                        </template>
                        <template x-for="hour in 24" :key="'label-' + hour">
                            <div class="absolute left-0 right-0 pr-3 text-right text-[11px] text-slate-400" :style="'top: ' + ((hour - 1) * 64 - 6) + 'px'" x-text="formatHourLabel(hour - 1)"></div>
                        </template>
                    </div>

                    <template x-for="(wd, i) in weekColumns" :key="i">
                        <div class="relative border-r border-slate-200 last:border-r-0 bg-white" style="height: 1536px;">
                            <div class="absolute inset-0" style="background-image: repeating-linear-gradient(to bottom, rgba(226, 232, 240, 0) 0, rgba(226, 232, 240, 0) 63px, #e2e8f0 64px);"></div>

                            <template x-if="wd.events.length === 0">
                                <div class="absolute inset-x-3 top-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-3 py-4 text-center text-xs text-slate-400">
                                    Tidak ada jadwal rapat
                                </div>
                            </template>

                            <template x-for="(event, ei) in wd.events" :key="ei">
                                <div
                                    class="absolute cursor-pointer rounded-xl px-3 py-2 text-[11px] font-medium leading-tight shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                    :class="event.skala === 'departemen'
                                        ? 'bg-blue-500 text-white ring-1 ring-blue-300'
                                        : 'bg-orange-500 text-white ring-1 ring-orange-300'"
                                    :style="eventStyle(event)"
                                    @click="openDetailModal(event)"
                                >
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.2em] opacity-90" x-text="event.skala === 'departemen' ? 'Dept' : 'General'"></span>
                                        <span class="text-[10px] font-semibold opacity-90" x-text="formatTime(event.start_time)"></span>
                                    </div>
                                    <div class="line-clamp-2 font-semibold" x-text="event.title"></div>
                                    <div class="mt-1 text-[10px] opacity-90" x-text="event.lokasi || 'Tanpa lokasi'"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

        </div>{{-- end calendar card --}}

        {{-- ============== UPCOMING AGENDA ============== --}}
        <div class="mb-10">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Upcoming Agenda</h3>

            <template x-if="upcomingEvents.length === 0">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-12 text-center text-sm text-slate-400">
                    Tidak ada agenda mendatang.
                </div>
            </template>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="(event, i) in upcomingEvents" :key="i">
                    <div
                        class="group relative rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 cursor-pointer"
                        @click="openDetailModal(event)"
                        :class="event.skala === 'departemen' ? 'border-l-4 border-[#0b5bd3]' : 'border-l-4 border-slate-300'"
                    >
                        <div class="flex items-start justify-between mb-2">
                            <span class="text-xs font-bold text-slate-400 tracking-wider" x-text="formatUpcomingDate(event.date)"></span>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                :class="event.skala === 'departemen'
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-slate-100 text-slate-600'"
                                x-text="event.skala === 'departemen' ? 'DEPARTEMEN' : 'GENERAL'"
                            ></span>
                        </div>
                        <p class="font-semibold text-slate-800 text-base mb-3 leading-snug" x-text="event.title"></p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2" stroke-linecap="round"/></svg>
                                <span x-text="formatTime(event.start_time) + ' - ' + formatTime(event.end_time)"></span>
                            </div>
                            <template x-if="event.lokasi">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 0 1 7 7c0 4.97-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                    <span x-text="event.lokasi"></span>
                                </div>
                            </template>
                            <template x-if="!event.lokasi && event.jenis === 'online'">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    <span>Online</span>
                                </div>
                            </template>
                        </div>

                        {{-- Edit/Delete — hanya untuk yang berwenang --}}
                        <template x-if="event.can_edit">
                            <div class="absolute top-3 right-3 hidden group-hover:flex items-center gap-1">
                                <button
                                    @click.stop="openEditModal(event)"
                                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
                                    title="Edit"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button
                                    @click.stop="deleteAgenda(event)"
                                    class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500 transition"
                                    title="Hapus"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ============== FAB Tambah (mobile) ============== --}}
        @canany(['agenda.create-dept', 'agenda.create-org'])
            <button
                @click="openModal()"
                class="fixed bottom-6 right-6 sm:hidden z-30 flex h-14 w-14 items-center justify-center rounded-full bg-[#0b5bd3] text-white shadow-lg shadow-blue-700/40 transition hover:bg-blue-700 active:scale-95"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            </button>
        @endcanany

        {{-- ============================================================
             MODAL — Tambah / Edit Agenda
             ============================================================ --}}
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- Modal panel --}}
            <div
                class="relative z-10 w-full max-w-2xl rounded-2xl bg-white shadow-2xl"
                @click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Header --}}
                <div class="flex items-start justify-between px-6 sm:px-8 pt-6 sm:pt-8 pb-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800" x-text="editingId ? 'Edit Agenda' : 'Tambah Agenda'"></h3>
                        <p class="text-sm text-slate-500 mt-1">Tambahkan kegiatan baru ke kalender Himalkom.</p>
                    </div>
                    <button @click="closeModal()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>

                {{-- Form --}}
                <form
                    :action="editingId ? '{{ route('dashboard.calendar.update', ':id') }}'.replace(':id', editingId) : '{{ route('dashboard.calendar.store') }}'"
                    method="POST"
                    class="px-6 sm:px-8 py-6 space-y-6 max-h-[70vh] overflow-y-auto"
                    id="agenda-form"
                >
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Nama Kegiatan --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kegiatan</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>
                            </span>
                            <input
                                type="text"
                                name="title"
                                x-model="form.title"
                                placeholder="Contoh: Rapat Evaluasi Divisi Ristek"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                            >
                        </div>
                    </div>

                    {{-- Tanggal + Mulai + Selesai --}}
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>
                                </span>
                                <input
                                    type="date"
                                    name="date"
                                    x-model="form.date"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-3 text-sm text-slate-800 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                                >
                            </div>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mulai</label>
                            <input
                                type="time"
                                name="start_time"
                                x-model="form.start_time"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-3 text-sm text-slate-800 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                            >
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Selesai</label>
                            <input
                                type="time"
                                name="end_time"
                                x-model="form.end_time"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-3 text-sm text-slate-800 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                            >
                        </div>
                    </div>

                    {{-- Jenis Kegiatan --}}
                    <div class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Jenis Kegiatan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                @click="form.jenis = 'offline'"
                                :class="form.jenis === 'offline'
                                    ? 'bg-white ring-2 ring-[#0b5bd3] text-[#0b5bd3] shadow-sm'
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                class="flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4" stroke-linecap="round"/></svg>
                                Offline
                            </button>
                            <button type="button"
                                @click="form.jenis = 'online'"
                                :class="form.jenis === 'online'
                                    ? 'bg-white ring-2 ring-[#0b5bd3] text-[#0b5bd3] shadow-sm'
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                class="flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                Online
                            </button>
                        </div>

                        {{-- Lokasi Kegiatan (hanya jika offline) --}}
                        <div x-show="form.jenis === 'offline'" x-transition class="mt-3">
                            <label class="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1.5">LOKASI KEGIATAN</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 0 1 7 7c0 4.97-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                </span>
                                <input
                                    type="text"
                                    name="lokasi"
                                    x-model="form.lokasi"
                                    placeholder="Contoh: Ruang Rapat Himalkom"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                                >
                            </div>
                        </div>

                        {{-- Link Online (hanya jika online) --}}
                        <div x-show="form.jenis === 'online'" x-transition class="mt-3">
                            <label class="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1.5">LINK MEETING (OPSIONAL)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                </span>
                                <input
                                    type="text"
                                    name="lokasi"
                                    x-model="form.lokasi"
                                    placeholder="Contoh: Online (Zoom)"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Skala --}}
                    <div class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <div class="mb-3">
                            <label class="text-sm font-semibold text-slate-700">Skala</label>
                            <span class="text-xs text-slate-400 ml-1">(Departemen untuk kegiatan internal divisi, General untuk kegiatan yang ditujukan secara umum)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                @click="form.skala = 'departemen'"
                                :class="form.skala === 'departemen'
                                    ? 'bg-white ring-2 ring-[#0b5bd3] text-[#0b5bd3] shadow-sm'
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                class="flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 11a4 4 0 1 0-8 0M3 21a7 7 0 0 1 14 0" stroke-linecap="round"/><path d="M18 8a3 3 0 0 1 0 6M21 21a5 5 0 0 0-4-4.9" stroke-linecap="round"/></svg>
                                Departemen
                            </button>
                            <button type="button"
                                @click="form.skala = 'general'"
                                :class="form.skala === 'general'
                                    ? 'bg-white ring-2 ring-[#0b5bd3] text-[#0b5bd3] shadow-sm'
                                    : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                class="flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                General
                            </button>
                        </div>
                        <input type="hidden" name="skala" :value="form.skala">
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <textarea
                            name="deskripsi"
                            x-model="form.deskripsi"
                            rows="4"
                            placeholder="Tambahkan catatan atau agenda rapat..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0b5bd3] focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 transition resize-none"
                        ></textarea>
                    </div>

                    {{-- Hidden field jenis --}}
                    <input type="hidden" name="jenis" :value="form.jenis">
                </form>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 sm:px-8 py-5 border-t border-slate-100">
                    <button
                        type="button"
                        @click="closeModal()"
                        class="rounded-xl px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitForm()"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#0b5bd3] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-blue-700/30 transition hover:bg-blue-700 active:scale-95"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Agenda
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================
             MODAL — Detail Agenda (read-only)
             ============================================================ --}}
        <div
            x-show="detailModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="detailModalOpen = false"></div>
            <div
                class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl p-6"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest rounded-full px-2.5 py-0.5"
                            :class="detailEvent?.skala === 'departemen' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                            x-text="detailEvent?.skala === 'departemen' ? 'DEPARTEMEN' : 'GENERAL'"
                        ></span>
                        <h4 class="text-lg font-bold text-slate-800 mt-2" x-text="detailEvent?.title"></h4>
                    </div>
                    <button @click="detailModalOpen = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 transition">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="space-y-3 text-sm text-slate-600">
                    <div class="flex items-center gap-3">
                        <svg class="h-4 w-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span x-text="formatUpcomingDate(detailEvent?.date)"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="h-4 w-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2" stroke-linecap="round"/></svg>
                        <span x-text="detailEvent ? formatTime(detailEvent.start_time) + ' – ' + formatTime(detailEvent.end_time) : ''"></span>
                    </div>
                    <template x-if="detailEvent?.lokasi">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 0 1 7 7c0 4.97-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                            <span x-text="detailEvent?.lokasi"></span>
                        </div>
                    </template>
                    <template x-if="detailEvent?.deskripsi">
                        <div class="flex items-start gap-3 pt-2 border-t border-slate-100">
                            <svg class="h-4 w-4 text-slate-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                            <span class="leading-relaxed" x-text="detailEvent?.deskripsi"></span>
                        </div>
                    </template>
                </div>
                <template x-if="detailEvent?.can_edit">
                    <div class="flex gap-2 mt-5 pt-4 border-t border-slate-100">
                        <button
                            @click="detailModalOpen = false; openEditModal(detailEvent)"
                            class="flex-1 rounded-xl border border-slate-200 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >Edit</button>
                        <button
                            @click="detailModalOpen = false; deleteAgenda(detailEvent)"
                            class="flex-1 rounded-xl border border-red-200 py-2 text-sm font-semibold text-red-500 hover:bg-red-50 transition"
                        >Hapus</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Hidden delete form --}}
        <form id="delete-agenda-form" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>

    {{-- ============================================================
         Alpine.js — Calendar App Logic
         ============================================================ --}}
    @push('scripts')
    <script>
    function calendarApp() {
        return {
            // State
            today: new Date(),
            currentYear: new Date().getFullYear(),
            currentMonth: new Date().getMonth(), // 0-indexed
            selectedDate: @json(now()->format('Y-m-d')),
            viewMode: 'month',
            activeFilter: 'all',

            // Events data — pre-mapped from controller
            allEvents: @json($allEventsJson),

            upcomingRaw: @json($upcomingJson),

            // Modal state
            modalOpen: false,
            detailModalOpen: false,
            detailEvent: null,
            editingId: null,

            form: {
                title: '',
                date: '',
                start_time: '',
                end_time: '',
                jenis: 'offline',
                lokasi: '',
                skala: 'departemen',
                deskripsi: '',
            },

            // -------------------------------------------------------
            // Init
            // -------------------------------------------------------
            init() {
                // Nothing extra needed — data loaded from Blade
            },

            // -------------------------------------------------------
            // Computed
            // -------------------------------------------------------
            get monthYearLabel() {
                const months = ['January','February','March','April','May','June',
                                'July','August','September','October','November','December'];
                return months[this.currentMonth] + ' ' + this.currentYear;
            },

            get weekRangeLabel() {
                const first = this.weekDays[0];
                const last = this.weekDays[6];
                return `${first.day} ${first.monthShort} - ${last.day} ${last.monthShort} ${last.year}`;
            },

            get weekColumns() {
                return this.weekDays.map(day => {
                    const events = this.filteredEvents
                        .filter(event => event.date === day.date)
                        .map(event => ({
                            ...event,
                            startMinutes: this.timeToMinutes(event.start_time),
                            endMinutes: this.timeToMinutes(event.end_time) || (this.timeToMinutes(event.start_time) + 60),
                        }))
                        .sort((left, right) => left.startMinutes - right.startMinutes || left.endMinutes - right.endMinutes);

                    const laneEnds = [];

                    const laidOutEvents = events.map(event => {
                        const laneIndex = laneEnds.findIndex(endMinutes => endMinutes <= event.startMinutes);

                        if (laneIndex === -1) {
                            laneEnds.push(event.endMinutes);
                            return { ...event, laneIndex: laneEnds.length - 1 };
                        }

                        laneEnds[laneIndex] = event.endMinutes;
                        return { ...event, laneIndex };
                    });

                    const laneCount = Math.max(1, laneEnds.length);

                    return {
                        ...day,
                        events: laidOutEvents.map(event => ({
                            ...event,
                            laneCount,
                        })),
                    };
                });
            },

            get filteredEvents() {
                if (this.activeFilter === 'all') return this.allEvents;
                return this.allEvents.filter(e => e.skala === this.activeFilter);
            },

            get upcomingEvents() {
                const today = this.formatDate(this.today);
                let events = this.upcomingRaw.filter(e => e.date >= today);
                if (this.activeFilter !== 'all') {
                    events = events.filter(e => e.skala === this.activeFilter);
                }
                return events.slice(0, 10);
            },

            get calendarCells() {
                const year  = this.currentYear;
                const month = this.currentMonth;

                const firstDay = new Date(year, month, 1);
                const lastDay  = new Date(year, month + 1, 0);

                // Monday = 0 … Sunday = 6
                let startDow = (firstDay.getDay() + 6) % 7;

                const cells = [];

                // Prev month padding
                for (let i = startDow - 1; i >= 0; i--) {
                    const d = new Date(year, month, -i);
                    cells.push({
                        day: d.getDate(),
                        date: this.formatDate(d),
                        currentMonth: false,
                        isToday: false,
                        events: [],
                    });
                }

                // Current month days
                for (let d = 1; d <= lastDay.getDate(); d++) {
                    const date = this.formatDate(new Date(year, month, d));
                    cells.push({
                        day: d,
                        date,
                        currentMonth: true,
                        isToday: date === this.formatDate(this.today),
                        events: this.filteredEvents.filter(e => e.date === date),
                    });
                }

                // Next month padding — fill to 6 rows
                const totalCells = Math.ceil(cells.length / 7) * 7;
                let nextDay = 1;
                while (cells.length < totalCells) {
                    const d = new Date(year, month + 1, nextDay++);
                    cells.push({
                        day: d.getDate(),
                        date: this.formatDate(d),
                        currentMonth: false,
                        isToday: false,
                        events: [],
                    });
                }

                return cells;
            },

            get weekDays() {
                // Find Monday of the selected week
                const ref = new Date(this.selectedDate + 'T00:00:00');
                const dow = (ref.getDay() + 6) % 7; // Monday=0
                const monday = new Date(ref);
                monday.setDate(ref.getDate() - dow);

                const labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                const days = [];
                for (let i = 0; i < 7; i++) {
                    const d    = new Date(monday);
                    d.setDate(monday.getDate() + i);
                    const date = this.formatDate(d);
                    days.push({
                        label: labels[i],
                        day: d.getDate(),
                        monthShort: months[d.getMonth()],
                        year: d.getFullYear(),
                        date,
                        isToday: date === this.formatDate(this.today),
                        events: this.filteredEvents.filter(e => e.date === date),
                    });
                }
                return days;
            },

            // -------------------------------------------------------
            // Navigation
            // -------------------------------------------------------
            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
            },

            prevPeriod() {
                if (this.viewMode === 'week') {
                    this.moveWeek(-1);
                    return;
                }

                this.prevMonth();
            },

            nextPeriod() {
                if (this.viewMode === 'week') {
                    this.moveWeek(1);
                    return;
                }

                this.nextMonth();
            },

            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
            },

            goToToday() {
                this.currentYear  = this.today.getFullYear();
                this.currentMonth = this.today.getMonth();
                this.selectedDate = this.formatDate(this.today);
            },

            moveWeek(offset) {
                const d = new Date(this.selectedDate + 'T00:00:00');
                d.setDate(d.getDate() + (offset * 7));
                this.selectedDate = this.formatDate(d);
                this.currentYear = d.getFullYear();
                this.currentMonth = d.getMonth();
            },

            // -------------------------------------------------------
            // Modal helpers
            // -------------------------------------------------------
            openModal() {
                this.editingId = null;
                this.form = { title:'', date:'', start_time:'', end_time:'', jenis:'offline', lokasi:'', skala:'departemen', deskripsi:'' };
                this.modalOpen = true;
            },

            openEditModal(event) {
                this.editingId     = event.id;
                this.form.title    = event.title;
                this.form.date     = event.date;
                this.form.start_time = event.start_time ? event.start_time.slice(0,5) : '';
                this.form.end_time   = event.end_time   ? event.end_time.slice(0,5)   : '';
                this.form.jenis    = event.jenis;
                this.form.lokasi   = event.lokasi || '';
                this.form.skala    = event.skala;
                this.form.deskripsi = event.deskripsi || '';
                this.modalOpen     = true;
            },

            closeModal() {
                this.modalOpen = false;
                this.editingId = null;
            },

            openDetailModal(event) {
                this.detailEvent    = event;
                this.detailModalOpen = true;
            },

            focusWeekDay(date) {
                this.selectedDate = date;
                const d = new Date(date + 'T00:00:00');
                this.currentYear = d.getFullYear();
                this.currentMonth = d.getMonth();
                this.viewMode = 'week';
            },

            submitForm() {
                document.getElementById('agenda-form').submit();
            },

            deleteAgenda(event) {
                if (!confirm('Yakin ingin menghapus agenda "' + event.title + '"?')) return;

                const form = document.getElementById('delete-agenda-form');
                form.action = '/dashboard/calendar/' + event.id;
                form.submit();
            },

            // -------------------------------------------------------
            // Formatters
            // -------------------------------------------------------
            formatDate(d) {
                const year  = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day   = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            },

            formatTime(t) {
                if (!t) return '';
                const parts = t.split(':');
                return parts[0] + ':' + parts[1];
            },

            formatHourLabel(hour) {
                const suffix = hour >= 12 ? 'PM' : 'AM';
                const normalized = hour % 12 === 0 ? 12 : hour % 12;
                return `${normalized} ${suffix}`;
            },

            timeToMinutes(timeStr) {
                if (!timeStr) return 0;

                const [hours, minutes] = timeStr.split(':').map(Number);
                return (hours * 60) + (minutes || 0);
            },

            eventStyle(event) {
                const startMinutes = event.startMinutes ?? this.timeToMinutes(event.start_time);
                const endMinutes = event.endMinutes ?? (this.timeToMinutes(event.end_time) || (startMinutes + 60));
                const top = (startMinutes / 60) * 64 + 2;
                const height = Math.max(((endMinutes - startMinutes) / 60) * 64 - 4, 42);
                const laneWidth = 100 / (event.laneCount || 1);
                const left = laneWidth * (event.laneIndex || 0);

                return `top: ${top}px; height: ${height}px; left: calc(${left}% + 4px); width: calc(${laneWidth}% - 8px);`;
            },

            formatUpcomingDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr + 'T00:00:00');
                const months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
                return d.getDate() + ' ' + months[d.getMonth()];
            },
        };
    }
    </script>
    @endpush

</x-sidebar-layout>
