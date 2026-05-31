@if ($paginator->hasPages())
<div class="pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span style="opacity:0.3;">←</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">←</a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span>{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active-page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">→</a>
    @else
        <span style="opacity:0.3;">→</span>
    @endif
</div>
@endif