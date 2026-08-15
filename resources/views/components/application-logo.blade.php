@props(['width' => 40, 'height' => 40])

<img src="{{ asset('images/himalkom_logo_bw.svg') }}" width="{{ $width }}" height="{{ $height }}"
    alt="{{ config('app.name', 'Portal Staff Himalkom') }}" {{ $attributes->merge(['class' => 'object-contain']) }} />
