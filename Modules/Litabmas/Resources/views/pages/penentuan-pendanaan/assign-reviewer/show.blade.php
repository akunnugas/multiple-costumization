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
    use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;

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

    $bisaUpdateReviewer = $statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN;

    $isMaxReviewer =
        $reviewers->count() >= \Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan::MAX_REVIEWER_KEGIATAN;

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

    $staticAlert = null;
    if (!$bisaUpdateReviewer) {
        $staticAlert = [
            'message' => 'Tidak dapat menambahkan reviewer. Status proposal saat ini belum ditentukan.',
            'type' => 'warning',
            'dismissible' => false,
        ];
        if ($statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN) {
            $staticAlert = [
                'message' =>
                    'Anda tidak dapat menambahkan reviewer karena status proposal tidak lolos penentuan pendanaan.',
                'type' => 'danger',
                'dismissible' => false,
            ];
        }
    } elseif ($isMaxReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak dapat menambahkan reviewer karena jumlah reviewer sudah mencapai batas maksimal.',
            'type' => 'helper',
            'dismissible' => false,
        ];
    } else {
        $staticAlert = [
            'message' => 'Setelah proposal ini lolos penentuan pendanaan,
                Anda dapat menambahkan reviewer.',
            'type' => 'helper',
            'dismissible' => false,
        ];
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

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData" />
        </div>
    </div>

    @if (!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert" />
    @endif

    <x-litabmas::layouts.detail.card title="Tambahkan Reviewer" customClassBody="util_d-flex util_flex-column util_gap-1"
        subtitle="Tambahkan reviewer untuk penilaian proposal setelah status proposal ditentukan.">
        @if ($bisaUpdateReviewer && !$isMaxReviewer)
            <x-slot:action>
                <div class="util_d-flex">
                    <x-core::button href="#" size="sm" data-toggle="modal"
                        data-target="#modal-tambah-reviewer">
                        Tambahkan Reviewer
                    </x-core::button>
                </div>
            </x-slot:action>
        @endif

        <div class="box-table__content">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Reviewer</th>
                            <th>Bertugas Sebagai</th>
                            <th>Dokumen SK Reviewer</th>
                            <th class="cell-action cell-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($reviewers as $reviewer)
                            @php
                                $namaLengkap = $reviewer['nip'] . ' - ' . $reviewer['nama'];
                                $encoded = base64_encode(
                                    json_encode([
                                        'id' => $reviewer['id'],
                                        'id_biodata' => $reviewer['id_biodata'],
                                        'text' => $namaLengkap,
                                        'tipe_reviewer' => $reviewer['tipe_reviewer'],
                                    ]),
                                );
                            @endphp
                            <tr>
                                <td width="10">{{ $no++ }}.</td>
                                <td>{{ $namaLengkap }}</td>
                                <td>{{ PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_OPTIONS[$reviewer['tipe_reviewer']] }}
                                </td>
                                <td class="util_d-flex util_flex-center-vertical">
                                    @if ($reviewer['id_dokumen_sk'])
                                        <img height="20px;" src="{{ $reviewer->asset_url }}"
                                            alt="Dokumen Reviewer {{ $namaLengkap }}">
                                        &nbsp; {{ $reviewer->nama_dokumen }}
                                    @endif
                                </td>
                                <td class="cell-action">
                                    <div class="dropdown-group">
                                        @if ($bisaUpdateReviewer)
                                            <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                href="javascript:showModal('{{ $encoded }}')"
                                                data-btn-label="Edit Reviewer" />
                                            <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                                                href="javascript:deleteRecord('{{ $encoded }}')"
                                                data-btn-label="Hapus Reviewer" />
                                        @else
                                            <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                disabled />
                                            <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                                                disabled />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($reviewers->isEmpty())
                    @php
                        $emptyTitle = 'Belum ada data reviewer.';
                        $emptySubtitle = 'Silakan tambahkan data reviewer dengan cara klik tombol tambahkan rembimbing';
                        if ($staticAlert) {
                            $emptySubtitle = 'Data reviewer akan ditampilkan setelah Anda menambahkan reviewer.';
                        }
                    @endphp
                    <x-core::handler title="{!! $emptyTitle !!}" subtitle="{!! $emptySubtitle !!}"
                        :canCreate="false" />
                @endif
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @if ($bisaUpdateReviewer)
        {{-- Modal --}}
        <x-litabmas::pages.penentuan-pendanaan.assign-reviewer.modal-show-page :$dosenOption />
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
