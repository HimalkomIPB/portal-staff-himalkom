@props(['name', 'label', 'filePath' => null])

<div class="mb-4">
    <label for="{{ $name }}" class="mb-1 block font-normal text-gray-600 text-sm md:text-lg">
        {{ $label }} (pdf, max: 5 MB)
    </label>

    @if ($filePath)
        <div class="bg-gray-100 p-2 md:p-4 rounded-lg">
            <p class="text-sm md:text-md lg:text-lg text-gray-600">File {{ ucfirst(str_replace('_url', '', $name)) }}:
            </p>
            <p class="text-xs text-gray-800">{{ explode('/', $filePath)[1] ?? $filePath }}</p>
            <p class="text-xs text-red-600">Mengunggah file baru akan menimpa file lama, kosongkan jika tidak ingin
                mengubah file</p>
        </div>
    @else
        <div class="bg-red-200 p-2 md:p-4 mb-2 rounded-lg w-full text-sm md:text-md lg:text-lg">
            <p class="text-gray-800">File {{ ucfirst(str_replace('_url', '', $name)) }} belum diunggah, silahkan unggah
                disini</p>
        </div>
    @endif

    <input type="file" name="{{ $name }}" id="{{ $name }}" accept="application/pdf"
        class="bg-[#FAFAFA] border border-gray-200 shadow-sm rounded-md p-2 w-full focus:ring-1 focus:ring-gray-100 focus:shadow-md focus:border-gray-100 focus:outline-none text-gray-700 text-sm md:text-md lg:text-lg">
</div>
