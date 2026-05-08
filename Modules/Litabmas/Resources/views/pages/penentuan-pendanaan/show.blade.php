@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    use Modules\Core\Helpers\Date;
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
    use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $dokumen = $rawData['dokumen_sk_peneliti'];

    // handle $data
    $primaryData ??= $data['primary-section']['items'];
    unset($data['primary-section']);

    $modalData = $data['modal-section']['items'];
    unset($data['modal-section']);

    // get status_penentuan_pendanaan from field on array $primaryData
    $statusPenentuanPendanaan =
        collect($primaryData)->firstWhere('field', 'status_penentuan_pendanaan')['original'] ?? null;

    $nilaiRataRata = collect($modalData)->firstWhere('field', 'nilai_reviewer')['original'] ?? null;
    $nilaiRataRataSum = collect($nilaiRataRata)->sum(function ($nilai) {
        return $nilai->total_nilai;
    });
    $countReviewer = count($nilaiRataRata);
    $nilaiRataRataAvg = $countReviewer > 0 ? $nilaiRataRataSum / $countReviewer : 0;

    // $resourceId dari Page::buildViewData()
    if (!empty($resourceId)) {
        $action = route('litabmas.penentuan-pendanaan.status-tidak-lolos-pendanaan', $resourceId);
        $method = 'PUT';
    }

    $tanggalReview = Date::formatDateRange(
        $infoReviewAdministrasi['waktu_mulai'],
        $infoReviewAdministrasi['waktu_selesai'],
        isoFormatMonth: 'MMMM',
    );
    $sudahMasaPenentuanPendanaan = !empty($infoReviewAdministrasi['sudah_masuk_masa_penentuan_pendanaan']);
    $disableButton = false;
    if (!$sudahMasaPenentuanPendanaan) {
        if (now() < $infoReviewAdministrasi['waktu_mulai']) {
            $disableButton = true;
            $staticAlert = [
                'message' =>
                    'Penentuan pendanaan belum bisa dilakukan. Pastikan review dan penilaian proposal oleh reviewer sudah selesai terlebih dahulu.',
                'type' => 'warning',
                'dismissible' => false,
            ];
        }
        if (now() > $infoReviewAdministrasi['waktu_selesai']) {
            $disableButton = true;
            $staticAlert = [
                'message' =>
                    'Batas waktu penentuan pendanaan sudah terlewati. Anda tidak dapat menentukan pendanaan terpilih saat ini.',
                'type' => 'warning',
                'dismissible' => false,
            ];
        }
    }

    if ($infoReviewAdministrasi['apakah_wajib_presentasi']) {
        foreach ($nilaiRataRata as $reviewer => $value) {
            if ($value->total_nilai_presentasi_reviewer == null) {
                $disableButton = true;
                $staticAlert = [
                    'message' => 'Beberapa reviewer belum menyelesaikan penilaian proposal.',
                    'type' => 'warning',
                    'dismissible' => false,
                ];
            }
        }
    }
    $disableUploadSK = true;
    if ($statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN) {
        $disableUploadSK = false;
    }
    if (!empty($dokumen['id'])) {
        $disableUploadSK = true;
    }
@endphp

@pushonce('head')
    @vite('resources/scss/custom-utils.scss')
@endpushonce
@pushOnce('headVendor')
    <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
    <script src="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.min.js') }}"></script>
@endPushOnce

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>

    <x-core::layouts.html.alert />
    <x-slot:action>
        @if (!$statusPenentuanPendanaan)
            <div class="util_d-flex" style="gap: 1rem">
                {{-- <x-core::form :method="'PUT'" :action="route('litabmas.penentuan-pendanaan.status-tidak-lolos-pendanaan', $resourceId)"> --}}
                <x-core::button type="submit" size="sm" variant="outline" data-toggle="modal"
                    data-target="#modal-penentuan-pendanaan-tidak-lolos" :disabled=$disableButton>
                    Tidak Lolos Pendanaan
                </x-core::button>
                {{-- </x-core::form> --}}
                {{-- <x-core::form :method="'PUT'" :action="route('litabmas.penentuan-pendanaan.status-lolos-pendanaan', $resourceId)"> --}}
                <x-core::button type="submit" size="sm" variant="primary" data-toggle="modal"
                    data-target="#modal-status-lolos-penentuan" :disabled=$disableButton>
                    Lolos Pendanaan
                </x-core::button>
                {{-- </x-core::form> --}}
            </div>
        @endif
        @if ($statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN)
            @if ($sudahMasaPenentuanPendanaan)
                <x-core::form :method="'PUT'" :action="route('litabmas.penentuan-pendanaan.batalkan-status-pendanaan', $resourceId)">
                    <x-core::button type="submit" size="sm" variant="outline">
                        Batalkan Status Pendanaan
                    </x-core::button>
                </x-core::form>
            @endif
            <x-core::button size="sm" variant="primary" leading-icon="check-badge" disabled="true">
                Lolos Pendanaan
            </x-core::button>
        @endif
        @if ($statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN)
            @if ($sudahMasaPenentuanPendanaan)
                <x-core::form :method="'PUT'" :action="route('litabmas.penentuan-pendanaan.batalkan-status-pendanaan', $resourceId)">
                    <x-core::button type="submit" size="sm" variant="outline">
                        Batalkan Status Pendanaan
                    </x-core::button>
                </x-core::form>
            @endif
            <x-core::button size="sm" variant="destructive" disabled="true" leading>
                Tidak Lolos Pendanaan
            </x-core::button>
        @endif
    </x-slot:action>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData" />
        </div>
    </div>

    @if (!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert" />
    @endif

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1">

        {{-- Info Detail --}}
        @php
            $section = $data['informasi-umum'];
            $attributes = Page::buildAttributes(
                Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']],
            );
        @endphp
        <x-litabmas::layouts.detail.card {{ $attributes }} />

        {{-- Detail Pendanaan --}}
        @php
            $section = $data['penilaian-reviewer'];
            $attributes = Page::buildAttributes(
                Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']],
            );
        @endphp
        <x-litabmas::layouts.detail.card {{ $attributes }} />


        {{-- Data Peneliti --}}
        <x-litabmas::layouts.detail.card title="Data Peneliti"
            subtitle="Detail tim yang terlibat dalam penelitian Anda">
            @php
                $noDosen = 0;
                $dosenMembers = [];
            @endphp
            @foreach ($dataPenelitiDosen as $item)
                @php
                    $isKetua = $item->apakah_ketua;
                    $rowTitle = $isKetua ? 'Ketua Penelitian' : 'Anggota Dosen';
                @endphp

                @if ($isKetua)
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">{{ $rowTitle }}</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__value">
                            <span class="row-data__colon">:</span>
                            {{ $item->nama_user }}
                        </span>
                    </div>
                @else
                    @php
                        $dosenMembers[] = $item->nama_user;
                    @endphp
                @endif
            @endforeach

            @if (!empty($dosenMembers))
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Anggota Dosen</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value">
                        @foreach ($dosenMembers as $member)
                            {{ $member }}<br>
                        @endforeach
                    </span>
                </div>
            @endif

            @if ($dataPenelitiMahasiswa->isNotEmpty())
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Anggota Mahasiswa</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value">
                        @foreach ($dataPenelitiMahasiswa as $member)
                            {{ $member->nama_user }}<br>
                        @endforeach
                    </span>
                </div>
            @endif
        </x-litabmas::layouts.detail.card>

    </x-litabmas::layouts.detail.card>

    <div class="card card_details-default">
        <div class="card__header">
            <div class="card__header-left">
                <div class="card__header-block">
                    <h2 class="header__title">Dokumen Surat Keterangan</h2>
                    <span class="header__subtitle">
                        Silahkan Unggah berkas dokumen SK jika telah menyatakan lolos pendanaan.
                    </span>
                </div>
            </div>
            <div class="card__header-right">
                <div class="util_d-flex">
                    <x-core::button size="sm" leading-icon="arrow-up-tray" data-toggle="modal"
                        data-target="#modal-upload-sk" disabled="{{ $disableUploadSK }}">
                        Unggah SK
                    </x-core::button>
                </div>
            </div>
        </div>
        @if (!empty($dokumen['id']))
            <x-core::table>
                <div class="box-table">
                    <div class="table-max table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Dokumen</th>
                                    <th>Terakhir Diubah</th>
                                    <th class="cell-action cell-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="cell-inline">
                                        <img src="{{ $dokumen['asset_url'] }}" width="24" height="24"
                                            alt="">
                                        {{ $dokumen['nama_dokumen'] }}.{{ $dokumen['extension_versi_terbaru'] }}
                                    </td>
                                    <td>{{ $dokumen['terakhir_diubah'] }}</td>
                                    <td class="cell-action cell-center">
                                        <div class="main__action" style="float: inline-end;">
                                            <x-core::button leading-icon="pencil" variant="outline" size="xs"
                                                data-toggle="modal" data-target="#modal-upload-sk" />
                                            <x-core::button leading-icon="trash" variant="outline" size="xs"
                                                data-toggle="modal" data-target="#modal-destroy-sk" />
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </x-core::table>
        @endif

    </div>

    {{-- Modal --}}
    <x-litabmas::pages.penentuan-pendanaan.modal-show-page :idPengajuanPendanaan="$resourceId" :statusPendanaan="$statusPenentuanPendanaan" />
    <x-litabmas::pages.penentuan-pendanaan.modal-upload-sk-show-page :idPengajuanPendanaan="$resourceId" :data="$modalData"
        :dokumen="$dokumen" />

    <x-core::modal title="Apakah Anda yakin menyatakan lolos pendaanan untuk Proposal ini?" variant="primary"
        id="modal-status-lolos-penentuan" width="600px">
        <x-core::form :method="'PUT'" :action="route('litabmas.penentuan-pendanaan.status-lolos-pendanaan', $resourceId)">
            <x-core::modal.body>
                <div class="card card-modal-penentuan-pendanaan">
                    <div class="grid">
                        <x-litabmas::layouts.detail.line :data="$modalData" />
                    </div>
                </div>
                <div class="section-inline-form">
                    <div class="util_w-100">
                        <div class="section-inline-form__label" style="margin: 0.7rem 0 0.3rem 0">
                            <span>
                                Masukan biaya disetujui
                            </span>
                        </div>
                        <x-core::controls.input placeholder="Masukan biaya yang disetujui" type="number" Masukan biaya
                            disetujui name="biaya_disetujui" required :control="'currency'" />
                    </div>
                </div>
                @if ($nilaiRataRataAvg <= AspekPenilaianPresentasiProposal::NILAI_MEMENUHI_KRITERIA)
                    <div class="section-inline-form__label" style="margin: 0.7rem 0 0.3rem 0">
                        <span>
                            Apakah anda ingin meloloskan proposal ini dengan hak afirmasi?
                        </span>
                    </div>
                    <div class="util_w-100">
                        <div class="form-control">
                            <div class="form-control__group">
                                <x-core::checkbox id="afirmasi" value="true" label="Iya, Gunakan afirmasi"
                                    name="is_afirmasi" />
                            </div>
                        </div>
                    </div>
                    <x-core::alert variant="warning" :dismissable="false" class="util_mt-16">
                        Nilai proposal ini di bawah batas minimal untuk lolos pendanaan. Menggunakan hak afirmasi
                        dapat
                        mempengaruhi pendanaan proposal lain yang memenuhi syarat.
                    </x-core::alert>
                @endif
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batalkan
                        </x-core::button>

                        <x-core::button variant="primary" type="submit">
                            Ya, Yakin
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>


    <x-core::modal title="Apakah Anda yakin menyatakan lolos pendaanan untuk Proposal ini?" variant="primary"
        id="modal-upload-sk" width="600px">
        <x-core::form :method="'PUT'" :action="route('litabmas.penentuan-pendanaan.status-lolos-pendanaan', $resourceId)">
            <x-core::modal.body>
                <div class="card card-modal-penentuan-pendanaan">
                    <div class="grid">
                        <x-litabmas::layouts.detail.line :data="$modalData" />
                    </div>
                </div>
                <div class="section-inline-form">
                    <div class="section-inline-form__label" style="margin: 0.7rem 0 0.3rem 0">
                        <span>
                            Masukan biaya disetujui
                        </span>
                    </div>
                    <div class="util_w-100">
                        <div class="form-control">
                            <div class="form-control__group">
                                <x-core::input placeholder="Masukan biaya yang disetujui" type="number"
                                    name="biaya_disetujui" required />
                            </div>
                        </div>
                    </div>
                </div>
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batalkan
                        </x-core::button>

                        <x-core::button variant="primary" type="submit">
                            Ya, Yakin
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>

    @pushonce('head')
        <style>
            .card-modal-penentuan-pendanaan {
                background-color: #EBF4FF;
                color: #36383A;
            }

            .card-modal-penentuan-pendanaan .col-sm-4,
            .card-modal-penentuan-pendanaan .col-md-3,
            .card-modal-penentuan-pendanaan .col-lg-3 {
                grid-column: span 6;
            }

            .card-modal-penentuan-pendanaan .col-sm-8,
            .card-modal-penentuan-pendanaan .col-md-9,
            .card-modal-penentuan-pendanaan .col-lg-9 {
                grid-column: span 6;
            }

            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .card .row-data__value {
                width: unset;
            }

            .row-data__title {
                font-size: 0.75rem;
                font-weight: 600;
                line-height: 1.125rem;
                display: inline-flex;
                padding-right: 0.75rem;
            }

            .badge.badge_sm {
                display: inline-flex;
            }

            .cell-inline {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            @media (max-width: 768px) {
                .card .row-data__colon {
                    display: none;
                }

                .card .card__header .card__header-left {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .btn.btn_outline#btn-edit-desktop {
                    display: none;
                }
            }

            @media (min-width: 768px) {
                .card .row-data__colon {
                    display: inline-flex !important;
                }

                .btn.btn_outline#btn-edit-mobile {
                    display: none;
                }
            }

            .box-table__content#table-docs {
                border-top: none;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
