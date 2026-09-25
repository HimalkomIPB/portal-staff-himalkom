<x-filament-panels::page>

    {{-- ===================== SECTION: TAMBAH PJ ===================== --}}
    <x-filament-panels::form wire:submit="tambahPj">
        {{ $this->pjForm }}
        <div class="flex justify-end -mt-2 pb-2 px-6">
            <x-filament::button type="submit" icon="heroicon-o-plus">
                Tambah PJ
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    {{-- ===================== SECTION: JADWAL ===================== --}}
    <x-filament-panels::form wire:submit="saveSchedule">
        {{ $this->scheduleForm }}
        <div class="flex justify-end -mt-2 pb-2 px-6">
            <x-filament::button type="submit" color="gray" icon="heroicon-o-check">
                Simpan Jadwal
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    {{-- ===================== TABEL: DAFTAR PJ ===================== --}}
    <x-filament::section>
        <x-slot name="heading">Daftar Penanggung Jawab</x-slot>
        <x-slot name="description">Semua akun yang terdaftar sebagai PJ SOTM dan dapat melihat hasil seluruh divisi.</x-slot>

        @if ($pjList->isEmpty())
            <div class="py-8 text-center text-sm text-gray-400">
                Belum ada Penanggung Jawab yang ditambahkan.
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                                #
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                                Department
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                                Akun PJ
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                                Email
                            </th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-transparent">
                        @foreach ($pjList as $i => $pj)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">
                                    {{ $pj->department->name ?? '–' }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $pj->user->name ?? '–' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $pj->user->email ?? '–' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        wire:click="hapusPj({{ $pj->id }})"
                                        wire:confirm="Yakin ingin menghapus PJ ini?"
                                        class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 transition"
                                    >
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
