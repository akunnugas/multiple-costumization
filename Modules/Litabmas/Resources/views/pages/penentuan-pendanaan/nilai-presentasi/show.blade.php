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

    // handle $data
    $primaryData ??= $data['primary-section']['items'];
    unset($data['primary-section']);

    // get status_penentuan_pendanaan from field on array $primaryData
    $statusPenentuanPendanaan =
        collect($primaryData)->firstWhere('field', 'status_penentuan_pendanaan')['original'] ?? null;

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
    $disableButton = '';
    if ($sudahMasaPenentuanPendanaan) {
        $disableButton = 'disabled';
    }

    $detailPresentasiJadwal = $detailPresentasi['jadwal_presentasi_proposal'];

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
        @if ($statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN)
            <x-core::button size="sm" variant="primary" leading-icon="check-badge" disabled="true">
                Lolos Pendanaan
            </x-core::button>
        @endif
    </x-slot:action>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData" />
        </div>
    </div>

    @if ($detailPresentasiJadwal)

        <x-litabmas::layouts.detail.card title="Jadwal Presentasi Proposal"
            customClassBody="util_d-flex util_flex-column util_gap-1">
            <div class="grid">
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Nama Kegiatan</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        {{ $detailPresentasiJadwal['nama_kegiatan'] }}
                    </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Status</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        <x-litabmas::fields.status_presentasi_proposal :data="$detailPresentasiJadwal" />
                    </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Tipe Kegiatan</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        {{ ucfirst($detailPresentasiJadwal['tipe_kegiatan']) }}
                    </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Waktu Pelaksanaan</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        {{ Date::formatDateTimeLong($detailPresentasiJadwal['waktu_pelaksanaan']) }}
                    </span>
                </div>
                @if ($detailPresentasiJadwal['tipe_kegiatan'] == 'offline')
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Tempat</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                            {{ $detailPresentasiJadwal['tempat_pelaksanaan'] }}
                        </span>
                    </div>
                @else
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Link Kegiatan</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                            {{ $detailPresentasiJadwal['link_presentasi_kegiatan'] }}
                        </span>
                    </div>
                @endif
            </div>
        </x-litabmas::layouts.detail.card>

        <x-litabmas::layouts.detail.card title="Penilaian Presentasi Proposal"
            customClassBody="util_d-flex util_flex-column util_gap-1">
            <x-litabmas::pages.penentuan-pendanaan.nilai-presentasi.tabel-nilai-presentasi-section :nilai="$rekapPresentasi"
                :reviewer="$reviewer" />
        </x-litabmas::layouts.detail.card>
    @else
        {{-- alert --}}
        <x-core::layouts.html.alert :data="[
            'type' => 'warning',
            'message' =>
                'Proposal ini tidak memerlukan presentasi. Jadwal presentasi tidak tersedia.',
        ]" />
    @endif

    @if (!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert" />
    @endif

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
