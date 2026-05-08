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
    use \Modules\Litabmas\Models\PengajuanPendanaanAnggota;
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    // handle $data
    $primaryData = $data['primary-section']['items'];
    unset($data['primary-section']);
    $detailPendanaan = $data['detail-pendanaan'];
    unset($data['detail-pendanaan']);

    // $resourceId dari Page::buildViewData()
    if (!empty($resourceId)) {
        $editUrl = route('litabmas.pengajuan-pendanaan.edit', $resourceId);
        $data['informasi-umum']['edit_url'] = $editUrl;
    }

    $validasiDokumenBelumDilakukan = $statusDokumenLengkap = false;
    if ($rawData['status_penilaian_administrasi'] === PengajuanPendanaanStatus::PENILAIAN_ADMINISTRASI_BELUM_DIVALIDASI) {
        $validasiDokumenBelumDilakukan = true;
    } elseif ($rawData['status_penilaian_administrasi'] !== PengajuanPendanaanStatus::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_DOKUMEN) {
        $statusDokumenLengkap = true;
    }

    $tanggalReview = Date::formatDateRange($infoReviewAdministrasi['waktu_mulai'], $infoReviewAdministrasi['waktu_selesai'], isoFormatMonth: 'MMMM');
    $sudahMasaReviewAdministrasi = !empty($infoReviewAdministrasi['sudah_masuk_masa_review_administrasi']);

    if (!$sudahMasaReviewAdministrasi && $validasiDokumenBelumDilakukan) {
        $staticAlert = [
            'message' => 'Anda tidak dapat melakukan validasi dokumen karena bukan dalam waktu seleksi administrasi.
                Anda hanya dapat melakukan validasi pada tanggal ' . $tanggalReview . '.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif ($sudahMasaReviewAdministrasi && $validasiDokumenBelumDilakukan) {
        $staticAlert = [
            'message' => 'Jika proposal ini dinyatakan “Dokumen Tidak Lengkap”, maka dianggap tidak lolos untuk tahap selanjutnya.',
            'type' => 'helper'
        ];
    } elseif (!$sudahMasaReviewAdministrasi) {
        $staticAlert = [
            'message' => 'Tidak dapat melakukan perubahan validasi dokumen karena bukan dalam waktu seleksi administrasi.
                Masa seleksi administrasi tanggal ' . $tanggalReview . '.',
            'type' => 'warning',
            'dismissible' => false
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
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData"/>
        </div>
    </div>

    @if(!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert"/>
    @endif

    <x-litabmas::layouts.detail.card title="Validasi Dokumen" customClassBody="util_d-flex util_flex-column util_gap-1"
                                     subtitle="Silahkan cek kelengkapan dari data proposal dibawah ini.">
        <x-slot:action>
            <div class="util_d-flex">
                @if($validasiDokumenBelumDilakukan && $sudahMasaReviewAdministrasi)
                    <x-core::button href="#" variant="destructive" size="sm" data-toggle="modal" class="util_mr-12"
                                    data-target="#modal-status-dokumen-tidak-lengkap">
                        Dokumen Tidak Lengkap
                    </x-core::button>
                    <x-core::button href="#" size="sm" data-toggle="modal"
                                    data-target="#modal-status-dokumen-valid">
                        Tandai Dokumen Lengkap
                    </x-core::button>
                @elseif ($validasiDokumenBelumDilakukan && !$sudahMasaReviewAdministrasi)
                    <x-core::button href="#" variant="destructive" size="sm" disabled class="util_mr-12">
                        Dokumen Tidak Lengkap
                    </x-core::button>
                    <x-core::button href="#" disabled size="sm">
                        Tandai Dokumen Lengkap
                    </x-core::button>
                @elseif (!$validasiDokumenBelumDilakukan)
                    @if($sudahMasaReviewAdministrasi)
                        <x-core::button href="#" variant="outline" size="sm" data-toggle="modal" class="util_mr-12"
                                        data-target="#modal-batalkan-status-dokumen">
                            Batalkan Penilaian
                        </x-core::button>
                    @endif
                    @if($statusDokumenLengkap)
                        <x-core::button href="#" size="sm" disabled leading-icon="check-badge">
                            Dokumen Lengkap
                        </x-core::button>
                    @else
                        <x-core::button href="#" variant="destructive" disabled size="sm" leading-icon="document-minus">
                            Dokumen Tidak Lengkap
                        </x-core::button>
                    @endif
                @endif
            </div>
        </x-slot:action>

        {{-- Pernyataan Penelitian --}}
        @php
            $section = $data['informasi-umum'];
            $attributes = Page::buildAttributes(Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]);
        @endphp
        <x-litabmas::layouts.detail.card {{ $attributes }} />

        {{-- Isian Proposal --}}
        <x-litabmas::layouts.detail.card title="Isian Proposal"
                                         subtitle="Data proposal yang menjelaskan mulai dari Latar belakang, tujuan, metode, dan rencana pelaksanaan proyek">
            @foreach($dataIsianProposal as $item)
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">{{ $item['judul_isian_proposal'] }}</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__value ql-snow">
                        <span class="row-data__colon">:</span>
                        <span class="ql-editor" style="display: flex; flex-direction: column; padding: 0;">
                            {!! $item['isian_proposal'] !!}
                        </span>
                    </span>
                </div>
            @endforeach
        </x-litabmas::layouts.detail.card>

        {{-- Data Peneliti --}}
        <x-litabmas::layouts.detail.card title="Data Peneliti"
                                         subtitle="Detail tim yang terlibat dalam penelitian Anda">
            @php
                $noDosen = 0;
            @endphp
            @foreach($dataPenelitiDosen as $item)
                @php
                    $statusAnggotaDisetujui = $item->apakah_undangan_diterima === PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA;
                    $statusAnggotaDitolak = $item->apakah_undangan_diterima === PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITOLAK;
                    $statusAnggotaMenunggu = $item->apakah_undangan_diterima === PengajuanPendanaanAnggota::STATUS_UNDANGAN_MENUNGGU;

                    $isKetua = $item->apakah_ketua;
                    if (!$isKetua) {
                        $noDosen++;
                    }
                    $rowTitle = $isKetua ? 'Data Ketua' : "Data Anggota Dosen $noDosen";
                @endphp
                <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                    <h3 class="row-data__title">{{ $rowTitle }}</h3>
                    @if(!$isKetua && $statusAnggotaMenunggu)
                        <x-core::badge variant="warning" type="outline" size="sm">
                            Menunggu Persetujuan
                        </x-core::badge>
                    @elseif (!$isKetua && $statusAnggotaDitolak)
                        <x-core::badge variant="danger" type="outline" size="sm">
                            Ditolak
                        </x-core::badge>
                    @elseif (!$isKetua && $statusAnggotaDisetujui)
                        <x-core::badge variant="success" type="outline" size="sm">
                            Diterima
                        </x-core::badge>
                    @endif
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Nama Lengkap</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__value">
                        <span class="row-data__colon">:</span>
                        {{ $item->nama_user }}
                    </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Asal Institusi</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__value">
                        <span class="row-data__colon">:</span>
                        {{ $item->nama_pt }}
                    </span>
                </div>
            @endforeach

            @php
                $noMahasiswa = 1;
            @endphp
            @foreach($dataPenelitiMahasiswa as $item)
                @php
                    $rowTitle = 'Data Anggota Mahasiswa ' . $noMahasiswa++;
                @endphp
                <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                    <h3 class="row-data__title">{{ $rowTitle }}</h3>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Nama Lengkap</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__value">
                        <span class="row-data__colon">:</span>
                        {!! $item->nama_user !!}
                    </span>
                </div>
            @endforeach
        </x-litabmas::layouts.detail.card>

        {{-- Detail Pendanaan --}}
        <x-litabmas::layouts.detail.cards :data="[$detailPendanaan]"/>

        {{-- Dokumen --}}
        <div class="box-table__content" id="table-docs">
            <div class="table-max">
                <table>
                    <thead>
                    <tr>
                        <th>Jenis Dokumen</th>
                        <th>Nama Dokumen</th>
                        <th>Ukuruan</th>
                        <th>Terakhir Diubah</th>
                        <th class="cell-action cell-center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dataDaftarDokumen as $document)
                        <tr>
                            <td>{{ $document['jenis_dokumen'] }}</td>
                            <td class="cell-inline">
                                @if(!empty($document['id']))
                                    <img src="{{ $document['asset_url'] }}" width="24" height="24" alt="">
                                    {{ $document['nama_dokumen'] }}.{{ $document['extension_versi_terbaru'] }}
                                @endif
                            </td>
                            <td>{{ $document['ukuran_file'] }}</td>
                            <td>{{ $document['terakhir_diubah'] }}</td>
                            <td class="cell-action">
                                @if(!empty($document['id']))
                                    <div class="dropdown-group">
                                        <a href="{{ $document['temp_url'] }}" class="btn btn_outline btn_xs btn_icon"
                                           data-btn-label="Download" rel="noopener" target="_blank">
                                            <span class="icon icon-magnifying-glass-solid"></span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    {{-- Modal --}}
    <x-litabmas::pages.penilaian-administrasi.modal-show-page :idPengajuanPendanaan="$rawData['id']"
                                                              :$validasiDokumenBelumDilakukan
    />

    @pushonce('head')
        <style>
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
