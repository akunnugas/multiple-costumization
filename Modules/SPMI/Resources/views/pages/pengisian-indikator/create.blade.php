@php
    use Modules\SPMI\Models\PenilaianMatriks;
    use Modules\DMS\Helpers\DokumenHelper;

    // default title
    $title ??= 'Detail Pengisian Laporan Kinerja';
    if (empty($subtitle) && !empty($title)) {
        $subtitle = $title;
    }

    $isCreate = empty($resourceId);

    if ($isCreate) {
        $method = 'POST';
        $action = Page::indexURL();
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
    }

    $isEdit = false;

    if (empty($_GET['is_edit']) && empty($_GET['id_record'])) {
        $isEdit = false;
        // set url to edit
        $urlChange = Page::buildURL(['is_edit' => '1']);
    } else {
        $isEdit = true;
        // delete edit parameter
        $urlChange = Page::buildURL(['is_edit' => null]);
    }

    $information = Page::showURLInfo();
    $urlReports = route('spmi.reports.filling-reports.generate');

    // Get tarik data
    $tarikDataLK = Modules\SPMI\Models\TarikDataLK::where(
        'id_indikator_laporan_kinerja',
        $data['indicator']['id'],
    )->first();
    $isGetData = !empty($tarikDataLK);
    $isGetDataKurikulum = !empty($tarikDataLK) && $tarikDataLK->apakah_kurikulum;

    // is act
    $isAction =
        end($data['table_input']) == '_action_'
            ? $data['apakah_tanggal_pengisian_valid'] && $data['apakah_sudah_mapping']
            : false;
@endphp

@pushOnce('head')
    <style>
        .full-page-loader {
            display: flex;
            position: fixed;
            left: 0;
            top: 0;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #ffffff90;
            z-index: 9999;
        }

        .hidden {
            display: none !important;
        }
    </style>
@endPushOnce

<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/pengisian-indikator/create.scss')
        @vite('Modules/SPMI/Resources/assets/js/pengisian-indikator/create.js')
    @endpush

    <x-core::form id="form_list" :$method :$action>
        <input type="hidden" id="teks_pengisian" name="teks_pengisian" />
        <div class="container">
            <div class="card">
                <div class="card__header">
                    <ul class="breadcrumb">
                        <li class="breadcrumb__item">
                            <span class="icon icon-home-mini"></span>
                        </li>
                        @foreach ($breadcrumb['items'] as $i => $item)
                            @if ($item['showLink'])
                                <li class="breadcrumb__item">
                                    <a href="{{ url($item['path']) }}">
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @elseif (!empty($item['label']))
                                <li class="breadcrumb__item active">{{ $item['label'] }}</li>
                            @endif
                        @endforeach
                        @if ($breadcrumb['showTitle'])
                            <li class="breadcrumb__item active">{{ $subtitle }}</li>
                        @endif
                    </ul>

                    <div class="button-group">
                        <div class="button-group__left">
                            <a class="btn btn_outline btn_xs" href="{{ route('spmi.pengisian-indikator.index') }}">
                                Kembali ke List
                            </a>

                            @if ($data['apakah_bisa_aksi'])
                                <a onclick="List.showReport('{{ $urlReports }}');"
                                    class="btn btn_outline btn_xs report-button">
                                    <i class="icon icon-printer"></i>
                                    Laporan
                                </a>
                                <button type="button" class="btn btn_outline btn_xs report-button"
                                    id="btn_salin_data" data-toggle="modal" data-target="#salin-data">
                                    <i class="icon icon-clipboard-document"></i> Salin Data
                                </button>

                                @if ($data['apakah_sudah_mapping'])
                                    @if (
                                        $isGetData &&
                                            ($data['apakah_tarik_data_hr'] ? $data['apakah_hr_aktif'] : true) &&
                                            $data['apakah_tanggal_pengisian_valid']
                                    )
                                        <button type="button" class="btn btn_outline btn_xs report-button"
                                            id="btn_filling_getdata">
                                            <i class="icon icon-arrow-down-tray"></i> Tarik Data
                                        </button>
                                    @endif
                                    @if (
                                        !empty($resourceId) &&
                                            isset($data['indicator']['nomor_indikator']) &&
                                            $data['apakah_tanggal_pengisian_valid'] &&
                                            !$isEdit)
                                        @php
                                            $encodedDeleteAll = base64_encode(
                                                json_encode([
                                                    'id' => $data['table_key']['id_indikator_laporan_kinerja'],
                                                ]),
                                            );
                                        @endphp
                                        <a type="button" class="btn btn_destructive btn_xs" id="btn_delete_all"
                                            href="javascript:deleteRecordAll('{{ $encodedDeleteAll }}')"
                                            data-btn-label="Hapus">
                                            <i class="icon icon-trash"></i> Hapus Data
                                        </a>
                                    @endif
                                    @if (
                                        $data['apakah_tanggal_pengisian_valid'] &&
                                            ($data['indicator']['jenis_form'] == Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_COLUMN ||
                                                !$data['indicator']['apakah_data_default'] || $data['apakah_iku_kualitatif']))
                                        @if (!$isEdit)
                                            <a class="btn btn_primary btn_xs" href="{{ $urlChange }}">
                                                Ubah Data
                                            </a>
                                        @else
                                            <a href="{{ $urlChange }}">
                                                <button type="button" class="btn btn_outline btn_xs">
                                                    Batal
                                                </button>
                                            </a>
                                            <button type="submit"
                                                @if (!$data['indicator']['apakah_data_default'] || $data['apakah_iku_kualitatif'])
                                                    onclick="document.getElementById('teks_pengisian').value = CKEDITOR.instances['text-editor'].getData();" @endif
                                                class="btn btn_primary btn_xs">
                                                Simpan
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        </div>

                        <div class="button-group__mobile">
                            <button type="button" class="btn btn_outline btn_icon btn_xs">
                                <span class="icon icon-arrow-left-solid"></span>
                            </button>

                            <button type="button" class="btn btn_primary btn_icon btn_xs">
                                <span class="icon icon-pencil-solid"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card__body">
                    <div class="sidebar-new">
                        <div class="title-sidebar">
                            <h2>Pengisian Laporan Kinerja</h2>
                        </div>

                        <div class="body-sidebar tree">
                            <ul>
                                {!! $data['sidebar'] !!}
                            </ul>
                        </div>
                    </div>
                    <div class="content">
                        <div class="body">
                            <div class="legend">
                                @foreach ($data['information'] as $key => $value)
                                    @php
                                        $label = Page::defineLabelByField($key);
                                    @endphp
                                    <div class="legend__section">
                                        <div class="legend__section_left">
                                            <h3>{{ $label }}</h3>
                                        </div>
                                        <span>:</span>
                                        <div class="legend__section_right">
                                            <p>{{ $value }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if (!$data['apakah_tanggal_pengisian_valid'])
                                @php
                                    $isBeforeAssessmentStart = $data['apakah_pengisian_belum_dimulai'];
                                @endphp
                                <div class="alert alert_{{ $isBeforeAssessmentStart ? 'warning' : 'danger' }}"
                                    style="margin-bottom: 1rem">
                                    <div class="alert__content">
                                        <p>Pengisian tanggal <b>{{ $data['information']['filling_date'] }}</b>
                                            {{ $isBeforeAssessmentStart
                                                ? 'belum dibuka. Anda belum bisa melakukan pengisian.'
                                                : 'telah berakhir. Anda tidak bisa melakukan pengisian lagi.' }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <x-core::layouts.html.alert style="margin-bottom:1rem" />

                            @if ($isGetData && ($data['apakah_tarik_data_hr'] ? $data['apakah_hr_aktif'] : true) && $data['apakah_sudah_mapping'])
                                <div class="panel-group">
                                    <div class="panel panel-info">
                                        <div class="panel-heading" data-target="#panel-keterangan">
                                            <div class="">
                                                <i class="icon icon-information-circle-solid"></i>
                                                Keterangan Tarik Data
                                                <i class="icon icon-chevron-down-solid right indicator-icon"></i>
                                            </div>
                                        </div>
                                        <div id="panel-keterangan" class="panel-collapse">
                                            <div class="panel-body">
                                                {!! $data['indicator']['deskripsi_sumber_data'] !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (!$data['apakah_sudah_mapping'])
                                <div class="alert alert alert_danger" style="margin-bottom: 1rem">
                                    <div class="alert__content">
                                        <h4 class="alert__heading">
                                            Butir ini tidak dapat diisi.
                                        </h4>
                                        <p>
                                            Anda tidak dapat mengisi butir ini karena untuk jenjang
                                            <b>{{ $data['table_key']['nama_jenjang'] }}</b> tidak disediakan opsi
                                            pengisian.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <p>
                                {!! $data['indicator']['deskripsi'] !!}
                            </p>

                            @if ($data['apakah_panduan_default'] && !$data['apakah_iku_kualitatif'] && $data['indicator']['apakah_data_default'])
                                <style>
                                    .table thead th {
                                        white-space: normal !important;
                                    }
                                    table.action-sticky .cell-action {
                                        position: sticky;
                                        right: 0;
                                        border-left: var(--qn-table-border);
                                    }
                                    table.action-sticky td.cell-action {
                                        background: white;
                                    }
                                </style>

                                <div class="parent">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped action-sticky">
                                            <thead>
                                                @php
                                                    $dCount = 0;
                                                @endphp
                                                <tr>
                                                    @foreach ($data['table_column'] as $key => $value)
                                                        @if ($value == '_no_')
                                                            <th rowspan="3" class="horizontal">No.</th>
                                                        @elseif ($value == '_action_')
                                                            @if ($isAction)
                                                                @if ($data['apakah_tanggal_pengisian_valid'] && $data['apakah_sudah_mapping'])
                                                                    <th rowspan="3" style="width: 100px;"
                                                                        class="horizontal cell-action">Aksi</th>
                                                                @endif
                                                            @else
                                                                @php
                                                                    $dCount--;
                                                                @endphp
                                                            @endif
                                                        @elseif (is_array($value))
                                                            @php
                                                                $colspan = 0;
                                                                foreach ($value as $sub_value) {
                                                                    if (is_array($sub_value)) {
                                                                        $colspan += count($sub_value);
                                                                    } else {
                                                                        $colspan++;
                                                                    }
                                                                }
                                                            @endphp
                                                            <th rowspan="1" colspan="{{ $colspan }}"
                                                                class="horizontal">{!! $key !!}</th>
                                                        @else
                                                            @php
                                                                $isSelect =
                                                                    is_array($data['table_input'][$dCount]) &&
                                                                    strpos(
                                                                        $data['table_input'][$dCount][0],
                                                                        'select:',
                                                                    ) !== false;
                                                            @endphp
                                                            <th rowspan="3" class="horizontal"
                                                                @if ($isSelect) style="min-width: 230px;" @endif>
                                                                {!! $value !!}
                                                            </th>
                                                        @endif
                                                        @php
                                                            if (is_array($value)) {
                                                                foreach ($value as $sub_value) {
                                                                    if (is_array($sub_value)) {
                                                                        $dCount += count($sub_value);
                                                                    } else {
                                                                        $dCount++;
                                                                    }
                                                                }
                                                            } else {
                                                                $dCount++;
                                                            }
                                                        @endphp
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @foreach ($data['table_column'] as $key => $value)
                                                        @if (is_array($value))
                                                            @foreach ($value as $sub_key => $sub_value)
                                                                @if (is_array($sub_value))
                                                                    <th rowspan="1"
                                                                        colspan="{{ count($sub_value) }}"
                                                                        class="horizontal">{!! $sub_key !!}</th>
                                                                @else
                                                                    <th rowspan="2" class="horizontal">
                                                                        {!! $sub_value !!}</th>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @foreach ($data['table_column'] as $key => $value)
                                                        @if (is_array($value))
                                                            @foreach ($value as $sub_value)
                                                                @if (is_array($sub_value))
                                                                    @foreach ($sub_value as $sub_sub_value)
                                                                        <th style="min-width: 100px;">
                                                                            {!! $sub_sub_value !!}</th>
                                                                    @endforeach
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @for ($i = 1; $i <= $dCount; $i++)
                                                        <th @if ($i == $dCount && $isAction) class="cell-action" @endif>{{ $i }}</th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($data['indicator']['jenis_form'] == Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_ROW)
                                                    @include(
                                                        'spmi::pages.pengisian-indikator.partials.table_row',
                                                        [
                                                            'data' => $data,
                                                            'isAction' => $isAction,
                                                        ]
                                                    )
                                                @elseif ($data['indicator']['jenis_form'] == Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_COLUMN)
                                                    @include(
                                                        'spmi::pages.pengisian-indikator.partials.table_column',
                                                        [
                                                            'data' => $data,
                                                        ]
                                                    )
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="parent">
                                    <table class="table table-bordered table-hover table-striped pt-12">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Pengisian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    @if ($isEdit)
                                                        <div id="text-editor" class="form-control__input text-editor"
                                                            name="teks_pengisian">
                                                            @if (!empty($data['teks_pengisian']))
                                                                {!! $data['teks_pengisian'] !!}
                                                            @endif
                                                        </div>
                                                    @else
                                                        @if (!empty($data['teks_pengisian']))
                                                            {!! $data['teks_pengisian'] !!}
                                                        @else
                                                            <div class="util_text-center util_text-italic">
                                                                <i>Tidak ada data pengisian.</i>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if (!empty($data['data']['information']))
                                <div class="notes">
                                    <h4>Catatan:</h4>
                                    <p>{!! $data['data']['information'] !!}</p>
                                </div>
                            @endif

                            @php
                                $isCanActionDokumenPendukung =
                                    $data['apakah_bisa_aksi'] &&
                                    $data['apakah_tanggal_pengisian_valid'] &&
                                    $data['apakah_sudah_mapping'];
                            @endphp
                            <div class="documents">
                                <div class="header-document">
                                    <h3>Dokumen Pendukung</h3>
                                    @if ($isCanActionDokumenPendukung)
                                        <div class="actions">
                                            <a class="btn btn_xs btn_primary" href="javascript::void(0)"
                                                data-toggle="modal" data-target="#upload-document">
                                                Unggah Dokumen
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="table-max table-max_absolute util_mt-15">
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width: 0; padding: 14px">No</th>
                                            <th>Nama Dokumen Pendukung</th>
                                            <th style="width: 10%">Ukuran</th>
                                            <th style="width: 10%">Terakhir Diubah</th>
                                            <th class="cell-action cell-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- // Jika kosong --}}
                                        @if (empty($data['dokumen_pendukung']))
                                            <tr>
                                                <td colspan="100" class="cell-center">
                                                    <div class="util_text-center util_text-italic">
                                                        <i>Tidak ada dokumen pendukung.</i>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @foreach ($data['dokumen_pendukung'] ?? [] as $item)
                                            @php
                                                $item = (array) $item;
                                                $ext = $item['extension_versi_terbaru'];
                                                if ($item['extension_versi_terbaru'] == 'docx') {
                                                    $ext = 'doc';
                                                }

                                                $assetUrl = asset("images/$ext-solid.svg");
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td>
                                                    <div class="util_d-flex util_flex-center-vertical">
                                                        <img height="25px;" src="{{ $assetUrl }}"
                                                            alt="">&nbsp;&nbsp;{{ $item['nama_dokumen'] }}.{{ $item['extension_versi_terbaru'] }}
                                                    </div>
                                                </td>
                                                <td>{{ Format::formatBytes($item['ukuran']) }}</td>
                                                <td>{{ Carbon\Carbon::parse($item['waktu_diubah'])->diffForHumans() }}
                                                </td>
                                                <td class="cell-action">
                                                    <div class="dropdown-group"
                                                        style="display: flex; align-items: center; gap: 4px">
                                                        @php
                                                            $encoded = base64_encode(
                                                                json_encode([
                                                                    'id' => $item['id'],
                                                                ]),
                                                            );

                                                            $docUrl = DokumenHelper::generateDocUrl($item['slug']);
                                                        @endphp
                                                        <x-core::button leading-icon="eye-solid" variant="outline"
                                                            size="xs" href="{{ $docUrl }}" />
                                                        @if ($isCanActionDokumenPendukung)
                                                            <x-core::button leading-icon="trash-solid"
                                                                variant="outline" size="xs"
                                                                href="javascript:deleteRecordDokumenPendukung('{{ $encoded }}')"
                                                                data-btn-label="Hapus" />
                                                        @endif
                                                        <div class="dropdown-group__target">
                                                            <div class="dropdown-group__toggle">
                                                                <a href="#"
                                                                    class="btn btn_outline btn_xs btn_icon">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($data['table_key'] as $key => $value)
            @php
                $name = "self[$key]";
            @endphp
            <x-core::controls.form :name="$name" :type="'hidden'" :value="$value" />
        @endforeach
        <x-core::controls.form :name="'key'" :type="'hidden'" />
        <x-core::controls.form :name="'act'" :type="'hidden'" />

        <div id="upload-document" class="modal">
            <div class="modal__overlay" data-dismiss="modal"></div>
            <div class="modal__wrapper">
                <div class="modal__header">
                    <div class="modal__header-wrapper">
                        <h3 class="modal__title">Unggah Dokumen Pendukung</h3>
                    </div>
                    <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
                </div>

                <div class="modal__body">
                    <div class="modal__content-upload">
                        <div class="form-control">
                            <div class="upload-draggable">
                                <div class="upload-draggable__box">
                                    <input type="file" class="upload-draggable__file-input"
                                        name="dokumen_pendukung" accept=".pdf,.doc,.docx,.jpg,.png,.xls,.xlsx,.jpeg">
                                    <label class="upload-draggable__icon"><span
                                            class="icon icon-cloud-arrow-up"></span></label>
                                    <h2 class="upload-draggable__title">Klik untuk pilih file</h2>
                                    <p class="upload-draggable__subtitle">atau seret file ke sini</p>
                                    <p class="upload-draggable__support">PDF, DOC, DOCX, JPG, Excel, JPEG, dan PNG
                                        (Max. 10MB)</p>
                                </div>
                                <div class="upload-draggable__success">
                                    Berhasil
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal__footer">
                    <div class="grid cols-1 cols-sm-2">
                        <button class="btn btn_outline" type="button" data-dismiss="modal">
                            Batal
                        </button>
                        <button class="btn btn_primary" type="button" id="save-dokumen">Konfirmasi</button>
                    </div>
                </div>
            </div>
        </div>
        </x-core::controls.>

        <div id="salin-data" class="modal">
            <div class="modal__overlay" data-dismiss="modal"></div>

            <div class="modal__wrapper">
                <div class="modal__header">
                    <div class="modal__header-wrapper">
                        <h3 class="modal__title">Salin Data Laporan Kinerja</h3>
                    </div>
                    <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
                </div>

                <form method="POST" action="{{ route('spmi.pengisian-indikator.copy') }}">
                    @csrf
                    <div class="modal__body">
                        <span>Anda dapat menyalin data dari periode sebelumnya agar tidak perlu mengisi ulang secara
                            manual.
                            Silakan pilih periode asal dan tentukan cakupan indikator yang ingin disalin.</span>
                        @php
                            $raw = $data['raw'];
                            $indicator = isset($data['data'][4]) ? $data['data'][4] : [];
                        @endphp
                        <div style="margin-top: 1rem;">
                            <x-core::input type="hidden" name="id_audit_periode"
                                value="{{ $raw['id_audit_periode'] }}" />
                            <x-core::input type="hidden" name="route_name"
                                value="{{ Route::currentRouteName() }}" />
                            <x-core::input type="hidden" name="id_unit" value="{{ $raw['id_unit'] }}" />
                            <x-core::input type="hidden" name="id_pengisian_panduan"
                                value="{{ $raw['id_pengisian_panduan'] }}" />
                            <x-core::input type="hidden" name="id_jadwal_audit"
                                value="{{ $raw['id_jadwal_audit'] ?? '' }}" />
                            <x-core::input type="hidden" name="id_indikator_laporan_kinerja"
                                value="{{ $indicator['id'] ?? null }}" />
                            <x-core::controls.form name="active_periode" purpose="form" id="active_periode"
                            :value="$indicator['audit_period_label'] ?? ''" label="Periode Aktif" control="input" disabled required />
                            <x-core::controls.form name="previous_source" purpose="form" id="previous_source"
                                :options="$indicator['previous_year_audit'] ?? []" label="Salin dari Kegiatan AMI" control="select" variant="search"
                                data-search-placeholder="Pilih Kegiatan AMI"
                                placeholder="{{ !empty($indicator['previous_year_audit']) ? 'Pilih Kegiatan AMI' : 'Tidak ada data di periode sebelumnya' }}"
                                :disabled="empty($indicator['previous_year_audit'])" required />

                            @if (!empty($indicator['previous_year_audit']) && isset($indicator['nama_indikator_laporan_kinerja']))
                                <x-core::controls.form control="radio" name="scope" label="Terapkan Pada"
                                    :options="[
                                        0 => 'Semua indikator laporan kinerja',
                                        1 => 'Hanya Indikator ' . $indicator['nama_indikator_laporan_kinerja'],
                                    ]" :value="'all'" :inline="false" required />
                            @endif
                        </div>
                    </div>
                    <div class="modal__footer">
                        <div class="grid cols-1 cols-sm-2">
                            <button class="btn btn_outline" type="button" data-dismiss="modal">
                                Batal
                            </button>
                            <button class="btn btn_primary" type="submit" id="save-dokumen">Salin Data</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @pushOnce('end')
            <x-core::modal.delete id="modal_delete">
                <x-core::form method="PUT" action="{{ $action }}">
                    <input type="hidden" name="iddelete" value="">
                    @foreach ($data['table_key'] as $key => $value)
                        @php
                            $name = "self[$key]";
                        @endphp
                        <x-core::controls.form :name="$name" :type="'hidden'" :value="$value" />
                    @endforeach
                    <x-core::modal.delete-body>
                        <x-slot:button type="submit">
                            Hapus
                        </x-slot:button>
                    </x-core::modal.delete-body>
                </x-core::form>
            </x-core::modal.delete>

            {{--  Modal delete dokumen --}}
            <x-core::modal.delete id="modal_delete_dokumen" title="Hapus Dokumen Pendukung">
                <x-core::form method="DELETE" action="{{ $action }}">
                    <input type="hidden" name="iddelete" value="">
                    @foreach ($data['table_key'] as $key => $value)
                        @php
                            $name = "self[$key]";
                        @endphp
                        <x-core::controls.form :name="$name" :type="'hidden'" :value="$value" />
                    @endforeach
                    <x-core::modal.delete-body message="Apakah Anda yakin ingin menghapus dokumen ini?">
                        <x-slot:button type="submit">
                            Hapus
                        </x-slot:button>
                    </x-core::modal.delete-body>
                </x-core::form>
            </x-core::modal.delete>

            @if (isset($data['indicator']['nomor_indikator']))
                {{-- Modal delete all --}}
                <x-core::modal.delete id="modal_delete_all" title="Hapus Semua Data">
                    <x-core::form method="DELETE" action="{{ $action }}">
                        <input type="hidden" name="iddelete" value="">
                        @foreach ($data['table_key'] as $key => $value)
                            @php
                                $name = "self[$key]";
                            @endphp
                            <x-core::controls.form :name="$name" :type="'hidden'" :value="$value" />
                        @endforeach
                        <x-core::modal.delete-body
                            message="Apakah Anda yakin ingin menghapus semua data pada <b>Tabel {{ $data['indicator']['nomor_indikator'] . ' ' . $data['indicator']['nama_indikator_laporan_kinerja'] }}?</b> Karena data yang telah dihapus tidak dapat dikembalikan lagi.">
                            <x-slot:button type="submit">
                                Hapus
                            </x-slot:button>
                        </x-core::modal.delete-body>
                    </x-core::form>
                </x-core::modal.delete>
            @endif

            <x-core::modal.filling-getdata :iskurikulum="$isGetDataKurikulum" id="modal_filling_getdata"
                message="Apakah Anda yakin tarik data <b>Tabel {{ isset($data['data']['nomor_indikator']) ? $data['data']['nomor_indikator'] . ' ' . $data['data']['nama_indikator_laporan_kinerja'] : 'ini' }}?</b>" />
            @pushOnce('scripts')
                <div class="full-page-loader util_d-none">
                    <div class="loader">
                        <span class="loader__spinner"></span>
                    </div>
                </div>
                @if ($isGetData)
                    <script type="module">
                        List.eventFillingGetData("{{ $action }}");
                    </script>
                @endif
            @endPushOnce
        @endPushOnce
        @pushOnce('scripts')
            <script>
                function validateNegativeDecimalInput(input) {
                    const prev = input.value.toString();

                    let v = prev.replace(/[^\d.,-]/g, "");

                    const isNegative = v.startsWith("-");
                    v = v.replace(/-/g, "");
                    if (isNegative) v = "-" + v;

                    const start = v.startsWith("-") ? 1 : 0;
                    const rest = v.slice(start);

                    const sepPos = rest.search(/[.,]/);
                    if (sepPos !== -1) {
                        const sepIndex = start + sepPos;
                        const before = v.slice(0, sepIndex + 1);

                        let after = v
                        .slice(sepIndex + 1)
                        .replace(/[.,]/g, "");

                        after = after.slice(0, 2);

                        v = before + after;
                    }

                    if (v !== prev) input.value = v;

                    const submitBtn = document.querySelector('button[type="submit"]');
                    submitBtn.addEventListener('click', (e) => {
                        input.value = input.value.replace(',', '.');
                    });
                }

                function validateDecimalInput(input) {
                    const prev = input.value.toString();

                    let v = prev.replace(/[^\d.,]/g, "");

                    const sepMatch = v.match(/[.,]/);
                    if (sepMatch) {
                        const sepIndex = v.indexOf(sepMatch[0]);
                        const sepChar = sepMatch[0];

                        let before = v.slice(0, sepIndex + 1);
                        let after = v
                        .slice(sepIndex + 1)
                        .replace(/[.,]/g, "");

                        after = after.slice(0, 2);

                        v = before + after;
                    }

                    if (v !== prev) input.value = v;

                    const submitBtn = document.querySelector('button[type="submit"]');
                    submitBtn.addEventListener('click', (e) => {
                        input.value = input.value.replace(',', '.');
                    });
                }

                function validateNumericInput(input) {
                    input.value = input.value.replace(/[^0-9.]/g, '');

                    if (input.value < 0) {
                        input.value = '';
                    }

                    if (/^0[0-9]+/.test(input.value)) {
                        input.value = '';
                    }
                }

                function enforceMaxNumber(input, maxValue) {
                    const inputValue = parseFloat(input.value);

                    if (!isNaN(inputValue) && inputValue >= maxValue) {
                        input.value = maxValue;
                    }
                }

                function enforceMinMaxNumber(input, minValue, maxValue) {
                    const inputValue = parseFloat(input.value);

                    if (!isNaN(inputValue) && minValue !== null && inputValue < minValue) {
                        input.value = minValue;
                    } else if (!isNaN(inputValue) && maxValue !== null && inputValue > maxValue) {
                        input.value = maxValue;
                    }
                }

                function handleCheckSingle(input) {
                    const identifier = input.dataset.checkSingle;

                    document.querySelectorAll(`input[data-check-single="${identifier}"]`).forEach((item) => {
                        if (item !== input) {
                            item.checked = false;
                        }
                    });
                }

                function validateNumberInput(input) {
                    if (typeof input.value !== "string") {
                        input.value = input.value.toString();
                    }

                    input.value = input.value.replace(/\./g, "");
                }

                document.addEventListener('livewire:initialized', () => {
                    Livewire.on('show-loading', () => {
                        document.querySelector('.full-page-loader').classList.remove('hidden');
                    });

                    Livewire.on('hide-loading', () => {
                        document.querySelector('.full-page-loader').classList.add('hidden');
                    });
                });

                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.querySelector('#upload-document');
                    const saveDokumen = document.querySelector('#save-dokumen');
                    const inputDokumen = document.querySelector('input[name="dokumen_pendukung"]');
                    const form = document.querySelector('#form_list');

                    saveDokumen.addEventListener('click', () => {
                        if (inputDokumen.files.length === 0) {
                            alert('Silakan pilih file terlebih dahulu.');
                            return;
                        }
                        document.querySelector('input[name="act"]').value = 'upload-dokumen-pendukung';

                        form.submit();
                    });
                });

                @if (!empty($resourceId))
                    function deleteRecordDokumenPendukung(encoded) {
                        List.deleteRecord(encoded, "{{ Page::detailURL($resourceId) . '/delete-dokumen' }}",
                            'modal_delete_dokumen');
                    }

                    function deleteRecordAll(encoded) {
                        List.deleteRecord(encoded, "{{ Page::detailURL($resourceId) . '/delete-all' }}", 'modal_delete_all');
                    }
                @endif

                setTimeout(() => {
                    CKEDITOR.replace("text-editor", {
                        height: 300,
                        extraPlugins: 'justify', // tambahkan plugin justify
                        toolbar: [
                            // { name: "clipboard", items: ["Cut", "Copy", "Paste", "Undo", "Redo"] },
                            // { name: "editing", items: ["Find", "Replace", "SelectAll"] },
                            {
                                name: "basicstyles",
                                items: ["Bold", "Italic", "Underline", "Strike"]
                            },
                            {
                                name: "paragraph",
                                items: [
                                    "NumberedList",
                                    "BulletedList",
                                    "-",
                                    "Outdent",
                                    "Indent",
                                    "-",
                                    "JustifyLeft",
                                    "JustifyCenter",
                                    "JustifyRight",
                                    "JustifyBlock",
                                    "-",
                                    "Blockquote"
                                ]
                            },
                            {
                                name: "insert",
                                items: ["Table"]
                            },
                            // { name: "tools", items: ["Maximize", "ShowBlocks"] }
                        ]
                    });

                }, 100);
            </script>
        @endPushOnce

        @pushOnce('headVendor')
            <style>
                .cke_notifications_area {
                    display: none !important;
                }
            </style>
            <link rel="stylesheet"
                href="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/styles/choices.min.css') }}">
            <script src="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/scripts/choices.min.js') }}"></script>
            <script src="https://cdn.ckeditor.com/4.16.0/full-all/ckeditor.js"></script>
        @endPushOnce

        @pushOnce('scripts')
            <script type="module">
                // Lazy Select Handler Class
                class LazySelectHandler {
                    constructor() {
                        this.choicesInstances = {};
                        this.debounceTimers = {};
                    }

                    initLazySelect(selectElement, apiUrl, currentValue = null) {
                        const elementId = selectElement.id || `lazy-select-${Math.random().toString(36).substr(2, 9)}`;
                        selectElement.id = elementId;

                        const choicesInstance = new Choices(selectElement, {
                            allowHTML: false,
                            shouldSort: false,
                            searchEnabled: true,
                            removeItemButton: false,
                            searchResultLimit: 20,
                            searchFields: ['label'],
                            placeholder: true,
                            searchPlaceholderValue: 'Ketik untuk mencari...',
                            noResultsText: 'Tidak ada hasil ditemukan',
                            noChoicesText: 'Ketik untuk mencari',
                            // itemSelectText: 'Tekan untuk memilih',
                        });

                        this.choicesInstances[elementId] = choicesInstance;

                        if (currentValue) {
                            this.loadInitialValue(choicesInstance, apiUrl, currentValue);
                        } else {
                            this.loadOptions(choicesInstance, apiUrl, '');
                        }

                        selectElement.addEventListener('search', (event) => {
                            const searchTerm = event.detail.value;

                            if (this.debounceTimers[elementId]) {
                                clearTimeout(this.debounceTimers[elementId]);
                            }

                            this.debounceTimers[elementId] = setTimeout(() => {
                                this.loadOptions(choicesInstance, apiUrl, searchTerm);
                            }, 300);
                        });

                        selectElement.addEventListener('choice', () => {
                            if (this.debounceTimers[elementId]) {
                                clearTimeout(this.debounceTimers[elementId]);
                            }
                        });
                    }

                    async loadOptions(choicesInstance, apiUrl, searchTerm) {
                        try {
                            const url = new URL(apiUrl, window.location.origin);
                            url.searchParams.append('search', searchTerm);
                            url.searchParams.append('limit', 20);

                            const response = await fetch(url, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            });

                            if (!response.ok) throw new Error('Network response was not ok');

                            const data = await response.json();
                            choicesInstance.clearChoices();
                            choicesInstance.setChoices(data, 'value', 'label', true);

                        } catch (error) {
                            console.error('Error loading options:', error);
                            choicesInstance.clearChoices();
                            choicesInstance.setChoices([{
                                value: '',
                                label: 'Error loading data',
                                disabled: true
                            }], 'value', 'label', true);
                        }
                    }

                    async loadInitialValue(choicesInstance, apiUrl, value) {
                        try {
                            let allChoices = [];

                            const valueUrl = apiUrl.replace('/search-dosen', `/dosen-option/${value}`);

                            try {
                                const valueResponse = await fetch(valueUrl, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    }
                                });

                                if (valueResponse.ok) {
                                    const currentOption = await valueResponse.json();
                                    allChoices.push(currentOption);
                                }
                            } catch (error) {
                                console.error('Error loading specific value:', error);
                            }

                            const url = new URL(apiUrl, window.location.origin);
                            url.searchParams.append('search', '');
                            url.searchParams.append('limit', 20);

                            try {
                                const response = await fetch(url, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    }
                                });

                                if (response.ok) {
                                    const data = await response.json();

                                    const filteredData = data.filter(item => item.value != value);
                                    allChoices = allChoices.concat(filteredData);
                                }
                            } catch (error) {
                                console.error('Error loading initial options:', error);
                            }

                            if (allChoices.length > 0) {
                                choicesInstance.setChoices(allChoices, 'value', 'label', true);
                                choicesInstance.setChoiceByValue(value.toString());
                            }

                        } catch (error) {
                            console.error('Error loading initial value:', error);
                        }
                    }

                    destroy(elementId) {
                        if (this.choicesInstances[elementId]) {
                            this.choicesInstances[elementId].destroy();
                            delete this.choicesInstances[elementId];
                        }
                        if (this.debounceTimers[elementId]) {
                            clearTimeout(this.debounceTimers[elementId]);
                            delete this.debounceTimers[elementId];
                        }
                    }
                }

                const lazySelectHandler = new LazySelectHandler();

                function initializeLazySelects() {
                    document.querySelectorAll('.lazy-select:not(.initialized)').forEach(selectElement => {
                        const apiUrl = selectElement.dataset.lazyApi;
                        const currentValue = selectElement.dataset.currentValue;

                        if (apiUrl) {
                            selectElement.classList.add('initialized');
                            lazySelectHandler.initLazySelect(selectElement, apiUrl, currentValue || null);
                        }
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeLazySelects);
                } else {
                    initializeLazySelects();
                }

                const observer = new MutationObserver((mutations) => {
                    let hasNewLazySelects = false;
                    mutations.forEach((mutation) => {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1) {
                                const lazySelects = node.querySelectorAll ? node.querySelectorAll('.lazy-select:not(.initialized)') : [];
                                if (lazySelects.length > 0) {
                                    hasNewLazySelects = true;
                                }
                            }
                        });
                    });

                    if (hasNewLazySelects) {
                        setTimeout(initializeLazySelects, 100);
                    }
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            </script>
        @endPushOnce
</x-core::layouts.outer>
