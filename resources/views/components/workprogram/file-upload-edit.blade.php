@props(['name', 'label', 'filePath' => null])

<div class="mb-4">
    <label for="{{ $name }}" class="mb-1 block font-normal text-gray-600 text-sm md:text-lg">
        {{ $label }} (pdf, max: 5 MB)
    </label>

    @if ($filePath)
        <div class="bg-green-50 border border-green-200 p-2 md:p-4 rounded-lg">
            <p class="text-sm md:text-md lg:text-lg text-green-800 font-medium">File {{ ucfirst(str_replace('_url', '', $name)) }} sudah diunggah</p>
            <p class="text-xs text-green-700 mt-1">{{ explode('/', $filePath)[1] ?? $filePath }}</p>
            <p class="text-[10px] md:text-xs text-gray-500 mt-2 italic">File yang sudah diunggah tidak dapat diubah di halaman ini.</p>
        </div>
    @else
        <div class="bg-red-200 p-2 md:p-4 mb-2 rounded-lg w-full text-sm md:text-md lg:text-lg">
            <p class="text-gray-800">File {{ ucfirst(str_replace('_url', '', $name)) }} belum diunggah, silahkan unggah
                disini</p>
        </div>
        
        <input type="file" name="{{ $name }}" id="{{ $name }}" accept="application/pdf"
            class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-gray-700 text-sm md:text-md lg:text-lg">
    @endif
</div>
