<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-[12px] text-gray-500 font-medium md:text-sm">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <span>
                        Proposal
                    </span>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-2 px-2">
        @include('components.sweet-alert')

        <div class="flex justify-end mb-3">
            @can('create', App\Models\Proposal::class)
                <a href="{{ route('dashboard.proposals.create') }}"
                    class="mr-2 lg:mr-0 
                           bg-[#111B5A] 
                           text-white 
                           px-2 py-1 md:px-3 md:py-2 
                           rounded-lg 
                           shadow-md 
                           hover:bg-[#14267B] 
                           transition duration-200 
                           text-[12px] md:text-sm
                           font-semibold 
                           tracking-wide 
                           flex items-center gap-2">
                    <span class="text-lg md:text-xl">+</span>
                    Ajukan Proposal
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Judul Proposal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nama Proker</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Uploader</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reviewer</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reviewed At</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($proposals as $proposal)
                        <tr class="border-t hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $proposal->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $proposal->nama_proker }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $proposal->uploader->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if ($proposal->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif ($proposal->status === 'approved') bg-green-100 text-green-800
                                    @elseif ($proposal->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($proposal->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $proposal->reviewer?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $proposal->reviewed_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('dashboard.proposals.show', $proposal) }}"
                                    class="inline-flex items-center gap-1 text-white bg-[#111B5A] hover:bg-[#14267B] transition px-3 py-1.5 text-xs font-medium rounded-full">
                                    View
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No proposals found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>