<x-app-layout :navigation="Auth::user()->hasRole('supervisor') ? 'layouts.navigation-spv' : 'layouts.navigation'">
    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-[12px]  text-gray-500 font-medium md:text-sm">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <a href="{{ route('dashboard.archive.department.index') }}"
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        Archived Departments
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800 font-semibold">
                        {{ $department->name }}
                    </span>

                </nav>
            </div>
        </div>
    </x-slot>

    @include('components.sweet-alert')

    <div class="mx-auto relative max-w-[90dvw] rounded-lg px-2 py-1 md:px-4 md:py-1.5 lg:px-6 lg:py-2 gap-2">
        <div
            class="space-y-8 bg-white rounded-xl shadow-md border border-gray-200 flex flex-col mt-2 md:mt-3 lg:mt-4 p-3 md:p-4 lg:p-6">
            <h2 class="text-center font-bold uppercase text-[#111B5A] text-lg md:text-xl lg:text-3xl">
                {{ $department->name }}
            </h2>
            <h3 class="text-center italic text-gray-500 text-sm md:text-base lg:text-lg">
                [Archived]
            </h3>

            <div>
                <h3 class="font-semibold text-[#111B5A] uppercase tracking-wide text-md md:text-lg lg:text-xl">Managing
                    Director</h3>
                <div class="h-[0.5px] md:h-[1px] px-2 mt-1 mb-2 md:mb-3 bg-gray-200 w-full"></div>
                @if ($department->managing_director)
                    <div class="mt-1 text-[12px] md:text-sm font-semibold text-gray-500 space-y-0.5">
                        <p>{{ $department->managing_director->name }}</p>
                        <p class="text-[11px] md:text-sm text-gray-400">Email:
                            {{ $department->managing_director->email }}</p>
                    </div>
                @else
                    <p
                        class="text-[12px] md:text-[16px] lg:text-md trix-content font-normal text-gray-600 mt-1 md:mt-2">
                        -
                    </p>
                @endif
            </div>

            <div>
                <h3 class="font-semibold text-[#111B5A]  uppercase tracking-wide text-md md:text-lg lg:text-xl">
                    Deskripsi</h3>
                <div class="h-[0.5px] md:h-[1px] px-2 mt-1 mb-2 md:mb-3 bg-gray-200 w-full"></div>
                <p class="text-[12px] md:text-[16px] lg:text-md trix-content font-normal text-gray-600 mt-1 md:mt-2">
                    {{ $department->description }}</p>
            </div>

            <div>
                <h3 class="font-semibold text-[#111B5A] uppercase tracking-wide text-md md:text-lg lg:text-xl">Archived
                    At</h3>
                <div class="h-[0.5px] md:h-[1px] px-2 mt-1 mb-2 md:mb-3 bg-gray-200 w-full"></div>
                <p class="text-[12px] md:text-[16px] lg:text-md trix-content font-normal text-gray-600 mt-1 md:mt-2">
                    {{ $department->deleted_at->format('d M Y H:i') }}</p>
            </div>

            <div>
                <h3 class="font-semibold text-[#111B5A] uppercase tracking-wide text-md md:text-lg lg:text-xl">Program
                    Kerja</h3>

                <div class="h-[0.5px] md:h-[1px] px-2 mt-1 mb-2 md:mb-3 bg-gray-200 w-full"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    @forelse ($department->workPrograms as $workProgram)
                        <div class="rounded-xl border border-gray-200 p-4 md:p-5 bg-white hover:shadow-md transition">
                            <h4 class="text-[#111B5A] font-semibold text-sm md:text-base tracking-wide">
                                {{ $workProgram->name }}
                            </h4>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $workProgram->timeline_range_text }}
                            </p>
                            <a href=""
                                class="inline-flex items-center gap-1 text-[#111B5A] hover:text-[#14267B] text-sm font-medium mt-3 transition">
                                Read More →
                            </a>
                        </div>
                    @empty
                        <h1 class="text-sm font-bold text-gray-800 mb-0 w-1/2">No data available.</h1>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
