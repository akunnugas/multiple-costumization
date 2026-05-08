@props([
    'data' => null,
    'gotoPage' => 'gotoPage',
    'nextPage' => 'nextPage',
    'previousPage' => 'previousPage',
])
@if (empty($isLivewire))
    <div id="paginate"></div>

    @pushOnce('scripts')
        <script type="module">
            let page = List.pagination(
                document.getElementById("paginate"),
                "{!! Page::buildURL(['page' => '<<$page>>', 'create' => null, 'edit' => null]) !!}",
                {{ $data->lastPage }},
                {{ $data->currentPage }}
            );
        </script>
    @endPushOnce
@else
    <div class="pagination">
        <button class="pagination__button-previous" aria-label="Previous" wire:click="{{ $previousPage }}"
            @if ($data->onFirstPage) disabled @endif>
            <span class="pagination__button-text">Previous</span>
        </button>
        @php
            $start = max(1, $data->currentPage - floor(7 / 2));
            $end = min($data->lastPage, $start + 4);
            if ($end === $data->lastPage) {
                $start = max(1, $end - 4);
            }
            $visible = range($start, $end);
            if ($start > 1 && $data->lastPage - 2 > $data->currentPage) {
                $visible = array_slice($visible, 2);
            } elseif ($start > 1 && $data->lastPage - 1 > $data->currentPage) {
                $visible = array_slice($visible, 1);
            }
        @endphp
        <ul class="pagination__list" data-mobile-text="Page {{ $data->currentPage }} of {{ $data->lastPage }}">
            @if (!in_array(1, $visible))
                <li class="pagination__item">
                    <a wire:click="{{ $gotoPage }}(1)">1</a>
                </li>
                @if ($start > 1)
                    <li class="pagination__ellipsis">...</li>
                @endif
            @endif
            @foreach ($visible as $page)
                <li class="pagination__item @if ($page == $data->currentPage) active @endif">
                    <a wire:click="{{ $gotoPage }}({{ $page }})">{{ $page }}</a>
                </li>
            @endforeach
            @if (!in_array($data->lastPage, $visible))
                @if ($end < $data->lastPage - 1)
                    <li class="pagination__ellipsis">...</li>
                @endif
                <li class="pagination__item">
                    <a wire:click="{{ $gotoPage }}({{ $data->lastPage }})">{{ $data->lastPage }}</a>
                </li>
            @endif
        </ul>
        <button class="pagination__button-next" aria-label="Next" wire:click="{{ $nextPage }}"
            @if (!$data->hasMorePages) disabled @endif>
            <span class="pagination__button-text">Next</span>
        </button>
    </div>
@endif
