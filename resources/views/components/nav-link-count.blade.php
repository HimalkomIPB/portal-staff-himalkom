@props([
    'title' => '',
    'count' => 0,
])

<span>{{ $title }}</span>

@if ($count > 0)
    <span
        class="absolute -right-6 inline-flex items-center justify-center
                   text-xs font-semibold text-white bg-red-500 rounded-full
                   w-5 h-5">
        {{ $count > 10 ? '10+' : $count }}
    </span>
@endif
