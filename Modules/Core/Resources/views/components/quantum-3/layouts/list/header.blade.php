@props([
    'filter' => [],
    'search' => null,
    'title' => null,
])

<div class="row align-items-center justify-content-between gap-2">
    <div class="col-md-3 col-12 mx-0 px-1">

        <x-core::quantum-3.input type="search" value="{{ $search ?? request()->get('search') }}"
            placeholder="Cari data ..."
            wire:model.live.debounce.500ms="search" wire:click="resetSearch" />
    </div>
    <div class="col-md-8 col-12">
        <div class="row justify-content-end">
            {{-- #TODO: Quantum 3. Filter pada section header tabel list --}}
            @php
                $dataFilter = $filter;
            @endphp
            @if (!empty($dataFilter))
                @foreach ($dataFilter as $key => $item)
                    <div class="col-md-4 col-12 px-1 py-1">
                        @if (empty($item['options']))
                            @continue
                        @endif
    
                        @php
                            if ($item['hideLabel'] ?? false) {
                                $label = null;
                            } else {
                                $label = $item['label'] ?? \Modules\Core\Helpers\Page::defineTitleByResource($key);
                                $label = "Pilih {$label}";
                            }
                            $dynamicComponent = Page::defineOptionComponent($item['options']);
                        @endphp
                        @if (empty($dynamicComponent))
                            <x-core::quantum-3.select class="select-filter" variant="search" name="{{ $key }}" label="{{ $label }}" :is_empty="$item['is_empty'] ?? false"
                                :options="$item['options']" :selected="$item['selected']"
                                wire:change="setFilter('{{ $key }}', event.target.value)" purpose="filter" />
                        @else
                            <x-dynamic-component :component="$dynamicComponent" name="{{ $key }}" :$label :selected="$item['selected']"
                                purpose="filter" wire:change="setFilter('{{ $key }}', event.target.value)" />
                        @endif
                    </div>
                @endforeach
    
                @if (empty($isLivewire))
                    @pushOnce('scripts')
                        <script type="module">
                            List.eventFilter("{!! Page::buildURL(['filter' => '__', 'page' => null]) !!}");
                        </script>
                    @endPushOnce
                @endif
            @endif
        </div>

    </div>
</div>

@if (empty($isLivewire))
    @pushOnce('scripts')
        <script type="module">
            List.eventSearch(
                "{!! Page::buildURL(['search' => '__', 'page' => null, 'create' => null, 'edit' => null]) !!}",
                "{!! Page::buildURL(['search' => null, 'page' => null, 'create' => null, 'edit' => null]) !!}"
            );
        </script>
    @endPushOnce
@endif
