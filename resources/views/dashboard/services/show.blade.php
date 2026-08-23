<x-sidebar-layout>
    <x-slot name="title">{{ $service->title }} - Detail Layanan</x-slot>

    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Layanan Antar Divisi' => route('dashboard.services.index'), 'Detail Pengajuan' => null]" />
    </x-slot>

    <div class="px-4 py-8 sm:px-8">


        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            
            {{-- Kiri: Detail & Aksion --}}
            <div class="space-y-6 lg:col-span-2">
                
                {{-- Card Info Utama --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    {{-- Judul & Info --}}
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h1 class="text-2xl font-bold text-slate-800">{{ $service->title }}</h1>
                        <div class="mt-2 flex items-center gap-2 text-sm text-slate-500">
                            <span class="font-medium uppercase text-[#0b5bd3]">{{ $service->type }}</span>
                            <span>&bull;</span>
                            <span>{{ $service->department->name }} ({{ $service->requester->name }})</span>
                        </div>
                    </div>

                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <span class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium {{ $service->status_color }}">
                            {{ $service->status_label }}
                        </span>
                        
                        <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Dibuat</span>
                                {{ $service->created_at->translatedFormat('d M Y H:i') }}
                            </div>
                            @if($service->due_date)
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Tenggat</span>
                                    <span class="{{ $service->due_date->isPast() && $service->status !== 'completed' ? 'text-red-500 font-medium' : '' }}">
                                        {{ $service->due_date->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            @endif
                            @if($service->assignees->count() > 0)
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Ditugaskan Ke</span>
                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                        @foreach($service->assignees as $assignee)
                                            <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 border border-blue-100">
                                                {{ $assignee->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="prose max-w-none text-slate-700">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Spesifikasi & Deskripsi</h3>
                        <p class="mt-2 whitespace-pre-wrap">{{ $service->description }}</p>
                    </div>

                    @if($service->status === 'rejected')
                        <div class="mt-8 rounded-xl border border-red-100 bg-red-50/50 p-6">
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="w-full">
                                    <h3 class="text-sm font-semibold text-red-900">Pengajuan Ditolak</h3>
                                    <p class="mt-1 text-sm text-red-700">Alasan penolakan: {{ $service->rejection_reason }}</p>
                                    
                                    @if(auth()->id() === $service->requester_id)
                                    <div class="mt-4 border-t border-red-100 pt-4">
                                        <a href="{{ route('dashboard.services.edit', $service) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700">
                                            Edit & Ajukan Ulang
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($service->status === 'revision')
                        <div class="mt-8 rounded-xl border border-orange-100 bg-orange-50/50 p-6">
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <h3 class="text-sm font-semibold text-orange-900">Status: Sedang Direvisi</h3>
                                </div>
                            </div>
                        </div>
                    @elseif($service->final_file_path || $service->final_link)
                        <div class="mt-8 rounded-xl border border-indigo-100 bg-indigo-50/50 p-6">
                            <h3 class="text-sm font-semibold text-indigo-900">Hasil Akhir Tersedia</h3>
                            
                            @php
                                $extension = $service->final_file_path ? strtolower(pathinfo($service->final_file_path, PATHINFO_EXTENSION)) : '';
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                            @endphp

                            @if($isImage)
                                <div class="mt-4 overflow-hidden rounded-lg border border-indigo-200">
                                    <img src="{{ Storage::url($service->final_file_path) }}" alt="Preview Hasil Akhir" class="max-h-96 w-full object-contain bg-white">
                                </div>
                            @endif

                            <div class="mt-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="flex flex-wrap gap-3">
                                    @if($service->final_file_path)
                                        <a href="{{ Storage::url($service->final_file_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                            </svg>
                                            Unduh Hasil File
                                        </a>
                                    @endif
                                    
                                    @if($service->final_link)
                                        <a href="{{ $service->final_link }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                                                <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                                            </svg>
                                            Buka Link Hasil
                                        </a>
                                    @endif
                                </div>
                                
                                @can('approve', $service)
                                    @if($service->status === 'uploaded' && !$service->is_approved_by_requester)
                                        <div class="flex flex-col gap-2 shrink-0">
                                            <form action="{{ route('dashboard.services.approve-final', $service) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex w-full h-10 items-center justify-center gap-2 rounded-md bg-emerald-500 px-4 text-sm font-semibold text-white transition hover:bg-emerald-600">
                                                    Terima Hasil
                                                </button>
                                            </form>
                                            <form action="{{ route('dashboard.services.reject-final', $service) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex w-full h-10 items-center justify-center gap-2 rounded-md bg-red-500 px-4 text-sm font-semibold text-white transition hover:bg-red-600">
                                                    Tolak Hasil (Revisi)
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Action Panel for Manager --}}
                @can('update', $service)
                    @if(!$service->is_approved_by_requester)
                        
                        @if($service->status === 'pending')
                            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-6 shadow-sm">
                                <h2 class="mb-4 text-lg font-bold text-indigo-900">Aksi Pengajuan Baru</h2>
                                <p class="mb-4 text-sm text-indigo-700">Ada pengajuan baru masuk. Silakan tinjau spesifikasinya, lalu tentukan apakah akan diterima atau ditolak.</p>
                                
                                <div class="flex flex-wrap gap-3">
                                    <form action="{{ route('dashboard.services.accept-manager', $service) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                                            Terima Pengajuan
                                        </button>
                                    </form>

                                    <div x-data="{ openReject: false }">
                                        <button type="button" @click="openReject = !openReject" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-white border border-red-200 px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:border-red-300">
                                            Tolak Pengajuan
                                        </button>
                                        
                                        <form x-show="openReject" action="{{ route('dashboard.services.reject-manager', $service) }}" method="POST" class="mt-4 flex flex-col gap-3 rounded-xl border border-red-100 bg-white p-4 shadow-sm" style="display: none;">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label for="rejection_reason_new" class="mb-1 block text-sm font-medium text-slate-700">Alasan Penolakan</label>
                                                <textarea name="rejection_reason" id="rejection_reason_new" rows="2" required class="block w-full rounded-md border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Jelaskan mengapa pengajuan ini tidak dapat diproses..."></textarea>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="openReject = false" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Batal</button>
                                                <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md bg-red-600 px-3 text-xs font-semibold text-white hover:bg-red-700">
                                                    Konfirmasi Tolak
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @elseif($service->status !== 'rejected')
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-bold text-slate-800">Manajemen Layanan</h2>
                        <div class="space-y-6">
                            
                            {{-- Ubah Status --}}
                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-slate-700">Perbarui Status</h3>
                                <form action="{{ route('dashboard.services.status.update', $service) }}" method="POST" class="flex items-center gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="block w-full rounded-xl border-slate-300 py-2.5 text-sm text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                                        <option value="accepted" {{ $service->status === 'accepted' ? 'selected' : '' }}>Diterima</option>
                                        <option value="in_progress" {{ $service->status === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    </select>
                                    <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        Update
                                    </button>
                                </form>
                            </div>
                            
                            @if(!auth()->user()->hasRole('anggota'))
                            <hr class="border-slate-100">

                            {{-- Assign MD --}}
                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-slate-700">Tugaskan ke (Assignee)</h3>
                                <form action="{{ route('dashboard.services.assign', $service) }}" method="POST" class="flex flex-col gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="w-full">
                                        <select name="assigned_to[]" multiple="multiple" class="select2-assignees w-full">
                                            @foreach(auth()->user()->department->users as $deptUser)
                                                <option value="{{ $deptUser->id }}" {{ $service->assignees->contains('id', $deptUser->id) ? 'selected' : '' }}>
                                                    {{ $deptUser->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="inline-flex h-10 w-fit items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        Perbarui Penugasan
                                    </button>
                                </form>
                            </div>
                            @endif
                            
                            <hr class="border-slate-100">

                            {{-- Upload Final --}}
                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-slate-700">Unggah Hasil Akhir</h3>
                                <form action="{{ route('dashboard.services.upload-final', $service) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                                    @csrf
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">File Lampiran (Maks 20MB, format: png, jpg, jpeg, pdf, zip)</label>
                                            <input type="file" name="final_file" accept=".png,.jpg,.jpeg,.pdf,.zip" class="block w-full rounded-xl border border-slate-300 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:outline-none">
                                            @error('final_file')
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Atau Link Eksternal (Contoh: Google Drive, Figma)</label>
                                            <input type="url" name="final_link" placeholder="https://" value="{{ old('final_link', $service->final_link) }}" class="block w-full rounded-xl border-slate-300 py-2 text-sm text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                                            @error('final_link')
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700 mt-2">
                                            Upload Final
                                        </button>
                                    </div>
                                </form>
                            </div>



                        </div>
                    </div>
                    @endif
                    @endif
                @endcan
            </div>

            {{-- Kanan: Diskusi & Timeline --}}
            @if(in_array($service->status, ['accepted', 'in_progress', 'revision', 'uploaded', 'completed']))
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col max-h-[800px]">
                    <div class="border-b border-slate-200 p-4">
                        <h2 class="text-lg font-bold text-slate-800">Diskusi & Revisi</h2>
                    </div>
                    
                    {{-- Komentar List --}}
                    <div class="flex-1 overflow-y-auto p-4">
                        @if($service->comments->isEmpty())
                            <div class="py-8 text-center text-sm text-slate-500">
                                Belum ada diskusi atau catatan revisi.
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach($service->comments as $comment)
                                    <div class="flex gap-3 {{ $comment->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-600">
                                            {{ substr($comment->user->name, 0, 1) }}
                                        </div>
                                        <div class="max-w-[85%]">
                                            <div class="flex items-baseline gap-2 {{ $comment->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                                <span class="text-xs font-semibold text-slate-700">{{ $comment->user->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $comment->created_at->format('H:i') }}</span>
                                            </div>
                                            <div class="mt-1 rounded-2xl p-3 text-sm {{ $comment->user_id === auth()->id() ? 'bg-[#0b5bd3] text-white rounded-tr-none' : 'bg-slate-100 text-slate-800 rounded-tl-none' }}">
                                                <p class="whitespace-pre-wrap">{{ $comment->content }}</p>
                                                
                                                @if($comment->attachment_path)
                                                    <a href="{{ Storage::url($comment->attachment_path) }}" target="_blank" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-black/10 px-3 py-1.5 text-xs font-medium backdrop-blur-sm transition hover:bg-black/20">
                                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                                        </svg>
                                                        File Lampiran
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    {{-- Form Komentar --}}
                    @can('comment', $service)
                        @if(!$service->is_approved_by_requester)
                        <div class="border-t border-slate-200 p-4 bg-slate-50 rounded-b-2xl">
                            <form x-data="{ 
                                    fileName: '',
                                    handlePaste(e) {
                                        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
                                        for (let i = 0; i < items.length; i++) {
                                            if (items[i].kind === 'file') {
                                                const file = items[i].getAsFile();
                                                const dataTransfer = new DataTransfer();
                                                const ext = file.type.split('/')[1] || 'png';
                                                const newFile = new File([file], `pasted-image.${ext}`, { type: file.type });
                                                dataTransfer.items.add(newFile);
                                                this.$refs.fileInput.files = dataTransfer.files;
                                                this.fileName = newFile.name;
                                                break;
                                            }
                                        }
                                    }
                                }" 
                                action="{{ route('dashboard.services.comments.store', $service) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <textarea name="content" @paste="handlePaste" rows="2" required placeholder="Tulis komentar, revisi, atau paste gambar di sini..." class="block w-full resize-none rounded-xl border-slate-300 py-2 text-sm text-slate-700 shadow-sm focus:border-[#0b5bd3] focus:ring-[#0b5bd3]"></textarea>
                                
                                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label for="attachment" class="cursor-pointer text-slate-500 hover:text-slate-700 flex items-center gap-2 text-sm font-medium">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                            </svg>
                                            Lampiran
                                        </label>
                                        <input type="file" id="attachment" name="attachment" x-ref="fileInput" @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''" class="hidden">
                                        
                                        {{-- Indikator File Terpilih --}}
                                        <div x-show="fileName" style="display: none;" class="flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                            </svg>
                                            <span x-text="fileName" class="max-w-[150px] truncate"></span>
                                            <button type="button" @click="$refs.fileInput.value = ''; fileName = ''" class="ml-1 text-emerald-600 hover:text-emerald-800 focus:outline-none">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        Kirim
                                    </button>
                                </div>
                                @error('attachment')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                        @endif
                    @endcan
                </div>
            </div>
            @endif

        </div>
    </div>
    
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-assignees').select2({
                placeholder: "-- Pilih Anggota --",
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });
        });
    </script>
    <style>
        .select2-container--classic .select2-selection--multiple {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.25rem !important;
            min-height: 44px !important;
        }
        .select2-container--classic.select2-container--focus .select2-selection--multiple {
            border-color: #0b5bd3 !important;
            box-shadow: 0 0 0 1px #0b5bd3 !important;
        }
    </style>
    @endpush
</x-sidebar-layout>
