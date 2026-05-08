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
    use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
    use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
    use Modules\Litabmas\Models\KlasterPendanaan;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    // $resourceId dari Page::buildViewData()
    if (!empty($resourceId)) {
        $editUrl = route('litabmas.pengajuan-pendanaan.edit', $resourceId);
        $data['informasi-umum']['edit_url'] = $editUrl;
    }

    //status ai
    $statusNilaiAi = [
        true => 'Tidak Lolos',
        false => 'Lolos',
    ];

    //status color
    $statusColor = [
        true => '#EC3D27',
        false => '#15B79E',
    ];

    $dataDetail = $rawData;
    $reviewer = $dataDetail['reviewer'];

    $peserta = $dataDetail['peserta'];

    $anggotaDosen = [PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL, PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL];
    $anggotaMahasiswa = PengajuanPendanaanAnggota::JENIS_MAHASISWA;

    $waktuMulai = Date::formatDate($infoReviewProposal['waktu_mulai'], 'd F Y');

    $sudahMasaReviewProposal = !empty($infoReviewProposal['sudah_masuk_masa_review_proposal']);
    $tanggalReview = Date::formatDateRange(
        $infoReviewProposal['waktu_mulai'],
        $infoReviewProposal['waktu_selesai'],
        isoFormatMonth: 'MMMM',
    );

    $disableButton = '';
    if (!$sudahMasaReviewProposal) {
        $disableButton = 'true';
        $staticAlert = [
            'message' =>
                 'Anda tidak dapat menentukan nominasi karena bukan dalam waktu review proposal.
                Anda dapat menentukan nominasi pada tanggal ' . $tanggalReview . '.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    } elseif ($rawData['status_penilaian_isian_proposal'] !== PengajuanPendanaanStatus::PENILAIAN_ISIAN_PROPOSAL_SUDAH_DINILAI) {
        $staticAlert = [
            'message' => 'Terdapat reviewer yang belum melakukan penilaian isian proposal.',
            'type' => 'warning',
            'dismissible' => false,
        ];
        $disableButton = 'true';
    } else {
        $staticAlert = [
            'message' =>
                'Silahkan tentukan nominasi untuk mendapatkan pendanaan penelitian/pengabdian. Anda dapat menentukan pada tanggal ' .
                $tanggalReview .
                '.',
            'type' => 'helper',
            'dismissible' => false,
        ];
    }

    $buttonColor = 'primary';

    $statusNominasi = '';
    if ($dataDetail['status_penentuan_nominasi'] == PengajuanPendanaanStatus::PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI) {
        $statusNominasi = 'Tidak Lolos Nominasi';
        $buttonText = 'Dinyatakan Tidak Lolos Nominasi';
        $buttonColor = 'destructive';
        $buttonIcon = 'x-circle';
    } elseif ($dataDetail['status_penentuan_nominasi'] == PengajuanPendanaanStatus::PENENTUAN_NOMINASI_LOLOS_NOMINASI) {
        $statusNominasi = 'Lolos Nominasi';
        $buttonText = 'Dinyatakan Lolos Nominasi';
        $buttonIcon = 'check-circle';
    } else {
        $statusNominasi = 'Review Proposal';
    }

    $totalNilai = 0;
    if (!empty($dataDetail['nilaiKomponenAll']['detail_total'])) {
        $dataTotalNilai = [];

        foreach ($dataDetail['nilaiKomponenAll']['detail_total'] as $reviewerke => $dataNilai) {
            $dataTotalNilai[$reviewerke] = 0;
            foreach ($dataNilai as $aspek => $nilai) {
                $dataTotalNilai[$reviewerke] += (float) $nilai;
            }
            $totalNilai += (float) $dataTotalNilai[$reviewerke];
        }
    }

    $batasLolosNominasi = AspekPenilaianKomposisiProposal::NILAI_MEMENUHI_KRITERIA;
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

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            @foreach ($data['primary-section']['items'] as $label => $item)
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">{{ $item['label'] }}</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        <span class="row-data__colon">:</span>
                        @php
                            $simpleFile = null;
                            if (!empty($item['showSimpleFile'])) {
                                $simpleFile = Modules\DMS\Models\Dokumen::where(
                                    'id',
                                    $item['original'] ?? null,
                                )->first();
                                // kalo nggk ada set text ke null biar bukan id yg tampil
                                $item['text'] = $simpleFile
                                    ? $simpleFile->nama_dokumen . '.' . $simpleFile->extension_versi_terbaru
                                    : null;
                            } elseif (isset($item['file_type'])) {
                                $dataFiles[] = $item;
                                continue;
                            }
                        @endphp
                        @if (!empty($simpleFile))
                            @php
                                $ext = $simpleFile->extension_versi_terbaru;
                                $ext = $ext == 'docx' ? 'doc' : $ext;
                                $assetUrl = asset("images/$ext-solid.svg");
                                $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                            @endphp
                            <a href="{{ $tempUrl }}" rel="noopener" target="_blank"
                                class="util_d-flex util_flex-center-vertical">
                                <img height="20px;" src="{{ $assetUrl }}"
                                    alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                                &nbsp; Lihat File
                            </a>
                        @elseif (!empty($item['text']))
                            {{ $item['text'] }}
                        @else
                            {{ $dataDetail[$item['field']] }}
                        @endif
                    </span>
                </div>
            @endforeach
            @php
                $totalReviewer = count($reviewer);
            @endphp
            @for ($i = 0; $i < $totalReviewer; $i++)
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Reviewer {{ $reviewer[$i]->reviewer_ke }}</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        <span class="row-data__colon">:</span>
                        {{ $dataDetail['dosenNameFormated'][$reviewer[$i]->id_biodata] ?? $reviewer[$i]->nama_user }}
                    </span>
                </div>
            @endfor
        </div>
    </div>

    {{-- Alert --}}
    @if (!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert" />
    @endif


    {{-- Detail --}}
    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1">
        <x-slot:action>
            <div class="util_d-flex">
                @if (!$dataDetail['status_penentuan_nominasi'])
                    <x-core::button href="#" variant="outline" size="sm" data-toggle="modal"
                        disabled="{{ $disableButton }}" class="util_mr-12"
                        data-target="#modal-status-tidak-lolos-nominasi">
                        Tolak Nominasi
                    </x-core::button>
                    <x-core::button href="#" size="sm" data-toggle="modal" disabled="{{ $disableButton }}"
                        data-target="#modal-status-lolos-nominasi">
                        Terima Nominasi
                    </x-core::button>
                @else
                    @if ($sudahMasaReviewProposal)
                        <x-core::button href="#" variant="outline" size="sm" data-toggle="modal"
                            disabled="{{ $disableButton }}" class="util_mr-12"
                            data-target="#modal-batalkan-status-nominasi">
                            Batalkan Status Nominasi
                        </x-core::button>
                    @endif
                    <x-core::button href="#" size="sm" disabled="true" leadingIcon="{{ $buttonIcon }}"
                        variant="{{ $buttonColor }}">
                        {{ $buttonText }}
                    </x-core::button>
                @endif
            </div>
        </x-slot:action>

        <div class="col-12">
            <div class="grid">
                @foreach ($data['secondary-section']['items'] as $item)
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">{{ $item['label'] }}</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                            @if ($item['field'] == 'status_nominasi')
                                <x-core::badge variant="primary" type="outline" size="sm">
                                    {{ $statusNominasi }}
                                </x-core::badge>
                            @elseif (!empty($item['text']))
                                {{ $item['text'] }}
                            @else
                                {{ $dataDetail[$item['field']] ?? 'Belum Ditentukan' }}
                            @endif
                        </span>
                    </div>
                @endforeach
                <div class="col-12" style="border: 1px dashed #E3E8EF"></div>
                @foreach ($data['third-section']['items'] as $item)
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">{{ $item['label'] }}</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                            @if ($item['field'] == 'penilaian_index_similarity')
                                <span
                                    style="color: {{ $statusColor[$dataDetail['infoSimilariyAI']['similarity']['melebihi_toleransi']] }}">{{ Format::removeTrailingZeroes($dataDetail['infoSimilariyAI']['similarity']['nilai']) }}%
                                    ({{ $statusNilaiAi[$dataDetail['infoSimilariyAI']['similarity']['melebihi_toleransi']] }})
                                </span>
                            @elseif ($item['field'] == 'penilaian_index_ai')
                                <span
                                    style="color: {{ $statusColor[$dataDetail['infoSimilariyAI']['ai']['melebihi_toleransi']] }}">
                                    {{ Format::removeTrailingZeroes($dataDetail['infoSimilariyAI']['ai']['nilai']) }}%
                                    ({{ $statusNilaiAi[$dataDetail['infoSimilariyAI']['ai']['melebihi_toleransi']] }})
                                </span>
                            @else
                                @php
                                    $totalRataRata = $totalNilai / $totalReviewer;
                                    $colorRata = '#EC3D27';
                                    $textRata = 'Tidak Lolos';
                                    if ($totalRataRata >= $batasLolosNominasi) {
                                        $colorRata = '#15B79E';
                                        $textRata = 'Lolos';
                                    }
                                @endphp
                                <span style="color: {{ $colorRata }}">
                                    {{ $totalRataRata }} ({{ $textRata }})<br>
                                </span>
                                @for ($i = 0; $i < $totalReviewer; $i++)
                                    {{ $dataTotalNilai[$reviewer[$i]->reviewer_ke] ?? 0 }} ( Reviewer
                                    {{ $reviewer[$i]->reviewer_ke }} -
                                    {{ $reviewer[$i]->nama_user }} )<br>
                                @endfor
                            @endif
                        </span>
                    </div>
                @endforeach
                <div class="col-12" style="border: 1px dashed #E3E8EF"></div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <label class="row-data__name">Ketua Penelitian</label>
                </div>
                <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                    <span class="row-data__colon">:</span>
                    <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                        {{ $dataDetail['dosenNameFormated'][$peserta['ketua']['id_biodata']] ?? $peserta['ketua']['nama_user'] }}
                    </span>
                </div>
                @if ($dataDetail['kategori_klaster'] == KlasterPendanaan::KATEGORI_KELOMPOK)
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Anggota Dosen</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                            @foreach ($anggotaDosen as $jenis)
                                @foreach ($peserta['anggota'] as $jenisAnggota => $anggota)
                                    @if ($jenisAnggota == $jenis)
                                        @foreach ($anggota as $eachAnggota)
                                            {{ $dataDetail['dosenNameFormated'][$eachAnggota['id_biodata']] ?? $eachAnggota['nama_user'] }}<br>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endforeach
                        </span>
                    </div>
                    @if (isset($peserta['anggota'][$anggotaMahasiswa]))
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Anggota Mahasiswa</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                                @foreach ($peserta['anggota'] as $jenisAnggota => $anggota)
                                    @if ($jenisAnggota == $anggotaMahasiswa)
                                        @foreach ($anggota as $eachAnggota)
                                            {{ $eachAnggota['nama_user'] }}<br>
                                        @endforeach
                                    @endif
                                @endforeach
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </x-litabmas::layouts.detail.card>
    {{-- Modal --}}
    @if ($sudahMasaReviewProposal)
        <x-litabmas::pages.penentuan-nominasi.modal-show-page :idPengajuanPendanaan="$dataDetail['id_pengajuan_pendanaan']" :sudahDinominasikan="$dataDetail['status_penentuan_nominasi']" />
    @endif
    {{-- Modal --}}
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
