<x-sidebar-layout>
    <x-slot name="header">
        @php
            $isBphOrSuperadmin = auth()->user()->hasRole(['bph', 'superadmin']);
            $links = ['Dashboard' => auth()->user()->getDashboardRoute()];
            
            if ($isBphOrSuperadmin) {
                $links['Periksa Dokumen'] = route('dashboard.proposals.index');
            } elseif ($proposal->workProgram) {
                $links[$proposal->workProgram->name] = route('dashboard.workProgram.detail', [
                    'department' => $proposal->uploader->department->slug ?? $proposal->uploader->department_id, 
                    'workProgram' => $proposal->workProgram->id
                ]);
            }
            
            $links[$proposal->title] = null;
        @endphp
        <x-breadcrumb :links="$links" />
    </x-slot>
    <div class="max-w-6xl mx-auto py-2 px-2">
        @include('components.sweet-alert')

        <div class="relative max-w-[90dvw] lg:max-w-6xl mx-auto p-2 bg-white rounded-xl md:rounded-2xl lg:rounded-3xl shadow-lg 
            before:absolute before:inset-0 before:-z-10 before:bg-gradient-to-r before:from-gray-200 before:to-gray-100 
            before:rounded-[inherit] before:p-[0.5px]">
            <div class="bg-white rounded-lg md:rounded-xl lg:rounded-2xl p-4 md:p-6 border border-gray-200">
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-2xl font-bold text-gray-800">{{ $proposal->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Program Kerja: <span class="font-medium text-gray-700">{{ $proposal->nama_proker }}</span></p>
                </div>

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
                    <div class="text-gray-700 trix-content">{!! $proposal->review_notes ?? '—' !!}</div>

                    @if(auth()->id() === $proposal->uploader_id && $proposal->status === 'rejected')
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-red-800 font-semibold mb-2">Silakan unggah dokumen revisi Anda di bawah ini:</p>
                            <form action="{{ route('dashboard.proposals.reupload', $proposal) }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row md:items-center gap-3">
                                @csrf
                                <input type="file" name="file" accept="application/pdf" class="text-sm w-full md:w-auto text-gray-700 bg-white border border-gray-300 rounded-md file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                                <button type="submit" class="text-sm px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-md transition whitespace-nowrap shadow-sm shadow-red-700/20">Unggah Revisi</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="border-t pt-4 mb-6">
                    <p class="text-sm text-gray-500 font-medium mb-4 flex items-center justify-between">
                        Preview Dokumen
                        <a href="{{ route('dashboard.proposals.download', $proposal) }}" 
                            class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-1.5 text-xs font-medium transition bg-[#0b5bd3] text-white shadow-sm shadow-blue-700/20 hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download PDF
                        </a>
                    </p>
                    <div class="w-full h-[600px] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                        @php
                            // Format filename correctly for the PDF viewer
                            $filename = explode('/', $proposal->file_path);
                            $filename = end($filename);
                        @endphp
                        <iframe src="{{ route('pdf.show', ['filename' => $filename]) }}" class="w-full h-full" frameborder="0"></iframe>
                    </div>
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
                            <input id="review_notes" type="hidden" name="review_notes" value="{{ old('review_notes') }}">
                            <trix-editor input="review_notes"
                                class="trix-content w-full min-h-[150px] bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-3 focus:outline-none focus:ring-1 focus:ring-gray-100 focus:shadow-md text-sm text-gray-700 md:text-md lg:text-lg"
                                placeholder="Masukkan catatan review..."></trix-editor>
                            @error('review_notes')
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" 
                                class="flex items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-medium transition bg-[#0b5bd3] text-white shadow-md shadow-blue-700/20 hover:bg-blue-700">
                                Submit Review
                            </button>
                            <a href="{{ url()->previous() }}"
                                class="flex items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-medium transition bg-gray-500 text-white shadow-md hover:bg-gray-600">
                                Cancel / Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css">
    <script>
        document.addEventListener("trix-file-accept", function(event) {
            event.preventDefault();
            alert("Maaf, fitur penyisipan gambar/file ke dalam catatan saat ini dinonaktifkan.");
        });
    </script>
</x-sidebar-layout>