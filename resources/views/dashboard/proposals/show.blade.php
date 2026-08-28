<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-[12px] text-gray-500 font-medium md:text-sm">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <a href="{{ route('dashboard.proposals.index') }}" 
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        Proposal
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800 font-semibold">
                        {{ $proposal->title }}
                    </span>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-2 px-2">
        @include('components.sweet-alert')

        <div class="relative max-w-[90dvw] lg:max-w-6xl mx-auto p-2 bg-white rounded-xl md:rounded-2xl lg:rounded-3xl shadow-lg 
            before:absolute before:inset-0 before:-z-10 before:bg-gradient-to-r before:from-gray-200 before:to-gray-100 
            before:rounded-[inherit] before:p-[0.5px]">
            <div class="bg-white rounded-lg md:rounded-xl lg:rounded-2xl p-4 md:p-6 border border-gray-200">
                <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
                    <div>
                        <p class="text-[12px] md:text-sm text-gray-500 font-medium">Uploader</p>
                        <p class="text-sm md:text-lg font-semibold text-gray-800">{{ $proposal->uploader->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $proposal->uploader->department->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[12px] md:text-sm text-gray-500 font-medium">Status</p>
                        <p class="text-sm md:text-lg font-semibold">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block
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
                        <p class="text-[12px] md:text-sm text-gray-500 font-medium">Created At</p>
                        <p class="text-sm md:text-lg text-gray-800">{{ $proposal->created_at->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[12px] md:text-sm text-gray-500 font-medium">Reviewer</p>
                        <p class="text-sm md:text-lg text-gray-800">{{ $proposal->reviewer?->name ?? '—' }}</p>
                    </div>
                </div>

                <div class="border-t pt-4 mb-6">
                    <p class="text-sm text-gray-500 font-medium mb-2">Review Notes</p>
                    <p class="text-gray-700">{{ $proposal->review_notes ?? '—' }}</p>
                </div>

                <div>
                    <a href="{{ route('dashboard.proposals.download', $proposal) }}" 
                        class="inline-flex items-center gap-2 bg-[#111B5A] text-white px-4 py-2 rounded-lg hover:bg-[#14267B] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download File
                    </a>
                </div>
            </div>
        </div>

        @can('review', $proposal)
            <div class="relative max-w-[90dvw] lg:max-w-6xl mx-auto mt-6 p-2 bg-white rounded-xl md:rounded-2xl lg:rounded-3xl shadow-lg 
                before:absolute before:inset-0 before:-z-10 before:bg-gradient-to-r before:from-gray-200 before:to-gray-100 
                before:rounded-[inherit] before:p-[0.5px]">
                <div class="bg-white rounded-lg md:rounded-xl lg:rounded-2xl p-4 md:p-6 border border-gray-200">
                    <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 mb-6">Review Proposal</h2>
                    <form action="{{ route('dashboard.proposals.review', $proposal) }}" method="POST" class="space-y-4 md:space-y-6">
                        @csrf

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-600 mb-2">
                                Review Status
                            </label>
                            <select id="status" name="status" required
                                class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-sm text-gray-700 md:text-md lg:text-lg">
                                <option value="">Pilih Status</option>
                                <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('status')
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="review_notes" class="block text-sm font-medium text-gray-600 mb-2">
                                Review Notes
                            </label>
                            <textarea id="review_notes" name="review_notes" rows="5"
                                class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-3 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-sm text-gray-700 md:text-md lg:text-lg"
                                placeholder="Masukkan catatan review...">{{ old('review_notes') }}</textarea>
                            @error('review_notes')
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" 
                                class="bg-[#14267B] text-white px-6 py-2 rounded-lg hover:bg-[#111B5A] transition font-semibold text-sm md:text-md">
                                Submit Review
                            </button>
                            <a href="{{ route('dashboard.proposals.index') }}"
                                class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition font-semibold text-sm md:text-md">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</x-app-layout>