@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Upload Proposal</h1>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dashboard.proposals.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-6 rounded shadow">
            @csrf

            <div class="mb-4">
                <label for="nama_proker" class="block text-sm font-medium text-gray-700 mb-2">Nama Program Kerja</label>
                <input type="text" name="nama_proker" id="nama_proker"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                    value="{{ old('nama_proker') }}" 
                    required 
                    maxlength="255"
                    placeholder="Masukkan nama program kerja">
                @error('nama_proker')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>


            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Proposal</label>
                <div class="flex gap-2 mb-2">
                    <input type="text" name="title" id="title"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                        value="{{ old('title') }}" 
                        required
                        maxlength="255" 
                        placeholder="Masukkan judul proposal">
                </div>
                @error('title')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>


            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Upload File (PDF)</label>
                <input type="file" name="file" id="file" accept="application/pdf"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500"
                    required>
                <p class="mt-2 text-sm text-gray-500">
                    Format file: PDF. Maksimal ukuran: 5 MB.
                </p>

                @error('file')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Upload</button>
                <a href="{{ route('dashboard.proposals.index') }}"
                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Cancel</a>
            </div>
        </form>
    </div>
@endsection