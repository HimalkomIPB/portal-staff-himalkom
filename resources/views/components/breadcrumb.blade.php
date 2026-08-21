@props(['links'])

<nav class="flex text-gray-500 font-medium text-sm" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2">
    @foreach($links as $label => $url)
        <li class="inline-flex items-center">
            @if(!$loop->last)
                @if($url)
                    <a href="{{ $url }}" class="inline-flex items-center hover:text-gray-900 transition-colors">
                        @if($loop->first)
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        @endif
                        {{ $label }}
                    </a>
                @else
                    <span class="inline-flex items-center">
                        @if($loop->first)
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        @endif
                        {{ $label }}
                    </span>
                @endif
                <svg class="w-4 h-4 ml-1 md:ml-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
            @else
                <span class="text-gray-800 font-semibold" aria-current="page">{{ $label }}</span>
            @endif
        </li>
    @endforeach
  </ol>
</nav>
