<x-sidebar-layout>
    <x-slot name="title">Edit Pengajuan Layanan - Pusat Layanan</x-slot>

    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Layanan Antar Divisi' => route('dashboard.services.index'), 'Edit Pengajuan' => null]" />
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-8">
        
        @if($service->status === 'rejected')
        <div class="mb-6 rounded-xl border border-red-100 bg-red-50/50 p-6">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="w-full">
                    <h3 class="text-sm font-semibold text-red-900">Alasan Penolakan Pengelola</h3>
                    <p class="mt-1 text-sm text-red-700">{{ $service->rejection_reason }}</p>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('dashboard.services.update', $service) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                {{-- Type --}}
                <div>
                    <label for="type" class="mb-2 block text-sm font-semibold text-slate-700">Jenis Layanan <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                        <option value="">Pilih Jenis Layanan</option>
                        @php
                            $userDept = auth()->user()->department->slug ?? '';
                            $currentType = old('type', $service->type);
                        @endphp

                        @if($userDept !== 'creative')
                        <optgroup label="Divisi Kreatif">
                            <option value="copm" {{ $currentType == 'copm' ? 'selected' : '' }}>COPM (Desain poster, banner, logo, dll)</option>
                            <option value="codm" {{ $currentType == 'codm' ? 'selected' : '' }}>CODM (Dokumentasi acara, pubdok)</option>
                        </optgroup>
                        @endif

                        @if($userDept !== 'research-and-technology')
                        <optgroup label="Divisi Research and Technology (RnT)">
                            <option value="komnews" {{ $currentType == 'komnews' ? 'selected' : '' }}>Komnews (Berita, artikel web)</option>
                            <option value="riset" {{ $currentType == 'riset' ? 'selected' : '' }}>Riset (Survei, analisis data)</option>
                        </optgroup>
                        @endif
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Judul Layanan <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $service->title) }}" required placeholder="Contoh: Desain Poster Seminar IT" class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Deskripsi & Spesifikasi <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="5" required placeholder="Jelaskan kebutuhan Anda secara detail. Sertakan juga referensi gaya/desain atau link google drive materi jika ada." class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">{{ old('description', $service->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label for="due_date" class="mb-2 block text-sm font-semibold text-slate-700">Tenggat Waktu / Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $service->due_date ? $service->due_date->format('Y-m-d') : '') }}" class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                    <p class="mt-1.5 text-xs text-slate-500">Opsional, tapi disarankan jika Anda memiliki target publikasi/kebutuhan waktu tertentu.</p>
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-4 border-t border-slate-100 pt-6">
                <a href="{{ route('dashboard.services.show', $service) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Batal</a>
                <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Simpan & Ajukan Ulang
                </button>
            </div>
        </form>
    </div>
</x-sidebar-layout>
