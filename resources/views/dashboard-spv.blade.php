<x-app-layout navigation='layouts.navigation-spv'>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                {{-- Greeting Message --}}
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-sm sm:text-lg md:text-xl font-bold text-gray-900">
                            Hallo, {{ Auth::user()->name }} 👋
                        </h1>
                        <p class="mt-1 text-sm sm:text-base md:text-lg text-gray-600">
                            Welcome back to your dashboard!
                        </p>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="mt-5">
                    <h2 class="text-sm sm:text-lg md:text-xl font-bold text-gray-800 mb-4">
                        Quick Links
                    </h2>

                    <ul class="space-y-3 text-sm sm:text-base md:text-lg text-blue-700">

                        <li>
                            <a href="{{ route('profile.edit') }}" class="hover:underline hover:text-blue-900">
                                Profile
                            </a>
                        </li>

                        <li>
                            <div>
                                <span class="font-medium text-gray-700">Supervisi Department</span>
                                <ul class="ml-5 mt-2 space-y-2 text-sm sm:text-base text-blue-600">
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
