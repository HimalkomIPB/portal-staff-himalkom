<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>


    <div class="py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                {{-- Greeting Message --}}
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Hallo, {{ Auth::user()->name }} 👋</h1>
                        <p class="mt-1 text-gray-600 text-lg">Welcome back to your dashboard!</p>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="mt-5">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Links</h2>

                    <ul class="space-y-3 text-base text-blue-700">

                        <li>
                            <a href="{{ route('profile.edit') }}" class="hover:underline hover:text-blue-900">
                                Profile
                            </a>
                        </li>

                        @hasanyrole('managing director|pjs')
                            <li>
                                <span> {{ $department->name }}</span>
                                <ul class="ml-5 mt-2 space-y-2 text-sm text-blue-600">
                                    <li>
                                        <a href="{{ route('dashboard.workProgram.create', $department) }}"
                                            class="hover:underline hover:text-blue-800">
                                            - Buat Program Kerja Baru
                                        </a>
                                    </li>
                                    <li>
                                        <span>- Program Kerja</span>
                                        <ul class="ml-5 mt-1 space-y-1">
                                            @forelse ($departmentWorkPrograms as $workProgram)
                                                <li>
                                                    <a href="{{ route('dashboard.workProgram.detail', [$department, $workProgram]) }}"
                                                        class="hover:underline hover:text-blue-800">
                                                        - {{ $workProgram->name }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li>
                                                    <span class="text-gray-500">No Data Available.</span>
                                                </li>
                                            @endforelse
                                            <li>
                                                <a href="{{ route('dashboard.workProgram.index', $department) }}"
                                                    class="hover:underline hover:text-blue-800">
                                                    - Lihat semua Program Kerja
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        @else
                        @endhasanyrole


                        @hasanyrole('bph')
                            @unlessrole('managing director|pjs')
                                <li>
                                    <span> {{ $department->name }}</span>
                                    <ul class="ml-5 mt-2 space-y-2 text-sm text-blue-600">
                                        <li>
                                            <a href="{{ route('dashboard.workProgram.create', $department) }}"
                                                class="hover:underline hover:text-blue-800">
                                                - Buat Program Kerja Baru
                                            </a>
                                        </li>
                                        <li>
                                            <span>- Program Kerja</span>
                                            <ul class="ml-5 mt-1 space-y-1">
                                                @forelse ($departmentWorkPrograms as $workProgram)
                                                    <li>
                                                        <a href="{{ route('dashboard.workProgram.detail', [$department, $workProgram]) }}"
                                                            class="hover:underline hover:text-blue-800">
                                                            - {{ $workProgram->name }}
                                                        </a>
                                                    </li>
                                                @empty
                                                    <li>
                                                        <span class="text-gray-500">No Data Available.</span>
                                                    </li>
                                                @endforelse
                                                <li>
                                                    <a href="{{ route('dashboard.workProgram.index', $department) }}"
                                                        class="hover:underline hover:text-blue-800">
                                                        - Lihat semua Program Kerja
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            @endunlessrole
                            <li>
                                <div>
                                    <span>Supervisi Department</span>
                                    <ul class="ml-5 mt-2 space-y-2 text-sm text-blue-600">
                                        @forelse ($departmentSlugs as $slug => $name)
                                            <li>
                                                <a href="{{ route('dashboard.modview.department.show', $slug) }}"
                                                    class="hover:underline hover:text-blue-800">
                                                    - {{ $name }}
                                                </a>
                                            </li>
                                        @empty
                                            <li>
                                                <span class="text-gray-500">No Data Available.</span>
                                            </li>
                                        @endforelse
                                        <li>
                                            <a href="{{ route('dashboard.modview.department.index') }}"
                                                class="hover:underline hover:text-blue-800">
                                                - View All Departments
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @else
                        @endhasanyrole

                        <li>
                            <a href="{{ route('dashboard.notifications.index') }}"
                                class="hover:underline hover:text-blue-900">
                                Notifications
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
