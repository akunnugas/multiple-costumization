

@props([
    'canCreate' => false,
    'canDelete' => false,
    'canUpdate' => false,
    'data' => [],
    'paginateInfo' => null,
    'edit' => null,
    'header' => [],
    'showCheck' => false,
    'showNumber' => null, // bisa boolean atau string ex: 'No' utk dinamis
    'showDetail' => false,
    'sortable' => false,
    'sort' => null,
    'sortDesc' => null,
    'isEditInline' => true,
    'withSeparator' => true,
    'responsiveTable' => true
])
@php
    $resourceTitle ??= 'Data';
    $showAction = $canDelete || $canUpdate || $showDetail;
    $actComponent = array_filter(
        $header,
        function ($item, $key) use (&$header) {
            if ($item['field'] === 'action') {
                // unset header action
                unset($header[$key]);
            }
            return $item['field'] === 'action';
        },
        ARRAY_FILTER_USE_BOTH,
    );
    $actComponent = array_values($actComponent);
    $actComponent = !empty($actComponent) ? $actComponent[0] : null;
@endphp

@if ($withSeparator)  
<hr />
@endif

<div @class(['table-responsive' => $responsiveTable, 'mb-3 pb-1'])>
    <table class="table table-bordered align-middle mb-0" x-data>
        @if (!empty($header))
            <thead class="align-middle">
                <tr class="table-light">
                    @if (!empty($showCheck))
                        <th class="cell-check text-center" style="width: 13px">
                            <input type="checkbox" class="form-check-input" name="group-all" aria-label="Centang semua data pada tabel" x-model="$store.checkbox.checkAll" x-on:change="$store.checkbox.toggleCheckAll()">
                        </th>
                    @endif
                    @if (!empty($showNumber))
                        <th class="cell-check text-center" style="width: 40px">
                            {{ is_bool($showNumber) ? 'No' : $showNumber }}
                        </th>
                    @endif
                    @foreach ($header as $i => $item)
                        @php
                            $isHidden = !empty($item['type']) && $item['type'] === 'hidden';

                            $no = $i + 1;
                            $sortableItem = $sortable && !empty($item['sortable']);
                            $attributes = Page::buildAttributes($item['attributes'] ?? null);

                            $label = $item['label'] ?? null;
                            if (empty($label) && !empty($item['field'])) {
                                $label = Page::defineLabelByField($item['field'], $urlInfo ?? null);
                            }

                            $class = [
                                'text-nowrap',
                                'cell-sorting' => $sortableItem,
                                'cell-sorting_active-' . (empty($sortDesc) ? 'asc' : 'desc') => $sortableItem && $sort == $no,
                            ];

                            $param = [];
                            if (empty($isLivewire)) {
                                $param['data-no'] = $no;
                            } elseif ($sortableItem) {
                                $param['wire:click'] = 'setSort(' . $no . ($sort == $no && empty($sortDesc) ? ',1' : '') . ')';
                            }
                        @endphp
                        <th {{ $attributes->class($class)->merge($param) }} @style([
                            'display: none' => $isHidden,
                            'cursor: pointer',
                        ])>
                            <div class="d-flex gap-3">
                                @php
                                    $icon = ($no == $sort)
                                        ? ($sortDesc ? 'sym-arrow-narrow-down' : 'sym-arrow-narrow-up')
                                        : 'sym-switch-vertical';
                                @endphp
                                {{ $label }}
                                @if ($sortableItem)    
                                <i class="float-end sym {{ $icon }}"></i>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    @if ($showAction)
                        <th class="cell-action text-center" style="width: 80px">Aksi</th>
                    @endif
                </tr>
            </thead>
        @endif

        <tbody>
            @if ($canCreate)
                <x-core::quantum-3.table.row-edit :$header :$showCheck :$showNumber />
            @endif
            @foreach ($data as $row)
                @php
                    $row['_numberRow'] = !empty($paginateInfo)
                        ? ($paginateInfo->firstItem + $loop->index)
                        : $loop->iteration;
                @endphp

                @if (!empty($header) && $row['id'] == $edit && empty($row['_readonly']))
                    <x-core::quantum-3.table.row-edit :$header :data="$row" :$showCheck :$showNumber />
                @else
                    <x-core::quantum-3.table.row :$header :data="$row" :$canDelete :$canUpdate :$showAction :$showCheck :$isEditInline
                        :$showNumber :$showDetail :readonly="!empty($row['_readonly'])" :$actComponent />
                @endif
            @endforeach
        </tbody>
    </table>
    @if (!empty($emptyElement) && $data->isEmpty())
        {!! $emptyElement !!}
    @endif
</div>


@if ($canDelete)
    @if (empty($isLivewire))
        @pushOnce('end')
            {{-- <x-core::quantum-3.modal.delete id="modal_delete">
                <x-core::quantum-3.form method="DELETE">
                    <x-core::quantum-3.modal.delete-body> --}}
                        {{-- <x-slot:button type="submit">
                            Hapus
                        </x-slot:button> --}}
                    {{-- </x-core::quantum-3.modal.delete-body>
                </x-core::quantum-3.form>
            </x-core::quantum-3.modal.delete> --}}
        @endPushOnce
    @else
            {{-- #TODO: Quantum 3 modal delete livewire --}}
        {{-- <x-core::quantum-3.modal.delete id="modal_delete">
            <x-core::quantum-3.modal.delete-body>
                <x-slot:button data-id wire:click="destroy(event.target.getAttribute('data-id'))">
                    Hapus
                </x-slot:button>
            </x-core::quantum-3.modal.delete-body>
        </x-core::quantum-3.modal.delete> --}}
    @endif
@endif
@if (empty($isLivewire) && !empty($sortable))
    @pushOnce('scripts')
        <script type="module">
            List.eventSort("{!! Page::buildURL(['sort' => '__sort__', 'sortDesc' => '__desc__']) !!}");
        </script>
    @endPushOnce
@endif
