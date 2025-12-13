<x-app-layout :navigation="Auth::user()->hasRole('supervisor') ? 'layouts.navigation-spv' : 'layouts.navigation'">

    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-[11px] text-gray-500 font-medium md:text-sm">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <a href="{{ route('dashboard.archive.department.index') }}"
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        [Archived] Departments
                    </a>
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('dashboard.archive.department.show', ['id' => $department->id]) }}"
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        {{ $department->name }}
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800 font-semibold">
                        {{ $workProgram->name }}
                    </span>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="relative max-w-[90dvw] mx-auto rounded-lg px-2 py-1 md:px-4 md:py-1.5 lg:px-6 lg:py-2">
        <div
            class="bg-white rounded-xl shadow-md border border-gray-200 flex gap-2 flex-row justify-between mt-2 md:mt-3 lg:mt-4 p-3 md:p-4 lg:p-6">
            <div>
                <h1 class="font-bold text-[#111B5A] text-lg md:text-xl lg:text-3xl">
                    {{ $workProgram->name }}
                </h1>
                <span class="text-lg text-red-500 italic">[Archived]</span>
            </div>
        </div>

        @include('components.sweet-alert')

        @php
            $infoItems = [
                'Deskripsi' => $workProgram->description,
                'Periode' => $workProgram->timeline_range_text,
                'Dana' => 'Rp ' . number_format($workProgram->funds, 0, ',', '.'),
                'Sumber Dana' => $workProgram->sources_of_funds,
                'Total Partisipasi' => $workProgram->participation_total . ' Orang',
                'Cakupan Partisipasi' => $workProgram->participation_coverage,
            ];

            $files = [
                'Proposal' => $workProgram->proposal_url,
                'LPJ' => $workProgram->lpj_url,
                'SPJ' => $workProgram->spg_url,
                'Komnews' => $workProgram->komnews_url,
            ];
        @endphp

        <div class="bg-white rounded-xl shadow-md border border-gray-200 mt-2 lg:mt-4 p-3 md:p-4 lg:p-6">
            <div class="flex flex-col justify-center">
                <h2 class="font-bold text-[#111B5A] mb-1 text-md md:text-lg md:mb-2 lg:text-2xl">Informasi Program
                    Kerja</h2>
                <p class="text-[8px] md:text-[10px] text-gray-400 italic mb-0 md:mb-0 ml-2">id: {{ $workProgram->id }}
                <p class="text-[8px] md:text-[10px] text-gray-400 italic mb-0 md:mb-0 ml-2">created at:
                    {{ $workProgram->created_at->format('d M Y H:i') }}
                <p class="text-[8px] md:text-[10px] text-gray-400 italic mb-4 md:mb-4 ml-2">last updated:
                    {{ $workProgram->updated_at->format('d M Y H:i') }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 md:gap-6">
                @foreach ($infoItems as $label => $value)
                    <div class="p-2 md:p-4 bg-blue-50/50 rounded-lg border border-blue-100/70 shadow-sm">
                        <p class="text-[13px] md:text-lg font-semibold text-[#14267B] mb-1">
                            {{ $label }}</p>
                        <p
                            class="text-[12px] md:text-[14px] lg:text-md text-gray-600  {{ $label === 'Dana' ? 'font-semibold' : '' }}">
                            {{ $value }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white  rounded-xl shadow-md border border-gray-200 mt-2 md:mt-3 lg:mt-4 p-3 md:p-4 lg:p-6">
            <h2 class="font-bold text-[#111B5A] mb-2 text-md md:text-lg md:mb-3 lg:mb-4 lg:text-2xl">Dokumen Terkait
            </h2>

            <div class="space-y-3 md:space-y-4">
                @foreach ($files as $label => $url)
                    @if ($url)
                        <div
                            class="flex items-center justify-between p-2 md:p-3 lg:p-4 bg-blue-50/50 rounded-lg border border-blue-100/70">
                            <div>
                                <p class="text-[13px] md:text-lg text-[#14267B] font-semibold">File
                                    {{ $label }}
                                </p>
                                <p class="text-[9px] md:text-sm italic text-gray-600">{{ explode('/', $url)[1] }}</p>
                            </div>
                            <a href="{{ route('pdf.show', ['filename' => explode('/', $url)[1]]) }}" target="_blank"
                                class="text-[10px] w-[140px] md:text-sm px-2 py-2 md:px-4 text-white bg-[#111B5A] hover:bg-[#14267B] rounded-md transition">
                                Lihat / Unduh
                            </a>
                        </div>
                    @else
                        <div class="bg-red-100 border border-red-300 rounded-lg p-2 md:p-3 lg:p-4">
                            <p class="text-[13px] md:text-lg text-red-700 font-medium">File
                                {{ $label }}</p>
                            <p class="text-[9px] md:text-sm text-gray-800">File {{ $label }} belum diunggah</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>


        <div class="bg-white rounded-xl shadow-md border border-gray-200 mt-2 md:mt-3 lg:mt-4 p-3 md:p-4 lg:p-6">
            <div class="mt-1 md:mt-2">
                <h3 class="font-bold text-[#111B5A] mb-2 text-md md:text-lg md:mb-3 lg:mb-4 lg:text-2x">Diskusi &
                    Komentar</h3>
                @if ($workProgram->comments->isNotEmpty())
                    <ul class="space-y-1 md:space-y-2">
                        @foreach ($workProgram->comments as $comment)
                            <div class="rounded-lg p-2">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-[13px] md:text-lg text-[#14267B] font-semibold">
                                            {{ $comment->author->name }}
                                            <span
                                                class="text-[9px] md:text-[14px] lg:text-sm text-gray-400 font-normal">
                                                •
                                                {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}</span>
                                        </p>
                                        <p class="text-[9px] md:text-[14px] lg:text-sm text-gray-400 font-normal">
                                            {{ $comment->author->getRoleNameForTitle() }}
                                        </p>
                                        <div
                                            class="text-[12px] md:text-[16px] lg:text-md trix-content font-normal text-gray-600 mt-1 md:mt-2">
                                            {!! $comment->content !!}
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1 md:mt-2">{{ $comment->created_at }}</p>
                                    </div>
                                </div>
                                <div class="h-[0.5px] md:h-[1px] mt-2 md:mt-3 bg-gray-200 w-full"></div>
                            </div>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600">Belum ada komentar atau catatan.</p>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
