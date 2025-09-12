<x-app-layout :navigation="Auth::user()->hasRole('supervisor') ? 'layouts.navigation-spv' : 'layouts.navigation'">
    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-[11px] text-gray-500 font-medium md:text-sm">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <a href="{{ route('dashboard.modview.department.index') }}"
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        Supervisi Department
                    </a>
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('dashboard.modview.department.show', ['department' => $workProgram->department]) }}"
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        {{ $workProgram->department->name }}
                    </a>
                    <span class="text-gray-400">/</span>
                    <h2 class="text-gray-800 font-semibold">
                        {{ $workProgram->name }}
                    </h2>
                </nav>

            </div>
        </div>
    </x-slot>

    <div class="relative max-w-[90dvw] mx-auto rounded-lg px-2 py-1 md:px-4 md:py-1.5 lg:px-6 lg:py-2">
        <div
            class="bg-white rounded-xl shadow-md border border-gray-200 flex gap-2 flex-row justify-between mt-2 md:mt-3 lg:mt-4 p-3 md:p-4 lg:p-6">
            <h1 class=" font-bold text-[#111B5A] text-lg md:text-xl  lg:text-3xl ">
                {{ $workProgram->name }}</h1>

            @hasanyrole('bph')
                <div class="flex gap-3 md:gap-4 lg:gap-6 items-center">
                    <a href="{{ route('dashboard.workProgram.edit', ['workProgram' => $workProgram, 'department' => $workProgram->department]) }}"
                        class="text-blue-600 hover:text-blue-800 ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-7 md:h-7 lg:w-8 lg:h-8" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 20h9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2" />
                        </svg>
                    </a>
                    <form
                        action="{{ route('dashboard.workProgram.destroy', ['workProgram' => $workProgram, 'department' => $workProgram->department]) }}"
                        method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-8 md:h-8 lg:w-10 lg:h-10"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    d="M6 7h12M10 11v6m4-6v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M9 3h6a1 1 0 011 1v1H8V4a1 1 0 011-1z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                        </button>
                    </form>
                </div>
            @else
            @endhasanyrole

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
                <h2 class="font-bold text-[#111B5A] mb-1 text-md md:text-lg md:mb-2 lg:text-2xl">📋 Informasi Program
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
            <h2 class="font-bold text-[#111B5A] mb-2 text-md md:text-lg md:mb-3 lg:mb-4 lg:text-2xl">📂 Dokumen Terkait
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
                                📄 Lihat / Unduh
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
                <h3 class="font-bold text-[#111B5A] mb-2 text-md md:text-lg md:mb-3 lg:mb-4 lg:text-2x">💬 Diskusi &
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

                                    @if (Auth::id() === $comment->user_id)
                                        <form method="POST"
                                            action="{{ route('dashboard.workProgram.comment.destroy', ['workProgram' => $workProgram, 'comment' => $comment]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Komentar"
                                                class="text-red-400 hover:text-red-500 italic transition duration-200 ease-in-out text-[9px] md:text-[14px] lg:text-md">
                                                Delete
                                            </button>
                                        </form>
                                    @endif

                                </div>
                                <div class="h-[0.5px] md:h-[1px] mt-2 md:mt-3 bg-gray-200 w-full"></div>
                            </div>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600">Belum ada komentar atau catatan.</p>
                @endif
            </div>

            <div class="mt-2 p-2">
                <h4 class="font-bold text-[#111B5A] mb-2 text-[14px] md:text-[18px] md:mb-3 lg:mb-4 lg:text-xl">📝
                    Tambah Komentar</h4>
                <form method="POST"
                    action="{{ route('dashboard.workProgram.comment.store', ['workProgram' => $workProgram]) }}">
                    @csrf
                    <input id="content" type="hidden" name="content">
                    <trix-editor input="content"
                        class="trix-content w-full h-32 bg-gray-100 border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-gray-300 text-[13px] md:text-[16px] lg:text-md"></trix-editor>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="mt-2 px-2 py-1 md:px-3 md:py-1.5 lg:px-4 lg:py-2 text-white bg-[#111B5A] hover:bg-[#14267B] rounded-md transition text-[10px] md:text-[14px] lg:text-sm">
                            Kirim Komentar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css">
</x-app-layout>
