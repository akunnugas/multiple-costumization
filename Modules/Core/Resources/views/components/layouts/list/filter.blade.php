@props([
    'data' => [],
])
@if (!empty($data))
    <div class="box-table__wrapper">
        <div class="grid cols-1 cols-sm-2 cols-md-3">
            <div class="col-3" style="display: flex; gap: 10px;">
                @php
                    $countOptions = 0;
                    foreach ($data as $key => $value) {
                        if (!empty($value['options']) && (($value['hideView'] ?? false) != true)) {
                            $countOptions++;
                        }
                    }
                @endphp
                @if ($countOptions == 1)
                    <div class="choices"></div>
                    <div class="choices"></div>
                @elseif ($countOptions == 2)
                    <div class="choices"></div>
                @endif
                @foreach ($data as $key => $item)
                    @if ((($item['hideView'] ?? false) == true))
                        @continue
                    @endif

                    @php
                        if ($item['hideLabel'] ?? false) {
                            $label = null;
                        } else {
                            $label = $item['label'] ?? \Modules\Core\Helpers\Page::defineTitleByResource($key);
                            $label = "Pilih {$label}";
                        }
                        $dynamicComponent = Page::defineOptionComponent(($item['options'] ?? []));
                    @endphp
                    @if (empty($dynamicComponent))
                        <x-core::select name="{{ $key }}" label="{{ $label }}" :is_empty="$item['is_empty'] ?? false"
                            :options="($item['options'] ?? [])" :selected="$item['selected']"
                            wire:change="setFilter('{{ $key }}', event.target.value)" variant="search" />
                    @else
                        <x-dynamic-component :component="$dynamicComponent" name="{{ $key }}" :$label :selected="$item['selected']"
                            purpose="filter" wire:change="setFilter('{{ $key }}', event.target.value)" />
                    @endif
                @endforeach
            </div>
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
