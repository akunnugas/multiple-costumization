@props([
    'data' => [],
])
@if (!empty($data))
    <div class="box-table__wrapper">
        <div class="grid cols-1 cols-sm-3 cols-md-5">
            @foreach($data as $key => $entry)
                @php
                    // TODO: ambil satu dulu, quantum pop up filter masih coming soon
                    $label = $entry['label'] ?? \Modules\Core\Helpers\Page::defineTitleByResource($key);
                    $dynamicComponent = Page::defineOptionComponent($entry['options']);
                @endphp

                @if (empty($dynamicComponent))
                    <x-core::select name="{{ $key }}" label="Pilih {{ $label }}" :options="$entry['options']"
                                    :selected="$entry['selected']" wire:change="setFilter('{{ $key }}', event.target.value)" />
                @else
                    <x-dynamic-component :component="$dynamicComponent" name="{{ $key }}" :$label :selected="$entry['selected']"
                                         purpose="filter" wire:change="setFilter('{{ $key }}', event.target.value)" />
                @endif
            @endforeach
        </div>
    </div>
    @if (empty($isLivewire))
        @pushOnce('scripts')
            <script type="module">
                List.eventFilter("{!! Page::buildURL(['filter' => '__', 'page' => null]) !!}");
            </script>
        @endPushOnce
    @endif
@endif
