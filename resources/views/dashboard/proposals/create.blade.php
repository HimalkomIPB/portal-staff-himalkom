<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row items-center">
            <div class="text-gray-500 font-medium text-[11px] md:text-sm ">
                <nav class="flex items-center space-x-1 md:space-x-2">
                    <a href="{{ route('dashboard.proposals.index') }}"
                        class="hover:underline hover:text-[#111B5A] cursor-pointer">
                        Proposal
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800 font-semibold">
                        Ajukan Proposal
                    </span>
                </nav>
            </div>
        </div>
    </x-slot>

    <div
        class="relative max-w-[90dvw] lg:max-w-6xl mx-auto mt-2 mb-8 p-2 bg-white rounded-xl md:rounded-2xl lg:rounded-3xl shadow-lg 
            before:absolute before:inset-0 before:-z-10 before:bg-gradient-to-r before:from-gray-200 before:to-gray-100 
            before:rounded-[inherit] before:p-[0.5px]">
        <div class="bg-white rounded-lg md:rounded-xl lg:rounded-2xl p-4 md:p-6 border border-gray-200">
            <h1 class="font-extrabold text-gray-900 md:mb-2 text-center text-lg md:text-xl lg:text-3xl">Ajukan Proposal
                Program Kerja
            </h1>

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4 border border-red-400">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('components.sweet-alert')

            <form action="{{ route('dashboard.proposals.store') }}" method="POST" enctype="multipart/form-data"
                class="md:p-3 rounded-md space-y-2 md:space-y-4">
                @csrf

                @php
                    $fields = [
                        'nama_proker' => 'Nama Program Kerja',
                        'title' => 'Judul Proposal',
                        'description' => 'Deskripsi Program Kerja',
                        'start_at' => 'Tanggal Mulai',
                        'finished_at' => 'Tanggal Selesai',
                        'funds' => 'Dana (Rp)',
                        'sources_of_funds' => 'Sumber Dana',
                        'participation_total' => 'Jumlah Partisipan',
                        'participation_coverage' => 'Cakupan Partisipasi',
                        'file' => 'Upload File Proposal (PDF, max: 5 MB)',
                    ];
                @endphp

                @foreach ($fields as $field => $label)
                    <div>
                        <label for="{{ $field }}"
                            class="mb-1 block font-normal text-gray-600 text-sm md:text-lg">{{ $label }}</label>

                        @if ($field === 'description')
                            <textarea name="{{ $field }}" id="{{ $field }}" required
                                class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-3 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-gray-700 text-sm md:text-md lg:text-lg"
                                placeholder="Masukkan deskripsi lengkap program kerja">{{ old($field) }}</textarea>

                        @elseif($field === 'funds')
                            <input type="text" id="funds_display" value="{{ number_format(old('funds', 0), 0, ',', '.') }}"
                                class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-gray-700 text-sm md:text-md lg:text-lg"
                                placeholder="Masukkan jumlah dana">
                            <input type="hidden" name="funds" id="funds" value="{{ old('funds', 0) }}">

                        @elseif($field === 'participation_coverage')
                            <select name="participation_coverage" id="participation_coverage" required
                                class="select2 bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-gray-700 text-sm md:text-md lg:text-lg">
                                <option value="" class="text-gray-700 text-sm md:text-md lg:text-lg">Pilih Cakupan</option>
                                <option value="Prodi" {{ old('participation_coverage') === 'Prodi' ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">Prodi</option>
                                <option value="Sekolah" {{ old('participation_coverage') === 'Sekolah' ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">Sekolah</option>
                                <option value="IPB" {{ old('participation_coverage') === 'IPB' ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">IPB</option>
                                <option value="Nasional" {{ old('participation_coverage') === 'Nasional' ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">Nasional</option>
                                <option value="Internasional" {{ old('participation_coverage') === 'Internasional' ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">Internasional</option>
                            </select>

                        @elseif($field === 'sources_of_funds')
                            <select name="sources_of_funds[]" id="sources_of_funds" required multiple
                                class="select2 bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-gray-700 text-sm md:text-md lg:text-lg">
                                <option value="" disabled class="text-gray-700 text-sm md:text-md lg:text-lg">Pilih Sumber Dana</option>
                                <option value="BPPTN" {{ in_array('BPPTN', old('sources_of_funds', [])) ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">BPPTN</option>
                                <option value="Dana Sekolah" {{ in_array('Dana Sekolah', old('sources_of_funds', [])) ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">Dana Sekolah</option>
                                <option value="Mandiri" {{ in_array('Mandiri', old('sources_of_funds', [])) ? 'selected' : '' }} class="text-gray-700 text-sm md:text-md lg:text-lg">Mandiri</option>
                            </select>

                        @elseif($field === 'file')
                            <x-workprogram.file-upload name="{{ $field }}" />

                        @else
                            <input
                                type="{{ in_array($field, ['start_at', 'finished_at']) ? 'date' : (in_array($field, ['participation_total']) ? 'number' : 'text') }}"
                                name="{{ $field }}" id="{{ $field }}" value="{{ old($field) }}" required
                                {{ in_array($field, ['participation_total']) ? 'min="1"' : '' }}
                                {{ in_array($field, ['nama_proker', 'title']) ? 'maxlength="255"' : '' }}
                                class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-sm text-gray-700 md:text-md lg:text-lg"
                                placeholder="Masukkan {{ strtolower($label) }}">
                        @endif

                        @error($field)
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach

                <div class="text-center">
                    <button type="submit"
                        class="mt-4 bg-[#14267B] text-white px-4 py-2 md:px-6 md:py-2 rounded-xl shadow hover:bg-[#111B5A] hover:transition text-sm md:text-md lg:text-lg">
                        Ajukan Proposal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const displayInput = document.getElementById("funds_display");
        const fundsHiddenInput = document.getElementById("funds");

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'decimal'
            }).format(value);
        }

        function unformatCurrency(value) {
            return value.replace(/\./g, "");
        }

        displayInput.addEventListener("input", function(e) {
            let rawValue = this.value.replace(/\D/g, "");
            this.value = formatCurrency(rawValue);
            fundsHiddenInput.value = unformatCurrency(rawValue);
        });
    });

    $(document).ready(function() {
        $('#participation_coverage').select2();
        $('#sources_of_funds').select2({
            placeholder: 'Pilih Sumber Dana',
            width: '100%'
        });
    });

    FilePond.create(document.getElementById('file'), {
        allowMultiple: false,
        acceptedFileTypes: ['application/pdf'],
        labelIdle: 'Drag & Drop file Proposal atau <span class="filepond--label-action text-[#14267B]">Klik di sini</span>',
        storeAsFile: true
    });
</script>