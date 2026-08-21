<x-sidebar-layout>
    <x-slot name="title">Buat Pengajuan Baru - Pusat Layanan</x-slot>

    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Layanan Antar Divisi' => route('dashboard.services.index'), 'Buat Pengajuan' => null]" />
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-8">
        <form action="{{ route('dashboard.services.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            
            <div class="space-y-6">
                {{-- Type --}}
                <div>
                    <label for="type" class="mb-2 block text-sm font-semibold text-slate-700">Jenis Layanan <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                        <option value="">Pilih Jenis Layanan</option>
                        @php
                            $userDept = auth()->user()->department->slug ?? '';
                        @endphp

                        @if($userDept !== 'creative')
                        <optgroup label="Divisi Kreatif">
                            <option value="copm" {{ old('type') == 'copm' ? 'selected' : '' }}>COPM (Desain poster, banner, logo, dll)</option>
                            <option value="codm" {{ old('type') == 'codm' ? 'selected' : '' }}>CODM (Dokumentasi acara, pubdok)</option>
                        </optgroup>
                        @endif

                        @if($userDept !== 'research-and-technology')
                        <optgroup label="Divisi Research and Technology (RnT)">
                            <option value="komnews" {{ old('type') == 'komnews' ? 'selected' : '' }}>Komnews (Berita, artikel web)</option>
                            <option value="riset" {{ old('type') == 'riset' ? 'selected' : '' }}>Riset (Survei, analisis data)</option>
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
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Desain Poster Seminar IT" class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Deskripsi & Spesifikasi <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="5" required placeholder="Jelaskan kebutuhan Anda secara detail. Sertakan juga referensi gaya/desain atau link google drive materi jika ada." class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label for="due_date" class="mb-2 block text-sm font-semibold text-slate-700">Tenggat Waktu / Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" class="block w-full rounded-xl border-slate-300 py-3 text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                    <p class="mt-1.5 text-xs text-slate-500">Opsional, tapi disarankan jika Anda memiliki target publikasi/kebutuhan waktu tertentu.</p>
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-4 border-t border-slate-100 pt-6">
                <a href="{{ route('dashboard.services.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Batal</a>
                <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Ajukan Layanan
                </button>
            </div>
        </form>
    </div>
</x-sidebar-layout>
