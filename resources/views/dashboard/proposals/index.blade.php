@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Proposals</h1>
        @can('create', App\Models\Proposal::class)
            <a href="{{ route('dashboard.proposals.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">
                Upload Proposal
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded shadow">
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
                    <tr class="border-t hover:bg-gray-50">
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
                            <a href="{{ route('dashboard.proposals.show', $proposal) }}" class="text-blue-600 hover:underline">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No proposals found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection