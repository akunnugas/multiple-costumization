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

    $title = 'Bobot Penilaian AMI';
    $subtitle = '';

    $periodId = $filter['id_audit_periode']['selected'];

    // form
    $action = $method = null;
    $editURL = Page::buildURL(['edit' => true]);

    if (isset($edit) && $edit != 1) {
        $edit = null;
    }

    if (!empty($edit)) {
        $method = 'PUT';
        $action = route('spmi.setting-indikator-bobot.updateSetting');
    }

    $isHasIKT = false;
    $isHasIKU = false;
    if ($filter['id_penilaian_panduan']['selected'] != 'null_filter') {
        $isHasIKT = Modules\SPMI\Models\MappingPenilaianMatriks::join(
            'spmi.penilaian_matriks as pm',
            'pm.id',
            '=',
            'spmi.mapping_penilaian_matriks.id_penilaian_matriks',
        )
            ->where('spmi.mapping_penilaian_matriks.id_audit_periode', $filter['id_audit_periode']['selected'])
            ->where('spmi.mapping_penilaian_matriks.id_unit', $filter['id_unit']['selected'])
            ->where('pm.id_penilaian_panduan', $filter['id_penilaian_panduan']['selected'])
            ->where('pm.apakah_data_default', false)
            ->exists();

        $isHasIKU = Modules\SPMI\Models\MappingPenilaianMatriks::join(
            'spmi.penilaian_matriks as pm',
            'pm.id',
            '=',
            'spmi.mapping_penilaian_matriks.id_penilaian_matriks',
        )
            ->where('spmi.mapping_penilaian_matriks.id_audit_periode', $filter['id_audit_periode']['selected'])
            ->where('spmi.mapping_penilaian_matriks.id_unit', $filter['id_unit']['selected'])
            ->where('pm.id_penilaian_panduan', $filter['id_penilaian_panduan']['selected'])
            ->where('pm.apakah_data_default', true)
            ->exists();
    }
    if ($filter['id_penilaian_panduan']['selected'] != 'null_filter') {
        $apakahDataDefault = Modules\SPMI\Models\PenilaianPanduan::where('id', $filter['id_penilaian_panduan']['selected'])
            ->value('apakah_data_default');
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle>
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu" />
        </x-slot:sidebar>
    @endif
    @if ($canUpdate && !empty($data->items))
        <x-slot:action>
            @if ($apakahDataDefault)
                @if (!isset($edit))
                    <x-core::button :href="$editURL">
                        <span class="btn__text">Atur Persentase</span>
                    </x-core::button>
                @else
                    <x-core::button :href="Page::buildURL(['edit' => null])" variant="outline" class="btn_vr-right">
                        Batalkan
                    </x-core::button>
                    <x-core::button id="save-data">
                        <span class="btn__text">Simpan Data</span>
                    </x-core::button>
                @endif
            @endif
        </x-slot:action>
    @endif
    <div class="card card_table">
        <div class="card__body">
            <x-core::table>
                <x-slot:header>
                    <x-core::layouts.list.header :$title :$filter />
                </x-slot:header>
                @if (isset($edit) && !empty(session()->get('error')))
                    <div class="alert alert_danger">
                        <div class="alert__content">
                            <p>
                                {{ session()->get('error') }}
                            </p>
                        </div>
                        <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
                    </div>
                    <br>
                @endif
                @if (!session('filter_setting_bobot_indikator_penilaian_panduan'))
                    <div class="alert alert_warning">
                        <div class="alert__content">
                            <p>
                                Silakan pilih Panduan Penilaian terlebih dahulu pada filter di atas. Jika Panduan
                                Penilaian tidak ditemukan pada filter, silakan lakukan mapping penilaian matriks
                                pada menu <a style="text-decoration: underline; color: blue;"
                                    href="{{ route('spmi.mapping-matriks-penilaian.index', ['id_audit_periode' => $periodId]) }}">Mapping
                                    Matriks Penilaian</a>.
                            </p>
                        </div>
                        <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
                    </div>
                    <br>
                @elseif (!isset($edit) && $apakahDataDefault)
                    <div class="alert alert @if ($indikatorBobot == 100) alert_helper @else alert_warning @endif">
                        <div class="alert__content">
                            <p>
                                @if ($indikatorBobot == 100)
                                    Klik tombol <b>Atur Persentase</b>, lalu atur ulang bobot pada setiap kategori
                                    indikator sesuai kebutuhan perguruan tinggi Anda.
                                @elseif($indikatorBobot == 0)
                                    Status pada halaman ini menunjukkan keterkaitan bobot indikator dengan Mapping
                                    Matriks Penilaian. <b>"Belum Dimapping"</b> berarti indikator belum terhubung.
                                @endif
                            </p>
                        </div>
                        <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
                    </div>
                    <br>
                @endif
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
                                        @endphp
                                        <th data-no="{{ $no }}">
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $nameIndikator = null;
                                @endphp
                                @foreach ($data->items as $key => $row)
                                    <tr>
                                        <input type="hidden" name="{{ "data[$key][id]" }}"
                                            value="{{ $row['id'] }}" />
                                        @foreach ($header as $item)
                                            @php
                                                if (empty($definer) && !empty($item['definer'])) {
                                                    $definer = $row[$item['field']] ?? null;
                                                }

                                                $value = $row[$item['field']] ?? null;

                                                if ($item['field'] == 'nama_kategori_indikator') {
                                                    // check if value contains utama
                                                    if (str_contains(strtolower($value), 'utama')) {
                                                        $nameIndikator = 'IKU';
                                                    } elseif (str_contains(strtolower($value), 'tambahan')) {
                                                        $nameIndikator = 'IKT';
                                                    }
                                                }
                                            @endphp
                                            @if (isset($edit) && empty($item['readonly']) && $canUpdate)
                                                @php
                                                    $item += [
                                                        'name' => "data[$key]" . '[' . $item['field'] . ']',
                                                        'value' => $row[$item['field']] ?? null,
                                                    ];

                                                    if ($item['field'] == 'percentage') {
                                                        $item['label'] = 'Persentase';
                                                    }

                                                    $attributes = Page::buildAttributes($item);
                                                @endphp
                                                <td>
                                                    <x-core::controls.form {{ $attributes }} :show-label="false" />
                                                </td>
                                            @else
                                                @if ($item['field'] == 'status_bobot')
                                                    <td>
                                                        @if ($nameIndikator == 'IKU')
                                                            <x-core::badge
                                                                variant="{{ $isHasIKU ? 'success' : 'warning' }}"
                                                                type="secondary">
                                                                @if ($isHasIKU)
                                                                    Sudah Dimapping
                                                                @else
                                                                    Belum Dimapping
                                                                @endif
                                                            </x-core::badge>
                                                        @elseif ($nameIndikator == 'IKT')
                                                            <x-core::badge
                                                                variant="{{ $isHasIKT ? 'success' : 'warning' }}"
                                                                type="secondary">
                                                                @if ($isHasIKT)
                                                                    Sudah Dimapping
                                                                @else
                                                                    Belum Dimapping
                                                                @endif
                                                            </x-core::badge>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td>{{ $value }}</td>
                                                @endif
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                                @if (count($data->items) == 0)
                                    <tr>
                                        <td colspan="{{ count($header) }}" style="padding: 16px; text-align: center;">
                                            <div class="empty-list">
                                                <div class="empty-list__wrapper">
                                                    <div class="empty-list__content"
                                                        style="display: flex !important; justify-content: center !important; align-items: center !important; width: 100% !important; height: 100% !important;">
                                                        <div class="empty-list__inner" style="display: flex; flex-direction: column; text-align: center !important; justify-content: center !important; align-items: center !important;">
                                                            <img src="{{ asset('images/empty-state.png') }}"
                                                                width="200px" alt="illustration"
                                                                style="margin-bottom: 1rem;">
                                                            <h1>{!! 'Belum ada data bobot penilaian. ' !!}</h1>
                                                            <p>{!! 'Data akan muncul setelah penilaian matriks terhubung dengan periode dan unit kerja yang dipilih.' !!}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                <tr style="background: #f8fafc;">
                                    <td colspan="3">
                                        <div style="display: flex; align-items: center; gap: 4px;">
                                            <h5 class="main__title" style="font-size: 14px; font-weight: 500">Total
                                                Bobot
                                                Indikator</h5>
                                            <span class="icon icon-information-circle"></span>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $indikatorBobot }}%
                                    </td>
                                </tr>
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
