<x-sidebar-layout>
    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Periksa Dokumen' => null]" />
    </x-slot>

    <div class="max-w-7xl mx-auto py-2 px-2">
        @include('components.sweet-alert')

        <div class="flex justify-between items-center mb-4 px-2">
            <h2 class="text-xl font-bold text-gray-800">Daftar Proposal Masuk</h2>
            <form action="{{ route('dashboard.proposals.index') }}" method="GET" class="flex gap-2">
                <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-md text-sm py-1.5 px-3 focus:ring-[#0b5bd3] focus:border-[#0b5bd3]">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Revisi)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Judul Proposal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Divisi Pengirim</th>
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
                            <td class="px-6 py-4 text-sm text-gray-700 font-semibold">{{ $proposal->uploader->department->name ?? '—' }}</td>
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
                                    class="inline-flex items-center gap-1 text-white bg-[#0b5bd3] hover:bg-blue-700 transition px-3 py-1.5 text-xs font-medium rounded-full shadow-sm shadow-blue-700/20">
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

        <div class="mt-4">
            {{ $proposals->links() }}
        </div>
    </div>
</x-sidebar-layout>