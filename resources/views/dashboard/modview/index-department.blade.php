<x-app-layout :navigation="Auth::user()->hasRole('supervisor') ? 'layouts.navigation-spv' : 'layouts.navigation'">
    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-[12px]  text-gray-500 font-medium md:text-sm">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <span class="text-gray-800 font-semibold">
                        Supervisi Department
                    </span>
                    <span class="text-gray-400">/</span>
                </nav>
            </div>
        </div>
    </x-slot>
    <div class="max-w-6xl mx-auto py-2 px-2">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 lg:gap-4">
            @forelse ($departments as $department)
                <div
                    class="flex flex-col gap-2 lg:gap-4 relative mx-2 lg:mx-0 bg-white/90 border border-[#111B5A]/30 hover:border-[#14267B]/40 shadow-inner hover:shadow-md rounded-xl p-5 transition duration-200 transform backdrop-blur-sm">

                    <h2 class="uppercase text-lg md:text-xl font-bold text-[#111B5A] tracking-wide">
                        {{ $department->name }}
                    </h2>

                    <div>
                        @if ($department->managing_director)
                            <div>
                                <h3 class="text-[14px] md:text-sm font-semibold text-[#14267B]">Managing Director</h3>
                                <div class="mt-1 text-[12px] md:text-sm font-semibold text-gray-500 space-y-0.5">
                                    <p>{{ $department->managing_director->name }}</p>
                                    <p class="text-[11px] md:text-sm text-gray-400">Email: {{ $department->managing_director->email }}</p>
                                </div>                                
                            </div>
                        @else
                            <span class="text-[14px] md:text-sm font-semibold text-[#14267B] ">Managing Director:
                                -</span>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-[14px] md:text-sm font-semibold text-[#14267B]">Total Program Kerja:
                            <span class="text-gray-500">{{ $department->work_programs_count }}</span>
                        </h3>
                    </div>

                    <div class="mt-auto pt-2 flex items-center">
                        <a href="{{ route('dashboard.modview.department.show', ['department' => $department]) }}"
                            class="inline-flex items-center gap-1 text-white bg-[#111B5A] hover:bg-[#14267B] transition px-3 py-1.5 text-xs font-medium rounded-full">
                            Read More

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 flex justify-center items-center h-40">
                    <p class="text-gray-500 text-lg font-semibold">No data available.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
