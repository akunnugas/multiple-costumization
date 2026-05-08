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
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $isEdit = false;

    $totalReviewer = count($reviewer);
    $sumTotalNilai = 0;
    $bobotPenilaianProposal = AspekPenilaianKomposisiProposal::OPTION_SKALA_BOBOT;
    $batasLolosNominasi = AspekPenilaianKomposisiProposal::NILAI_MEMENUHI_KRITERIA;

    if ($isEdit === true) {
        $methodForm = 'PUT';
        $actionForm = route('litabmas.penilaian-isian.update', $resourceId);
    }

    $isReviewer = null;
    $sudahMasaReviewProposal = !empty($infoReviewProposal['sudah_masuk_masa_review_proposal']);
    $tanggalReview = Date::formatDateRange(
        $infoReviewProposal['waktu_mulai'],
        $infoReviewProposal['waktu_selesai'],
        isoFormatMonth: 'MMMM',
    );

    $buttonColor = 'primary';
    $buttonText = '';

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

    $disableButton = '';
    if ($sudahMasaReviewProposal == false) {
        $disableButton = 'true';
        //jika tanggal review proposal kurang dari tanggal sekarang
        if (strtotime($infoReviewProposal['waktu_mulai']) > strtotime('now')) {
            $staticAlert = [
                'message' =>
                    'Penentuan Nominasi belum bisa dilakukan, Anda dapat memastikan review proposal oleh reviewer terlebih dahulu.',
                'type' => 'warning',
                'dismissible' => false,
            ];
        } elseif (strtotime($infoReviewProposal['waktu_selesai']) < strtotime('now')) {
            $staticAlert = [
                'message' => 'Batas penentuan nominasi sudah lewat. Anda tidak dapat menentukan nominasi saat ini.',
                'type' => 'warning',
                'dismissible' => false,
            ];
        }
    } else {
        $staticAlert = [
            'message' =>
                'Silahkan tentukan nominasi untuk mendapatkan pendanaan penelitian/pengabdian. Anda dapat menentukan pada tanggal ' .
                $tanggalReview .
                '.',
            'type' => 'helper',
            'dismissible' => false,
        ];
        $disableButton = '';
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>

    @if ($buttonText)
        <x-slot:action>
            <div class="util_d-flex">
                <x-core::button href="#" size="sm" disabled="true" leadingIcon="check-circle"
                    variant="{{ $buttonColor }}">
                    {{ $buttonText }}
                </x-core::button>
            </div>
        </x-slot:action>
    @endif

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

    <x-litabmas::layouts.detail.card title="Penilaian Aspek Proposal"
        customClassBody="util_d-flex util_flex-column util_gap-1">
        <div class="table-max table-max_absolute">
            <table style="padding-bottom: 1rem">
                <thead>
                    <tr>
                        @foreach ($staticTableHeader as $item)
                            <th>{{ $item['label'] }}</th>
                        @endforeach
                        @for ($i = 0; $i < $totalReviewer; $i++)
                            <th>Reviewer {{ $reviewer[$i]->reviewer_ke }}</th>
                        @endfor
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspekBobot as $item)
                        <tr>
                            <td>{{ $item['no'] }}</td>
                            <td>
                                <x-litabmas::fields.textarea_breakline :value="$item['nama_komposisi_proposal']" />
                            </td>
                            <td id="bobot-{{ $item['id'] }}">
                                {{ Format::number($item['bobot_komposisi_proposal']) }}</td>
                            @php
                                $totalNilai = 0;
                            @endphp
                            @for ($i = 0; $i < $totalReviewer; $i++)
                                <td>
                                    @php
                                        $nilaiSkala =
                                            ($nilaiKomponen[$reviewer[$i]->reviewer_ke][$item['id']] ?? 0) *
                                            $item['bobot_komposisi_proposal'];
                                        $totalNilai += $nilaiSkala;
                                    @endphp
                                    {{ $nilaiSkala ?: 0 }}
                                </td>
                            @endfor
                            @php
                                $sumTotalNilai += $totalNilai;
                            @endphp
                            <td id="total-nilai-{{ $item['id'] }}">{{ $totalNilai }}</td>
                        </tr>
                    @endforeach
                    @php
                        //Determine the appropriate label based on $sumTotalNilai
                        $labelAfterSumTotalNilai = '';
                        foreach ($bobotPenilaianProposal as $bobot => $label) {
                            if ($sumTotalNilai <= $bobot) {
                                $labelAfterSumTotalNilai = $label;
                                break;
                            }
                        }

                        $totalRataRata = round($sumTotalNilai / $totalReviewer, 2);
                        $colorRata = '#EC3D27';
                        $textRata = 'Tidak Lolos';
                        if ($totalRataRata >= $batasLolosNominasi) {
                            $colorRata = '#15B79E';
                            $textRata = 'Lolos';
                        }
                    @endphp
                    <tr>
                        <td colspan="{{ 3 + $totalReviewer }}">Total Nilai Keseluruhan</td>
                        <td id="sum-nilai">{{ $sumTotalNilai }}</td>
                    </tr>
                    <tr>
                        <td colspan="{{ 3 + $totalReviewer }}">Rata-rata nilai</td>
                        <td id="rata-nilai"><span style="color: {{ $colorRata }}">
                                {{ $totalRataRata }} ({{ $textRata }})<br>
                            </span></td>
                </tbody>
            </table>
            <div class=""
                style="border:1px solid #E3E8EF; border-radius:8px; padding: 1rem 0.75rem; display:flex; flex-direction: column; gap:0.75rem">
                <h2 class="card__content_title" style="margin-top: 0">Keterangan Penilaian</h2>
                <ul class="" style="display: flex; width:100%; padding-left: 1.25rem">
                    @php
                        $width = 100 / count($bobotPenilaianProposal);
                        $start = 0;
                    @endphp
                    @foreach ($bobotPenilaianProposal as $bobot => $label)
                        <li class="col" style="width:{{ $width }}%">
                            {{ $start }}-{{ $bobot }} =
                            {{ $label }}</li>
                        @php
                            $start = $bobot + 1;
                        @endphp
                    @endforeach
                </ul>
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card title="Feedback Reviewer"
        customClassBody="util_d-flex util_flex-column util_gap-1">

        <div class="col-12">
            <div class="grid cols-1">
                <x-litabmas::pages.pengajuan-pendanaan.review-proposal.feedback-section :data="$dataIsianProposal" />
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

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
