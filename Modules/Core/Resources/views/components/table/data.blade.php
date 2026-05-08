@php use Modules\Core\Helpers\Cstr; @endphp
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
    'resourceTitle' => null,
    'showAction' => null,
    'customRowInsideBody' => null
])
@php
    $resourceTitle ??= 'Data';
    $showAction = $showAction ?? ($canDelete || $canUpdate || $showDetail);
    $actComponent = array_filter(
        $header,
        function ($item, $key) use (&$header) {
            if ($item['field'] === 'action') {
                //set searchable false
                $header[$key]['searchable'] = false;
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
<div {{ $attributes->merge(['class' => 'table-max']) }}>
    <table>
        @if (!empty($header))
            <thead>
                <tr>
                    @if (!empty($showCheck))
                        <th class="cell-check cell-center">
                            <input type="checkbox" class="check-all-item" name="group-all">
                        </th>
                    @endif
                    @if (!empty($showNumber))
                        <th class="cell-check cell-center">
                            {{ is_bool($showNumber) ? 'No' : $showNumber }}
                        </th>
                    @endif
                    @foreach ($header as $i => $item)
                        @php
                            $isHidden = !empty($item['type']) && $item['type'] === 'hidden';
                            $isHtmlForLabel = !empty($item['isHtmlForLabel']);

                            $no = $i + 1;
                            $sortableItem = $sortable && !empty($item['sortable']);
                            $attributes = Page::buildAttributes($item['attributes'] ?? null);

                            $label = $item['label'] ?? null;
                            if (empty($label) && !empty($item['field'])) {
                                $label = Page::defineLabelByField($item['field'], $urlInfo ?? null);
                            }

                            $class = [
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
                        ])>
                            @if($isHtmlForLabel)
                                {!! strip_tags($label, ['br']) !!}
                            @else
                                {!! $label !!}
                            @endif

                            @if(!empty($item['tooltip']))
                                <span data-tooltip="{{ $item['tooltip'] }}" data-placement="top">
                                    <span class="icon icon-information-circle-solid"></span>
                                </span>
                            @endif
                        </th>
                    @endforeach
                    @if ($showAction)
                        <th class="cell-action cell-center">Aksi</th>
                    @endif
                </tr>
            </thead>
        @endif
        <tbody>
            @if ($canCreate)
                <x-core::table.row-edit :$header :$showCheck :$showNumber />
            @endif
            @foreach ($data as $row)
                @php
                    $row['_numberRow'] = !empty($paginateInfo)
                        ? ($paginateInfo->firstItem + $loop->index)
                        : $loop->iteration;
                @endphp

                @if (!empty($header) && $row['id'] == $edit && empty($row['_readonly']))
                    <x-core::table.row-edit :$header :data="$row" :$showCheck :$showNumber />
                @else
                    <x-core::table.row :$header :data="$row" :$canDelete :$canUpdate :$showAction :$showCheck :$isEditInline
                        :$showNumber :$showDetail :readonly="!empty($row['_readonly'])" :$actComponent />
                @endif
            @endforeach
            @if(!empty($customRowInsideBody))
                {{ $customRowInsideBody }}
            @endif
        </tbody>
    </table>
</div>
@if ($canDelete)
    @if (empty($isLivewire))
        @pushOnce('end')
            <x-core::modal.delete id="modal_delete" :$resourceTitle>
                <x-core::form method="DELETE">
                    <x-core::modal.delete-body :$resourceTitle>
                        <x-slot:button type="submit">
                            Hapus
                        </x-slot:button>
                    </x-core::modal.delete-body>
                </x-core::form>
            </x-core::modal.delete>
        @endPushOnce
    @else

        <x-core::modal.delete id="modal_delete" :$resourceTitle>
            <x-core::modal.delete-body :$resourceTitle>
                <x-slot:button data-id wire:click="destroy(event.target.getAttribute('data-id'))">
                    Hapus
                </x-slot:button>
            </x-core::modal.delete-body>
        </x-core::modal.delete>
    @endif
@endif
@if (empty($isLivewire) && !empty($sortable))
    @pushOnce('scripts')
        <script type="module">
            List.eventSort("{!! Page::buildURL(['sort' => '__sort__', 'sortDesc' => '__desc__']) !!}");
        </script>
    @endPushOnce
@endif
