@props([
    'data' => null,
    'gotoPage' => 'gotoPage',
    'nextPage' => 'nextPage',
    'previousPage' => 'previousPage',
])
@if (empty($isLivewire))
    @pushOnce('scripts')
        <script type="module">
            List.eventPagination("{!! Page::buildURL(['page' => '__page__']) !!}");
            List.eventPerPagePagination("{!! Page::buildURL(['perPage' => '__perPage__']) !!}");
        </script>
    @endPushOnce
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
    @php
        $perPageOptions = [];
        foreach (\Modules\Core\Helpers\Pagination::showPerPage() as $baris) {
            $perPageOptions[$baris] = $baris . ' Baris';
        }
    @endphp
    <div class="w-100 d-flex flex-md-row flex-column align-items-center align-items-md-start justify-content-center justify-content-md-end gap-3">
        <div class="" style="width: 7rem">
            <x-core::quantum-3.select :selected="$data->perPage" id="select-navigation" :options="$perPageOptions" />
        </div>
        <nav>
            <ul class="pagination justify-content-md-end mb-0">
                <li class="page-item {{ $data->onFirstPage ? 'disabled' : '' }}">
                    <button data-column="page" data-value="{{ $data->currentPage - 1 }}" class="page-link" role="button" aria-label="Sebelumnya">
                        <i class="sym sym-arrow-narrow-left"></i>
                        <span class="d-none d-xxl-inline-block">Sebelumnya</span>
                    </button>
                </li>
                @if (!in_array(1, $visible))
                    <li class="page-item {{ $data->currentPage == 1 ? 'active' : '' }}">
                        <button data-column="page" data-value="{{ 1 }}" class="page-link">{{ 1 }}</button>
                    </li>
                    @if ($start > 1)
                        <li class="page-item">
                            <button class="page-link">
                                ...
                            </button>
                        </li>
                    @endif
                @endif
                @foreach ($visible as $page)
                    @php
                        $index = (int) $page
                    @endphp
                    <li class="page-item {{ $data->currentPage == $index ? 'active' : '' }}">
                        <button data-column="page" data-value="{{ $index }}" class="page-link">{{ $index }}</button>
                    </li>
                @endforeach
                @if (!in_array($data->lastPage, $visible))
                    @if ($end < $data->lastPage - 1)
                        <li class="page-item">
                            <button class="page-link">
                                ...
                            </button>
                        </li>
                    @endif
                    <li class="page-item {{ $data->currentPage == $data->lastPage ? 'active' : '' }}">
                        <button data-column="page" data-value="{{ $data->lastPage }}" class="page-link">{{ $data->lastPage }}</button>
                    </li>
                @endif
                <li class="page-item">
                    <button data-column="page" data-value="{{ $data->currentPage + 1 }}" class="page-link {{ !$data->hasMorePages ? 'disabled' : '' }}" role="button" aria-label="Selanjutnya">
                        <span class="d-none d-xxl-inline-block">Selanjutnya</span>
                        <i class="sym sym-arrow-narrow-right"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
@else

@endif
