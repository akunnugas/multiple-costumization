@php
    use Modules\SPMI\Models\PenilaianMatriks;
    use Modules\DMS\Helpers\DokumenHelper;

    // default title
    $title = 'Pengisian Indikator Evaluasi Diri';
    if (empty($subtitle) && !empty($title)) {
        $subtitle = $title;
    }

    $indicator = $data['data'];

    $isCreate = empty($resourceId);

    if ($isCreate) {
        $method = 'POST';
        $action = Page::indexURL();
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
    }

    $isEdit = false;

    $urlReports = route('spmi.reports.filling-self-reports.generate');

    if (empty($_GET['is_edit'])) {
        $isEdit = false;
        // set url to edit
        $urlChange = Page::buildURL(['is_edit' => '1']);
    } else {
        $isEdit = true;
        // delete edit parameter
        $urlChange = Page::buildURL(['is_edit' => null]);
    }

    $isDefaultView = false;
    if (empty($_GET['id_indikator_evaluasi_diri'])) {
        $isDefaultView = true;
    }
@endphp

<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/pengisian-indikator-led/create.scss')
        @vite('Modules/SPMI/Resources/assets/js/pengisian-indikator-led/create.js')
    @endpush

    <x-core::form :$method :$action id="form_list">
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
                            @if (!$isDefaultView)
                                <a class="btn btn_outline btn_xs"
                                    href="{{ route('spmi.pengisian-indikator-led.index') }}">
                                    Kembali ke List
                                </a>
                                @if ($data['apakah_bisa_aksi'])
                                    <a onclick="List.showReport('{{ $urlReports }}');"
                                        class="btn btn_outline btn_xs report-button">
                                        <i class="icon icon-printer"></i>
                                        Laporan
                                    </a>
                                    <button type="button" class="btn btn_outline btn_xs report-button"
                                        id="btn_filling_getdata" data-toggle="modal" data-target="#salin-data">
                                        <i class="icon icon-clipboard-document"></i> Salin Data
                                    </button>
                                    @if ($data['apakah_tanggal_pengisian_valid'])
                                        @if (!$isEdit)
                                            <a href="{{ $urlChange }}" class="btn btn_primary btn_xs">
                                                Ubah Data
                                            </a>
                                        @else
                                            <a href="{{ $urlChange }}">
                                                <button type="button" class="btn btn_outline btn_xs">
                                                    Batal
                                                </button>
                                            </a>
                                            <button type="submit"
                                                onclick="document.getElementById('uraian_input').value = CKEDITOR.instances['text-editor'].getData();"
                                                class="btn btn_primary btn_xs">
                                                Simpan
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card__body">
                    <div class="sidebar-new">
                        <div class="title-sidebar">
                            <h2>{{ $title }}</h2>
                        </div>

                        <div class="body-sidebar tree">
                            <ul>
                                {{-- <li>
                                    <a href="{{ $data['link_proposing_team'] }}" style="padding-left: 0px">
                                        <div class="indicator-vector"></div>
                                        <div class="number"></div>
                                        <div class="title">Identitas Pengusul</div>
                                    </a>
                                </li> --}}
                                {!! $data['sidebar'] !!}
                            </ul>
                        </div>
                    </div>
                    <div class="content">
                        <div class="title">
                            @if (empty($data['data']['nama_indikator_evaluasi_diri']))
                                <h2>{{ @$data['data']['nomor_indikator'] . ' ' . @$data['data']['name'] }}</h2>
                            @else
                                @php
                                    $name = $data['data']['nama_indikator_evaluasi_diri'] ?? '';
                                    $nomor = $data['data']['nomor_indikator'] ?? '';
                                    $kode  = $data['pengisian_panduan']['kode_pengisian_panduan'] ?? '';

                                    if (!in_array($kode, ['IAPS5.1', 'LED5.1'])) {
                                        $parts = explode('. ', $name, 2);
                                        $name = isset($parts[1]) ? "$nomor. {$parts[1]}" : $parts[0];
                                    } else {
                                        $name = "$nomor. $name";
                                    }
                                @endphp
                                <h2>{{ $name }}</h2>
                            @endif
                        </div>
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
                            @if ($isDefaultView)
                            @else
                                @if (!empty($data['data']['deskripsi']))
                                    <div class="panel-group">
                                        <div class="panel panel-info">
                                            <div class="panel-heading" data-target="#panel-keterangan">
                                                <div class="">
                                                    <i class="icon icon-information-circle-solid"></i>
                                                    Keterangan
                                                    <i class="icon icon-chevron-down-solid right indicator-icon"></i>
                                                </div>
                                            </div>
                                            <div id="panel-keterangan" class="panel-collapse">
                                                <div class="panel-body">
                                                    {!! $data['data']['deskripsi'] !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                {!! $data['table'] !!}

                                @if (!empty($data['data']['information']))
                                    <div class="notes">
                                        <h4>Catatan:</h4>
                                        <p>{!! $data['data']['information'] !!}</p>
                                    </div>
                                @endif

                                @if (!empty($indicator['is_comment']))
                                    <table class="table table-bordered table-hover table-striped pt-12">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Komentar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    @if ($isEdit)
                                                        <div class="form-control">
                                                            <div class="form-control__group">
                                                                <textarea class="form-control__input textarea" name="komentar" column="5" placeholder="Komentar">
                                                        @if (!empty($data['records']['komentar']))
{{ $data['records']['komentar'] }}
@endif
                                                        </textarea>
                                                            </div>
                                                        </div>
                                                    @else
                                                        @if (!empty($data['records']['komentar']))
                                                            {{ $data['records']['komentar'] }}
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif

                                @if (!empty($indicator['is_key_point']))
                                    <table class="table table-bordered table-hover table-striped pt-12 tb-poin">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th class="text-center">Poin Kunci</th>
                                                @if ($isEdit)
                                                    <th class="text-center">
                                                        <button type="button" id="btn_add"
                                                            class="btn btn_icon btn_link btn_sm btn_action">
                                                            <span class="icon icon-plus-circle-solid"></span>
                                                        </button>
                                                    </th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="template-form-keypoint">
                                                <td></td>
                                                <td>
                                                    @if ($isEdit)
                                                        <div class="form-control">
                                                            <div class="form-control__group">
                                                                <x-core::input name="key_points[]" />
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn_outline btn_xs m-auto btn_delete" style="">
                                                        <span class="icon icon-x-mark-solid btn_delete"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                            @if (!empty($data['records']['key_points']))
                                                @foreach ($data['records']['key_points'] as $key => $value)
                                                    <tr>
                                                        <td class="text-center">{{ $key + 1 }}</td>
                                                        @if ($isEdit)
                                                            <td class="text-center">
                                                                <div class="form-control">
                                                                    <div class="form-control__group">
                                                                        <x-core::input name="key_points[]"
                                                                            :value="$value" />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn_outline btn_xs m-auto btn_delete"
                                                                    style="">
                                                                    <span
                                                                        class="icon icon-x-mark-solid btn_delete"></span>
                                                                </button>
                                                            </td>
                                                        @else
                                                            <td class="text-center">{{ $value }}</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                @endif

                                <div class="documents">
                                    <div class="header-document">
                                        <h3>Dokumen Pendukung</h3>
                                        @if ($data['apakah_bisa_aksi'] && $data['apakah_tanggal_pengisian_valid'])
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
                                                    <td colspan="100" class="cell-center">Tidak ada data</td>
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
                                                            <x-core::button leading-icon="trash-solid"
                                                                variant="outline" size="xs"
                                                                href="javascript:deleteRecordDokumenPendukung('{{ $encoded }}')"
                                                                data-btn-label="Hapus" />
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#"
                                                                        class="btn btn_outline btn_xs btn_icon">
                                                                        <span
                                                                            class="icon icon-ellipsis-horizontal"></span>
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($data['raw'] as $key => $value)
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
    </x-core::form>

    <div id="salin-data" class="modal">
        <div class="modal__overlay" data-dismiss="modal"></div>

        <div class="modal__wrapper">
            <div class="modal__header">
                <div class="modal__header-wrapper">
                    <h3 class="modal__title">Salin Data Evaluasi Diri</h3>
                </div>
                <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
            </div>

            <form method="POST" action="{{ route('spmi.pengisian-indikator-led.copy') }}">
                @csrf
                <div class="modal__body">
                    <span>Anda dapat menyalin data dari periode sebelumnya agar tidak perlu mengisi ulang secara manual.
                        Silakan pilih periode asal dan tentukan cakupan indikator yang ingin disalin.</span>
                    <div style="margin-top: 1rem;">
                        @php
                            $raw = $data['raw'];
                        @endphp
                        <x-core::input type="hidden" name="id_audit_periode"
                            value="{{ $raw['id_audit_periode'] }}" />
                        <x-core::input type="hidden" name="route_name" value="{{ Route::currentRouteName() }}" />
                        <x-core::input type="hidden" name="id_unit" value="{{ $raw['id_unit'] }}" />
                        <x-core::input type="hidden" name="id_pengisian_panduan"
                            value="{{ $raw['id_pengisian_panduan'] }}" />
                        <x-core::input type="hidden" name="id_jadwal_audit"
                            value="{{ $raw['id_jadwal_audit'] ?? '' }}" />
                        <x-core::input type="hidden" name="id_indikator_evaluasi_diri"
                            value="{{ $indicator['id'] ?? null }}" />
                        <x-core::controls.form name="active_periode" purpose="form" id="active_periode"
                            :value="$indicator['audit_period_label'] ?? ''" label="Periode Aktif" control="input" disabled required />
                        <x-core::controls.form name="previous_source" purpose="form" id="previous_source"
                            :options="$indicator['previous_year_audit'] ?? []" label="Salin dari Kegiatan AMI" control="select" variant="search"
                            data-search-placeholder="Pilih Kegiatan AMI"
                            placeholder="{{ !empty($indicator['previous_year_audit']) ? 'Pilih Kegiatan AMI' : 'Tidak ada data di periode sebelumnya' }}"
                            :disabled="empty($indicator['previous_year_audit'])" required />

                        @if (!empty($indicator['previous_year_audit']) && isset($indicator['nama_indikator_evaluasi_diri']))
                            <x-core::controls.form control="radio" name="scope" label="Terapkan Pada"
                                :options="[
                                    0 => 'Semua indikator evaluasi diri',
                                    1 => 'Hanya Indikator '. $indicator['nama_indikator_evaluasi_diri'],
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
                @foreach ($data['raw'] as $key => $value)
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
                @foreach ($data['raw'] as $key => $value)
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
        {{-- Versi lama hanya tersedia via CDN saat ini, tidak ada binary yang bisa di download --}}
        <script src="https://cdn.ckeditor.com/4.16.0/full-all/ckeditor.js"></script>
        <script src="{{ asset('js/image-resize.min.js') }}"></script>
    @endPushOnce

    @push('scripts')
        <script>
            setTimeout(() => {
                CKEDITOR.replace("text-editor", {
                    height: 300,
                    extraPlugins: 'justify', // tambahkan plugin justify
                    toolbar: [{
                            name: "clipboard",
                            items: ["Cut", "Copy", "Paste", "Undo", "Redo"]
                        },
                        {
                            name: "editing",
                            items: ["Find", "Replace", "SelectAll"]
                        },
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
                            items: ["Image", "Table", "HorizontalRule", "SpecialChar"]
                        },
                        {
                            name: "tools",
                            items: ["Maximize", "ShowBlocks"]
                        }
                    ]
                });

            }, 100);
        </script>

        <script>
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
            @endif
        </script>
    @endpush
</x-core::layouts.outer>
