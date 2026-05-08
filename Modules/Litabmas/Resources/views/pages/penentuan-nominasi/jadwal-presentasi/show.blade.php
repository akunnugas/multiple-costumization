@php
    use Modules\Core\Helpers\Page;
    use Modules\Core\Helpers\Date;
    use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
@endphp
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
    $dataDetail = $rawData;
    $resourceId = $dataDetail['id_pengajuan_pendanaan'];
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $isEdit = false;

    $totalReviewer = count($reviewer);
    $sumTotalNilai = 0;
    $bobotPenilaianProposal = AspekPenilaianKomposisiProposal::OPTION_SKALA_BOBOT;

    if ($isEdit === true) {
        $methodForm = 'PUT';
        $actionForm = route('litabmas.penilaian-isian.update', $resourceId);
    }

    $buttonColor = 'primary';
    $statusNominasi = '';
    if ($dataDetail['status_penentuan_nominasi'] == PengajuanPendanaanStatus::PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI) {
        $statusNominasi = 'Tidak Lolos Nominasi';
        $buttonText = 'Dinyatakan Tidak Lolos Nominasi';
        $buttonColor = 'destructive';
    } elseif ($dataDetail['status_penentuan_nominasi'] == PengajuanPendanaanStatus::PENENTUAN_NOMINASI_LOLOS_NOMINASI) {
        $statusNominasi = 'Lolos Nominasi';
        $buttonText = 'Dinyatakan Lolos Nominasi';
    } else {
        $statusNominasi = 'Review Proposal';
    }

    // cek jika sumber pendanaan memiliki presentasi proposal
    if (!empty($sumberPendanaanMemilikiPresentasiProposal)) {
        $sudahMasaReviewProposal = !empty($infoReviewProposal['sudah_masuk_masa_review_proposal']);
        $tanggalReview = Date::formatDateRange(
            $infoReviewProposal['waktu_mulai'],
            $infoReviewProposal['waktu_selesai'],
            isoFormatMonth: 'MMMM',
        );

        $disableButton = '';
        if (
            $dataDetail['status_penentuan_nominasi'] ===
            PengajuanPendanaanStatus::PENENTUAN_NOMINASI_BELUM_DINOMINASIKAN
        ) {
            $disableButton = 'true';
            $staticAlert = [
                'message' =>
                    'Anda tidak dapat menentukan jadwal presentasi karena proposal ini belum dinyatakan lolos nominasi.',
                'type' => 'warning',
                'dismissible' => false,
            ];
        } elseif (
            $dataDetail['status_penentuan_nominasi'] ==
            PengajuanPendanaanStatus::PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI
        ) {
            $staticAlert = [
                'message' => 'Anda tidak bisa menambahkan jadwal karena penelitian dinyatakan tidak lolos nominasi',
                'type' => 'warning',
                'dismissible' => false,
            ];
        } elseif (
            $dataDetail['status_penentuan_nominasi'] == PengajuanPendanaanStatus::PENENTUAN_NOMINASI_LOLOS_NOMINASI
        ) {
            $staticAlert = [
                'message' => 'Silakan tentukan jadwal presentasi pada proposal ini.',
                'type' => 'helper',
                'dismissible' => false,
            ];
        }
    } else {
        // jika sumber pendanaan tidak memiliki presentasi proposal
        $disableButton = 'true';
        $staticAlert = [
            'message' =>
                'Sumber Pendanaan pada Proposal ini tidak memerlukan presentasi proposal, sehingga tidak membutuhkan jadwal presentasi proposal.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    }

    // cek hide & show button
    $showButton = true;
    if (
        $dataDetail['status_penentuan_nominasi'] != PengajuanPendanaanStatus::PENENTUAN_NOMINASI_LOLOS_NOMINASI || // jika proposal belum lolos nominasi
        empty($sumberPendanaanMemilikiPresentasiProposal) || // jika sumber pendanaan tidak memiliki presentasi proposal
        !$sudahMasaReviewProposal // jika belum masuk masa review proposal
    ) {
        $showButton = false;
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>

    <x-core::layouts.html.alert />

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

    @if (
        $jadwalPresentasi &&
            $dataDetail['status_penentuan_nominasi'] == PengajuanPendanaanStatus::PENENTUAN_NOMINASI_LOLOS_NOMINASI)
        @php
            array_splice($fields, 1, 0, [
                [
                    'label' => 'Status',
                    'field' => 'status',
                    'type' => 'boolean',
                    'disabled' => true,
                    'type' => 'hidden',
                ],
            ]);
            $jenisKegiatan = $jadwalPresentasi['tipe_kegiatan'];
        @endphp
        {{-- Detail --}}
        <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1">
            <div class="col-12">
                <div class="grid">
                    <div class="col-11 grid">
                        @foreach ($fields as $item)
                            @if ($jenisKegiatan == 'offline')
                                @if ($item['field'] == 'link_presentasi_kegiatan')
                                    @continue
                                @endif
                            @elseif($jenisKegiatan == 'online')
                                @if ($item['field'] == 'tempat_pelaksanaan')
                                    @continue
                                @endif
                            @endif
                            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                                <label class="row-data__name">{{ $item['label'] }}</label>
                            </div>
                            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                                <span class="row-data__colon">:</span>
                                <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                                    @php
                                        $isOffline =
                                            $item['field'] === 'tipe_kegiatan' &&
                                            $jadwalPresentasi[$item['field']] === 'offline';
                                        $isOnline =
                                            $item['field'] === 'tipe_kegiatan' &&
                                            $jadwalPresentasi[$item['field']] === 'online';
                                    @endphp

                                    @switch($item['field'])
                                        @case('status')
                                            <x-litabmas::fields.status_presentasi_proposal :data="$jadwalPresentasi" />
                                        @break

                                        @case('waktu_pelaksanaan')
                                            {{ Date::formatDate($jadwalPresentasi[$item['field']], 'DD MMMM YYYY, HH:mm') }} WIB
                                        @break

                                        @case('tipe_kegiatan')
                                            {{ $isOffline ? 'Offline' : 'Online' }}
                                        @break

                                        @default
                                            @if (!empty($item['text']))
                                                {{ $item['text'] }}
                                            @else
                                                {{ $jadwalPresentasi[$item['field']] }}
                                            @endif
                                    @endswitch
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-1">
                        <x-core::button
                            href="{{ route('litabmas.penentuan-nominasi.edit-jadwal-presentasi', [
                                'penentuan_nominasi' => $resourceId,
                                'jadwal_presentasi' => $jadwalPresentasi['id'],
                            ]) }}"
                            size="sm" class="util_mr-8" leading-icon="pencil-square-solid" variant="link">
                            Ubah Data
                        </x-core::button>
                    </div>
                </div>
            </div>

        </x-litabmas::layouts.detail.card>
    @else
        <x-core::layouts.detail.card>
            <x-core::handler title="Belum Ada Data {{ $title }}"
                subtitle="Silakan buat {{ strtolower($title) }} jika proposal ini lolos ke tahap nominasi"
                createLabel="Buat {{ $title }}" createIcon="icon icon-calendar-days"
                customCreateUrl="{{ route('litabmas.penentuan-nominasi.create-jadwal-presentasi', ['penentuan_nominasi' => $resourceId]) }}"
                withHandleButton="{{ $showButton }}" />
        </x-core::layouts.detail.card>
    @endif

    @pushOnce('headVendor')
        <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
        <script src="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.min.js') }}"></script>
    @endPushOnce
    @pushonce('head')
        <style>
            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .form-control .form-control__label {
                color: #364152 !important;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
