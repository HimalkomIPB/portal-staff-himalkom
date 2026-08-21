<x-sidebar-layout>
    <x-slot name="title">Pusat Layanan Antar Divisi</x-slot>

    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Layanan Antar Divisi' => null]" />
    </x-slot>

    <div class="px-4 py-8 sm:px-8">
        {{-- Tabs --}}
        <div class="mb-6 border-b border-slate-200">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <a href="{{ route('dashboard.services.index', ['tab' => 'my_requests']) }}"
                   class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'my_requests' ? 'border-[#0b5bd3] text-[#0b5bd3]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    Pengajuan Divisi Saya
                </a>
                
                @if(auth()->user()->department?->slug === 'creative' || auth()->user()->department?->slug === 'research-and-technology')
                    <a href="{{ route('dashboard.services.index', ['tab' => 'incoming']) }}"
                       class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'incoming' ? 'border-[#0b5bd3] text-[#0b5bd3]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                        Masuk ke Divisi Saya
                        @if(isset($incomingCount) && $incomingCount > 0)
                            <span class="ml-2 rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-semibold text-white">
                                {{ $incomingCount }}
                            </span>
                        @endif
                    </a>
                @endif
            </nav>
        </div>

        {{-- Header Actions --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-800">
                {{ $tab === 'my_requests' ? 'Daftar Pengajuan' : 'Tugas Masuk' }}
            </h2>
            
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <form action="{{ route('dashboard.services.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <label for="statusFilter" class="text-sm font-medium text-slate-600">Filter:</label>
                    <select name="status" id="statusFilter" onchange="this.form.submit()" class="block w-full rounded-md border-slate-300 py-2 text-sm text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu (Baru)</option>
                        <option value="not_completed" {{ $statusFilter === 'not_completed' ? 'selected' : '' }}>Selain Selesai</option>
                        <option value="accepted" {{ $statusFilter === 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                        <option value="uploaded" {{ $statusFilter === 'uploaded' ? 'selected' : '' }}>Diunggah</option>
                        <option value="revision" {{ $statusFilter === 'revision' ? 'selected' : '' }}>Revisi</option>
                        <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </form>

                @if($tab === 'my_requests')
                    <a href="{{ route('dashboard.services.create') }}" 
                       class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Pengajuan Baru
                    </a>
                @endif
            </div>
        </div>

        {{-- List --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if($serviceRequests->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="mb-4 rounded-full bg-slate-50 p-4">
                        <svg class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900">Belum ada pengajuan</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $tab === 'my_requests' ? 'Divisi Anda belum pernah mengajukan layanan.' : 'Belum ada tugas masuk dari divisi lain.' }}
                    </p>
                </div>
            @else
                <ul role="list" class="divide-y divide-slate-100">
                    @foreach($serviceRequests as $req)
                        <li class="relative group flex flex-col gap-4 p-4 hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between sm:p-6 transition cursor-pointer">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 px-3 text-indigo-600">
                                    <span class="text-xs font-bold uppercase">{{ $req->type }}</span>
                                </div>
                                <div>
                                    <a href="{{ route('dashboard.services.show', $req) }}" class="text-sm font-semibold text-slate-900 group-hover:text-[#0b5bd3] focus:outline-none before:absolute before:inset-0">
                                        {{ $req->title }}
                                    </a>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span>Dari: {{ $req->department->name }} ({{ $req->requester->name }})</span>
                                        <span>&bull;</span>
                                        <span>Dibuat: {{ $req->created_at->translatedFormat('d M Y') }}</span>
                                        @if($req->due_date)
                                            <span>&bull;</span>
                                            <span class="{{ $req->due_date->isPast() && $req->status !== 'completed' ? 'text-red-500 font-medium' : '' }}">
                                                Tenggat: {{ $req->due_date->translatedFormat('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex shrink-0 items-center gap-4 sm:flex-col sm:items-end">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $req->status_color }}">
                                    {{ $req->status_label }}
                                </span>
                                @if($req->assigned_to)
                                    <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                        <svg class="h-3.5 w-3.5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                                        </svg>
                                        {{ $req->assignee->name }}
                                    </div>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 border border-slate-200">
                                        Belum di-assign
                                    </span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-sidebar-layout>
