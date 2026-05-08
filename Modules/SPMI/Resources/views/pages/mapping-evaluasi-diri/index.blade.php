@props([
    'canUpdate' => true,
    'data' => [],
    'edit' => null,
    'filter' => [],
    'header' => [],
    'menu' => [],
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'indikatorBobot' => 0,
])
@php
    if ($canUpdate && empty($permission['put'])) {
        $canUpdate = false;
    }

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }

    // form
    $action = $method = null;
    $editURL = Page::buildURL(['edit' => true]);

    if (isset($edit) && $edit != 1) {
        $edit = null;
    }

    if (!empty($edit)) {
        $action = '';
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle>
    @push('head')
        <style>
            .alert.alert_helper::before {
                display: none;
            }
        </style>
    @endpush
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu" />
        </x-slot:sidebar>
    @endif
    @if ($canUpdate && !empty($data))
        <x-slot:action>
            @if (!isset($edit))
                <x-core::button :href="$editURL">
                    <span class="btn__text">Edit Mapping</span>
                </x-core::button>
            @else
                <x-core::button :href="Page::buildURL(['edit' => null])" variant="outline" class="btn_vr-right">
                    Batalkan
                </x-core::button>
                <x-core::button id="save-data">
                    <span class="btn__text">Simpan Mapping</span>
                </x-core::button>
            @endif
        </x-slot:action>
    @endif
    <div class="card card_table">
        <div class="card__body">
            <x-core::table>
                <x-slot:header>
                    <div class="grid">
                        <div class="col-md-6 col-sm-8">
                            <div class="box-table__wrapper" style="display: flex;">
                                @foreach ($filter as $key => $item)
                                    <x-core::select variant="search" name="{{ $key }}" label="{{ $item['label'] }}"
                                        :options="$item['options']" :selected="$item['selected']"
                                        onchange="setFilter('{{ $key }}', event.target.value)" />
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <script>
                        function setFilter(key, value) {
                            const url = new URL(window.location.href);
                            url.searchParams.set(key, value);
                            window.location.href = url.href;
                        }
                        function checkAll() {
                            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                            checkboxes.forEach((checkbox) => {
                                if (checkbox.id === 'check-all') return;
                                checkbox.checked = document.getElementById('check-all').checked;
                            });
                        }
                    </script>
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
                </x-slot:header>
                <x-core::layouts.html.alert style="margin-bottom:1rem" />
                <x-core::form id="form_list" :$action :$method>
                    <div class="table-max">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($header as $i => $item)
                                        @php
                                            $no = $i + 1;
                                            $attributes = Page::buildAttributes($item['attributes'] ?? null);

                                            $label = $item['label'] ?? null;
                                            if (empty($label) && !empty($item['field'])) {
                                                $label = Page::defineLabelByField($item['field']);
                                            }

                                            if ($item['field'] == 'nama_indikator_evaluasi_diri') {
                                                $label = 'Nama Butir';
                                            }
                                        @endphp
                                        <th data-no="{{ $no }}">
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                    <td>
                                        @if (isset($edit) && empty($item['readonly']) && $canUpdate)
                                            <input type="checkbox" id="check-all" onclick="checkAll()" />
                                        @endif
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $key => $row)
                                    <tr>
                                        <input type="hidden" name="{{ "data[$row->id][id]" }}"
                                            value="{{ $row->id }}" />
                                        @foreach ($header as $item)
                                            @php
                                                if (empty($definer) && !empty($item['definer'])) {
                                                    $definer = $row->{$item['field']} ?? null;
                                                }

                                                $value = $row->{$item['field']} ?? null;
                                            @endphp
                                            <td>
                                                <p
                                                    @if ($item['field'] == 'nama_indikator_evaluasi_diri')
                                                        <p @if ($item['field'] == 'nama_indikator_evaluasi_diri') style="margin-left: {{ $row->info_level * 18 }}px" @endif>
                                                            {{ $value }}
                                                        </p>
                                                    @endif

                                                    @if ($item['field'] == 'apakah_data_default')
                                                        <x-spmi::fields.apakah_iku_ikt :value="$value" />
                                                    @endif
                                            </td>
                                        @endforeach
                                        <td>
                                            <input @if (!(isset($edit) && empty($item['readonly']) && $canUpdate)) disabled @endif type="checkbox"
                                                name="data[{{ $row->id }}][selected]" @if($row->is_mapping) checked @endif  value="1" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-core::form>
            </x-core::table>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const form = document.getElementById('form_list');
                const saveData = document.getElementById('save-data');
                if (form && saveData) {
                    saveData.addEventListener('click', () => {
                        form.submit();
                    });
                }
            })()
        </script>
    @endpush
</x-core::layouts.main>
