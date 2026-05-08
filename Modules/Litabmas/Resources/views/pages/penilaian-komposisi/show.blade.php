@php
    use Modules\Core\Helpers\Date;
    use Modules\Litabmas\Models\PenilaianReviewerKomposisiProposal;
    use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
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

    $skalaNilai = PenilaianReviewerKomposisiProposal::SKALA_NILAI;
    $bobotPenilaianProposal = AspekPenilaianKomposisiProposal::OPTION_SKALA_BOBOT;

    $primaryData = $data['primary-section']['items'];
    $section = $data['primary-section'];
    $editURL = Page::buildURL(['edit' => true]);

    $tableDetail = $data['table-detail'];

    //table header
    $tableHeader = $tableDetail['items'];

    // form
    $action = $method = null;

    if (isset($isEdit) && $isEdit != 1) {
        $isEdit = null;
    }

    if (!empty($isEdit)) {
        $method = 'PUT';
        // $action = route('litabmas.penilaian-isian-proposal.updateNilaiIsianProposal');
    }

    //add placeholder for select
    $placeholder = ['' => 'Pilih Skala Nilai'];
    $skalaNilaiMapped = [];
    foreach ($skalaNilai as $key => $value) {
        $skalaNilaiMapped[$key] = $key . ' (' . $value . ')';
    }

    $skalaNilaiMapped = $placeholder + $skalaNilaiMapped;

    $sudahMasaReviewProposal = !empty($infoReviewProposal['sudah_masuk_masa_review_proposal']);
    $tanggalReview = Date::formatDateRange(
        $infoReviewProposal['waktu_mulai'],
        $infoReviewProposal['waktu_selesai'],
        isoFormatMonth: 'MMMM',
    );

    $disableButton = !$sudahMasaReviewProposal ? 'true' : '';

    if (!$isReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak dapat memberikan penilaian karena bukan reviewer dari proposal ini.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    } elseif (!$sudahMasaReviewProposal) {
        $staticAlert = [
            'message' =>
                'Anda tidak dapat memberikan nilai karena bukan dalam waktu review proposal.
                Anda dapat memberikan nilai pada tanggal ' .
                $tanggalReview .
                '.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    } else {
        $staticAlert = [
            'message' => 'Anda dapat memberikan nilai pada tanggal ' . $tanggalReview . '.',
            'type' => 'helper',
            'dismissible' => false,
        ];
    }

    $sumTotalNilai = 0;

    $defaultCurrency = config('money.defaults.currency');
    $currency = $rawData['mata_uang'] ?? $defaultCurrency;
    $convert = $currency !== $defaultCurrency;

    $canUpdate = 1;
    $disableEdit = 1;
@endphp

<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    @pushOnce('head')
        @vite('resources/scss/layouts/_detail.scss')
        @vite('resources/scss/custom-utils.scss')
    @endPushOnce

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
                            <li class="breadcrumb__item active">
                                @if ($i === count($breadcrumb['items']) - 1)
                                    Detail {{ $item['label'] }}
                                @else
                                    {{ $item['label'] }}
                                @endif
                            </li>
                        @endif
                    @endforeach
                    @if ($breadcrumb['showTitle'])
                        <li class="breadcrumb__item active">{{ $subtitle }}</li>
                    @endif
                </ul>

                <div class="button-group">
                    <div class="button-group__left">
                        <a class="btn btn_outline btn_xs"
                            href="{{ !empty($resourceId) ? Page::indexURL() : Page::backURL() }}">
                            Kembali ke List
                        </a>

                        @if (!empty($action))
                            {{ $action }}
                        @endif

                        @if ($canUpdate && !$disableEdit)
                            <a class="btn btn_primary btn_xs" href="{{ Page::editURL($resourceId) }}">
                                Ubah Data
                            </a>
                        @endif
                    </div>

                    <div class="button-group__mobile">
                        <a href="{{ !empty($resourceId) ? Page::indexURL() : Page::backURL() }}"
                            class="btn btn_outline btn_icon btn_xs">
                            <span class="icon icon-arrow-left-solid"></span>
                        </a>

                        @if ($canUpdate && !$disableEdit)
                            <a class="btn btn_primary btn_icon btn_xs" href="{{ Page::editURL($resourceId) }}">
                                <span class="icon icon-pencil-solid"></span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card__body" style="padding: 0.625rem">
                <!-- Sidebar -->
                <x-litabmas::layouts.detail.sidebar-v2 :title="$title" :subtitle="$subtitle" :submenu="$submenu" />
                <!-- Content -->
                <div class="content" style="margin: 0 !important; padding: 1.5rem !important; width: 80%">
                    <h2>{{ $subtitle }}</h2>

                    <div class="card card_details-custom_blue">
                        <div class="grid">
                            <x-litabmas::layouts.detail.line :data="$primaryData" />
                        </div>
                    </div>

                    @if (!empty($staticAlert))
                        <x-core::layouts.html.alert :data="$staticAlert" />
                    @endif


                    @if ($isReviewer)
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($tableHeader as $item)
                                        <th>{{ $item['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($aspekBobot as $item)
                                    @php
                                        $totalNilai = 0;
                                        if (isset($nilaiIsianProposal[$item['id']])) {
                                            $nilaiSkala =
                                                $nilaiIsianProposal[$item['id']]['skala_nilai_komposisi_proposal'];
                                            $totalNilai = $nilaiSkala * $item['bobot_komposisi_proposal'];
                                        } else {
                                            $nilaiSkala = 0;
                                        }
                                        $sumTotalNilai += $totalNilai;
                                    @endphp
                                    <tr>
                                        <td width="10">{{ $item['no'] }}</td>
                                        <td>{{ $item['nama_komposisi_proposal'] }}</td>
                                        <td id="bobot-{{ $item['id'] }}">
                                            {{ $item['bobot_komposisi_proposal'] }}</td>
                                        @if ($isEdit == true)
                                            <td>
                                                <x-core::select class="nilai-komponen"
                                                    data-komponen="{{ $item['id'] }}"
                                                    name="nilaiKomponent[{{ $item['id'] }}]" :options="$skalaNilaiMapped"
                                                    :selected="$nilaiSkala" />
                                            </td>
                                        @else
                                            <td>{{ $nilaiSkala ?: 0 }}</td>
                                        @endif
                                        <td id="total-nilai-{{ $item['id'] }}">{{ $totalNilai }}</td>
                                    </tr>
                                @endforeach
                                @php
                                    // Determine the appropriate label based on $sumTotalNilai
                                    $labelAfterSumTotalNilai = '';
                                    foreach ($bobotPenilaianProposal as $bobot => $label) {
                                        if ($sumTotalNilai <= $bobot) {
                                            $labelAfterSumTotalNilai = $label;
                                            break;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td colspan="4">Total Nilai Keseluruhan</td>
                                    <td id="sum-nilai">{{ $sumTotalNilai }} ( {{ $labelAfterSumTotalNilai }} )</td>
                                </tr>
                            </tbody>
                        </table>
                        <x-litabmas::layouts.detail.card-keterangan-penilaian-aspek :bobotPenilaian="$bobotPenilaianProposal" />
                    @else
                        <x-core::table>
                            @php
                                $showAction = $showDetail = $isEditInline = false;
                                $canDelete = $canUpdate = $canCreate = false;
                                $showNumber = true;
                                $nilaiMemenuhiKriteriaProposal =
                                    AspekPenilaianKomposisiProposal::NILAI_MEMENUHI_KRITERIA;
                            @endphp
                            <x-core::table.data :header="$headerNilaiKomposisiProposal" :data="$rekapNilaiKomposisiProposal['data']" :can-create="$canCreate && $isReference && $create" :$canDelete
                                :$canUpdate :$showDetail :$showNumber :$isEditInline :resourceTitle="$title">
                                <x-slot:customRowInsideBody>
                                    <tr class="custom-background-gray">
                                        <td colspan="{{ count($headerNilaiKomposisiProposal) }}">
                                            <b>Total Nilai Keseluruhan</b>
                                        </td>
                                        <td class="util_text-right">
                                            <b>{{ $rekapNilaiKomposisiProposal['total_nilai_keseluruhan'] }}</b>
                                        </td>
                                    </tr>
                                    <tr class="custom-background-gray">
                                        <td colspan="{{ count($headerNilaiKomposisiProposal) }}">
                                            <b>Rata-Rata Nilai</b>
                                        </td>
                                        <td>
                                            <span class="util_d-flex util_flex-center-vertical util_flex-end">
                                                @if ($rekapNilaiKomposisiProposal['rata_rata_nilai'] >= $nilaiMemenuhiKriteriaProposal)
                                                    <span class="icon icon-check-circle-solid util_mr-4"
                                                        height="20"></span>
                                                @else
                                                    <span class="icon icon-x-circle-solid util_mr-4"
                                                        height="20"></span>
                                                @endif
                                                <b>{{ $rekapNilaiKomposisiProposal['rata_rata_nilai'] }}</b>
                                            </span>
                                        </td>
                                    </tr>
                                </x-slot:customRowInsideBody>
                            </x-core::table.data>
                        </x-core::table>

                        <x-litabmas::layouts.detail.card-keterangan-penilaian-aspek :bobotPenilaian="$bobotPenilaianProposal" />
                    @endif

                    <div>
                        <hr class="divider">
                        <h2 class="header__title_custom util_mt-16">Rekomendasi Pendanaan</h2>

                        <div class="form-control util_pt-8">
                            <div class="form-control__group grid">
                                <label class="col-3">Maksimal Anggaran Klaster</label>
                                <label class="col-9">:
                                    {{ money($rawData['maksimal_anggaran_klaster'] ?? 0, $currency, $convert) }}
                                </label>
                            </div>
                        </div>
                        <div class="form-control util_pt-8">
                            <div class="form-control__group grid">
                                <label class="col-3">Usulan Biaya</label>
                                <label class="col-9">:
                                    {{ money($rawData['nominal_anggaran_diajukan'] ?? 0, $currency, $convert) }}
                                </label>
                            </div>
                        </div>
                        @if ($isReviewer)
                            <div class="form-control util_pt-8">
                                <div class="form-control__group grid">
                                    <label class="col-3">
                                        Rekomendasi Anggaran
                                        @if ($isEdit != false)
                                            <span class="important">*</span>
                                        @endif
                                    </label>
                                    <div class="col-9">
                                        @if ($isEdit == false)
                                            <label>:
                                                {{ money($rawData['rekomendasi_anggaran'] ?? 0, $currency, $convert) }}
                                            </label>
                                        @else
                                            @if (!empty($rawData['rekomendasi_anggaran']))
                                                @php
                                                    $rawData['rekomendasi_anggaran'] =
                                                        (int) $rawData['rekomendasi_anggaran'];
                                                @endphp
                                            @endif
                                            <x-core::controls.input type="number" name="rekomendasi_anggaran" required
                                                control="currency"
                                                value="{{ $rawData['rekomendasi_anggaran'] ?? null }}" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif(!empty($rawData['reviewers']))
                            @foreach ($rawData['reviewers'] as $reviewer)
                                <div class="form-control util_pt-8">
                                    <div class="form-control__group grid">
                                        <label class="col-3">Rekomendasi Anggaran Reviewer
                                            {{ $reviewer['reviewer_ke'] }}</label>
                                        <label class="col-9">:
                                            {{ money($reviewer['rekomendasi_anggaran'] ?? 0, $currency, $convert) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('headVendor')
        <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
        <script src="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.min.js') }}"></script>
    @endPushOnce
    @push('head')
        <style>
            .card_details-custom_blue {
                background: var(--qn-primary-100);
                border: 1px solid var(--qn-primary-200);
                padding: 1rem 1.5rem;
                font-weight: 400;
                border-radius: 0.5rem;
                line-height: 0.7rem;
                margin: 1.7rem 0 1rem 0 !important;
            }

            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .form-control .form-control__label {
                color: #364152 !important;
            }
        </style>
    @endpush

    @if (!empty($outer))
        {{ $outer }}
    @endif

    @push('scripts')
        <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>
        <!-- [END] Core script -->


        <script>
            const sidebarWithin = document.querySelector('.sidebar.sidebar_within');
            const mainContent2 = document.querySelector('.content');

            document.querySelector('.sidebar__action button').onclick = function() {
                sidebarWithin.classList.toggle('sidebar_within-collapsed');
                mainContent2.classList.toggle('content_large');

            }
        </script>
    @endpush

    @push('scripts')
        <script>
            (() => {
                //if class nilai-komponen change
                document.querySelectorAll('.nilai-komponen').forEach((item) => {
                    item.addEventListener('change', (e) => {
                        const komponen = e.target.getAttribute('data-komponen');
                        const bobot = document.getElementById('bobot-' + komponen).innerText;
                        const nilai = e.target.value;
                        const totalNilai = bobot * nilai;
                        document.getElementById('total-nilai-' + komponen).innerText = totalNilai;
                        let sumTotalNilai = 0;
                        document.querySelectorAll('.nilai-komponen').forEach((item) => {
                            const komponen = item.getAttribute('data-komponen');
                            const bobot = document.getElementById('bobot-' + komponen).innerText;
                            const nilai = item.value;
                            const totalNilai = bobot * nilai;
                            sumTotalNilai += totalNilai;
                        });
                        document.getElementById('sum-nilai').innerText = sumTotalNilai;
                    });
                });


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
</x-core::layouts.outer>
