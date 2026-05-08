@props([
    'canCreate' => true,
    'canDelete' => true,
    'canUpdate' => false,
    'create' => false,
    'data' => [],
    'edit' => null,
    'filter' => [],
    'header' => [],
    'isReference' => false,
    'withSync' => false,
    'menu' => [],
    'search' => null,
    'showNumber' => null, // bisa boolean atau string ex: 'No' utk dinamis
    'showDetail' => true,
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'navTab' => null,
    'subtitle' => null,
    'title' => null,
    'staticAlert' => [],
    'showDeleteChecked' => true, // tidak diperbolehkan menghapus checked, utk hapus per row sesuai kondisi $canDelete
    'isEditInline' => true,
    'isShowPagination' => true,
])

@php
    // custom
    $isShowPagination = true;
    $isReference = true;
    $sortable = true;

    // [Start] Copy From component/layouts/list.blade.php
    // referensi
    if ($isReference) {
        $canUpdate = true;
        $showDetail = false;
    } else {
        $create = false;
        $edit = null;
    }

    // hak akses
    $permission = request()->permission;
    if ($canCreate && empty($permission['post'])) {
        $canCreate = false;
    }
    if ($canDelete && empty($permission['delete'])) {
        $canDelete = false;
    }
    if ($canUpdate && empty($permission['put'])) {
        $canUpdate = false;
    }
    if ($showDetail && empty($permission['get'])) {
        $showDetail = false;
    }

    // checkbox hanya untuk delete (untuk sekarang)
    $showCheck = $canDelete;
    if (!$showDeleteChecked) {
        $showCheck = false;
    }

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }

    // form
    $action = $method = null;
    if (!empty($edit)) {
        $method = 'PUT';
        $action = Page::detailURL($edit);
    }

    $isEditInline ??= true;
    // [END] Copy From component/layouts/list.blade.php
@endphp

{{--copy from templates/index-reference--}}
<x-core::layouts.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc
                      :withSync="$withSync ?? false" :showNumber="$showNumber ?? false" :$title
                      :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true"
                      :$create :$edit :is-reference="true" :$isShowPagination>
    <x-core::table>
        <x-slot:header>
            <x-core::layouts.list.header :$title :$filter :$search/>
        </x-slot:header>
        <x-core::layouts.html.alert style="margin-bottom:1rem"/>
        @if (!empty($tableHeader))
            {{ $tableHeader }}
        @endif
        <x-core::form id="form_list" :with-upload="false" :$action :$method>
            {{--- [Start] Copy from compoenents.table.data--}}
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

                // [READ!] custom
                $canCreate = $canCreate && $isReference && $create;
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

                                    $no = $i + 1;
                                    $sortableItem = $sortable && !empty($item['sortable']);
                                    $attributes = Page::buildAttributes($item['attributes'] ?? null);

                                    if (isset($item['styleAlign'])) {
                                        $attributes['class'] = 'cell-center';
                                    }

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
                                    {{ $label }}
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
                        <x-core::table.row-edit :$header :$showCheck :$showNumber :$isEditInline/>
                    @endif
                    @php
                        // [READ!] Custom
                        $bobot = 0;
                    @endphp
                    @foreach ($data->items as $row)
                        @php
                            $row['_numberRow'] = !empty($paginateInfo)
                                ? ($paginateInfo->firstItem + $loop->index)
                                : $loop->iteration;

                           // [READ!] Custom
                            $bobot += $row['bobot_komposisi_proposal'] ?? 0;
                        @endphp

                        @if (!empty($header) && $row['id'] == $edit && empty($row['_readonly']))
                            <x-core::table.row-edit :$header :data="$row" :$showCheck :$showNumber :$isEditInline/>
                        @else
                            <x-core::table.row :$header :data="$row" :$canDelete :$canUpdate :$showAction :$showCheck
                                               :$isEditInline :$actComponent
                                               :$showNumber :$showDetail :readonly="!empty($row['_readonly'])" />
                        @endif
                    @endforeach
                    @if(!empty($data->items))
                        <tr>
                            {{-- [READ!] Custom --}}
                            <td colspan="2"><b>Total Bobot Penilaian</b></td>
                            <td colspan="1">
                                <div style="text-align: end;">
                                    <b>
                                        {{ number_format($bobot, 2) }}%
                                    </b>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            @if ($canDelete)
                @if (empty($isLivewire))
                    @pushOnce('end')
                        <x-core::modal.delete id="modal_delete">
                            <x-core::form method="DELETE">
                                <x-core::modal.delete-body>
                                    <x-slot:button type="submit">
                                        Hapus
                                    </x-slot:button>
                                </x-core::modal.delete-body>
                            </x-core::form>
                        </x-core::modal.delete>
                    @endPushOnce
                @else
                    <x-core::modal.delete id="modal_delete">
                        <x-core::modal.delete-body>
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
            {{--- [End] Copy from compoenents.table.data--}}
        </x-core::form>
        @if (!empty($data->items))
            <x-slot:footer>
                <x-core::table.navigation :$data />
            </x-slot:footer>
        @endif
        @if (!$create && empty($data->items))
            <x-core::handler title="Belum Ada Data {{ $title }}"
                             subtitle="Silakan tambahkan data {{ strtolower($title) }} dengan cara klik tombol tambah data"/>
        @endif
    </x-core::table>
</x-core::layouts.list>
