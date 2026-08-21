<x-sidebar-layout>
    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Arsip Department' => null]" />
    </x-slot>
    <div class="max-w-6xl mx-auto py-2 px-2">
        @forelse ($groupedDepartments as $cabinetYear => $departments)
            <div class="mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-[#111B5A] mb-3 border-b-2 border-[#111B5A]/20 pb-2">
                    Kabinet {{ $cabinetYear }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 lg:gap-4">
                    @foreach ($departments as $department)
                        <div
                            class="flex flex-col gap-2 lg:gap-4 relative mx-2 lg:mx-0 bg-white/90 border border-[#111B5A]/30 hover:border-[#14267B]/40 shadow-inner hover:shadow-md rounded-xl p-5 transition duration-200 transform backdrop-blur-sm">

                            <h2 class="uppercase text-lg md:text-xl font-bold text-[#111B5A] tracking-wide">
                                {{ $department->name }}
                            </h2>

                            <div>
                                @if ($department->managing_director)
                                    <div>
                                        <h3 class="text-[14px] md:text-sm font-semibold text-[#14267B]">Managing
                                            Director</h3>
                                        <div
                                            class="mt-1 text-[12px] md:text-sm font-semibold text-gray-500 space-y-0.5">
                                            <p>{{ $department->managing_director->name }}</p>
                                            <p class="text-[11px] md:text-sm text-gray-400">Email:
                                                {{ $department->managing_director->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[14px] md:text-sm font-semibold text-[#14267B] ">Managing
                                        Director:
                                        -</span>
                                @endif
                            </div>

                            <div>
                                <h3 class="text-[14px] md:text-sm font-semibold text-[#14267B]">Program Kerja:
                                    <span class="text-gray-500">{{ $department->work_programs_count }}</span>
                                </h3>

                                <h3 class="text-[14px] md:text-sm font-semibold text-[#14267B]">Diarsip:
                                    <span class="text-gray-500">{{ $department->deleted_at->format('d M Y') }}</span>
                                </h3>
                            </div>

                            <div class="mt-auto pt-2 flex items-center">
                                <a href="{{ route('dashboard.archive.department.show', ['id' => $department->id]) }}"
                                    class="inline-flex items-center gap-1 text-white bg-[#111B5A] hover:bg-[#14267B] transition px-3 py-1.5 text-xs font-medium rounded-full">
                                    Read More

                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="flex justify-center items-center h-40">
                <p class="text-gray-500 text-lg font-semibold">No data available.</p>
            </div>
        @endforelse
    </div>
</x-sidebar-layout>
