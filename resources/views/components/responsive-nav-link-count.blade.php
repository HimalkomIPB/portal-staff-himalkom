@props([
    'title' => '',
    'count' => 0,
])

<span class="relative inline-block">
    <span class="text-base sm:text-lg md:text-xl font-semibold">{{ $title }}</span>

    @if ($count > 0)
        <span
            class="absolute -top-1 -right-5
                   flex items-center justify-center
                   text-[10px] sm:text-xs md:text-sm
                   font-semibold text-white bg-red-500 rounded-full
                   w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6
                   select-none"
            aria-label="{{ $count }} unread notifications" role="status">
            {{ $count > 10 ? '10+' : $count }}
        </span>
    @endif
</span>
