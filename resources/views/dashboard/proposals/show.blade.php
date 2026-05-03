@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $proposal->title }}</h1>
        <a href="{{ route('dashboard.proposals.index') }}" class="text-blue-600 hover:underline">
            Back to List
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded shadow p-6 mb-6">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Uploader</p>
                <p class="text-lg font-semibold">{{ $proposal->uploader->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="text-lg font-semibold">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if ($proposal->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif ($proposal->status === 'approved') bg-green-100 text-green-800
                        @elseif ($proposal->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($proposal->status) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Created At</p>
                <p class="text-lg">{{ $proposal->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Reviewer</p>
                <p class="text-lg">{{ $proposal->reviewer?->name ?? '—' }}</p>
            </div>
        </div>

        <div class="border-t pt-4">
            <p class="text-sm text-gray-500 mb-2">Review Notes</p>
            <p class="text-gray-700">{{ $proposal->review_notes ?? '—' }}</p>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6 mb-6">
        <a href="{{ route('dashboard.proposals.download', $proposal) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Download File
        </a>
    </div>

    @can('review', $proposal)
        <div class="bg-white rounded shadow p-6">
            <h2 class="text-xl font-bold mb-4">Review Proposal</h2>
            <form action="{{ route('dashboard.proposals.review', $proposal) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                        Review Status
                    </label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-2 border border-gray-300 rounded @error('status') border-red-500 @enderror">
                        <option value="">Select Status</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    @error('status')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="review_notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Review Notes
                    </label>
                    <textarea id="review_notes" name="review_notes" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded @error('review_notes') border-red-500 @enderror">{{ old('review_notes') }}</textarea>
                    @error('review_notes')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Submit Review
                </button>
            </form>
        </div>
    @endcan
</div>
@endsection