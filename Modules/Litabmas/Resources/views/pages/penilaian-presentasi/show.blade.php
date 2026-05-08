@php
    use Modules\Core\Helpers\Date;
    use Modules\Litabmas\Models\PenilaianReviewerPresentasiProposal;
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

    $primarySection = $data['primary-section'];
    $primaryData = $primarySection['items'];

    $tablePenilaian = $data['table-detail'];
    $tableHeader = $tablePenilaian['items'];

    $permission = request()->permission;
    $action = $method = $editURL = null;

    $isReviewer = $rawData['is_reviewer'];
    $sumTotalNilai = 0;
    $skalaNilai = PenilaianReviewerPresentasiProposal::SKALA_NILAI;

    if ($permission['put'] == true) {
        $editURL = Page::buildURL(['edit' => true]);

        if (isset($isEdit) && $isEdit != 1) {
            $isEdit = null;
        }

        if (!empty($isEdit)) {
            $method = 'PUT';
            // $action = route('litabmas.penilaian-isian-proposal.updatenilaiPresentasiProposal');
        }
    }

    $placeholderSkala = 'Pilih Skala Nilai';
    $skalaNilaiMapped = [];
    foreach ($skalaNilai as $key => $value) {
        $skalaNilaiMapped[$key] = $key . ' (' . $value . ')';
    }
    $skalaNilaiMapped = collect($skalaNilaiMapped)->prepend($placeholderSkala, '');

    $disableAction = '';
    if (!$isReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak dapat memberikan feedback karena bukan reviewer dari proposal ini.',
            'type' => 'warning',
            'dismissible' => false,
        ];
        $disableAction = 'true';
    } elseif ($sumberPendanaanMemilikiPresentasiProposal) {
        $sudahMasaPresentasiProposal = !empty($infoPresentasiProposal['sudah_masuk_masa_presentasi_proposal']);
        if (!$sudahMasaPresentasiProposal) {
            $tanggalPresentasi = Date::formatDateRange(
                $infoPresentasiProposal['waktu_mulai'],
                $infoPresentasiProposal['waktu_selesai'],
                isoFormatMonth: 'MMMM',
            );
            $staticAlert = [
                'message' =>
                    'Anda tidak dapat memberikan nilai karena bukan dalam waktu presentasi proposal.
                    Anda dapat memberikan nilai pada tanggal ' .
                    $tanggalPresentasi .
                    '.',
                'type' => 'warning',
                'dismissible' => false,
            ];
            $disableAction = 'true';
        }
    } else {
        $staticAlert = [
            'message' => 'Sumber Pendanaan pada Proposal ini tidak menggunakan agenda presentasi proposal.',
            'type' => 'helper',
            'dismissible' => false,
        ];
        $disableAction = 'true';
    }
@endphp
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

    <x-core::form id="form_list" :$action :$method>
        <div class="card card_details">
            <div class="card__header">
                <div class="card__header-left">
                    <div class="card__header-block">
                        <h2 class="header__title">Berikan Penilaian</h2>
                        <span class="header__subtitle">Silakan berikan feedback berupa komentar dan berikan penilaian
                            pada
                            Proposal Penelitian ini </span>
                    </div>
                </div>
                @if($isReviewer)
                    @if ($permission['put'] == true)
                        @if ($isEdit == false)
                            <div class="card__header-right">
                                <x-core::button href="{{ $editURL }}" size="sm" class="util_mr-8"
                                    disabled="{{ $disableAction }}" leading-icon="pencil-square-solid">
                                    Ubah Data
                                </x-core::button>
                            </div>
                        @else
                            <div class="card__header-right" style="display: flex">
                                <x-core::button href="{{ Page::buildURL(['edit' => null]) }}" variant="outline"
                                    size="sm" class="util_mr-8">
                                    Batalkan
                                </x-core::button>
                                <x-core::button type="submit" size="sm" class="util_mr-8">
                                    <span class="btn__text">Simpan Nilai</span>
                                </x-core::button>
                            </div>
                        @endif
                    @else
                        <div class="card__header-right">
                            <x-core::button size="sm" class="util_mr-8" leading-icon="pencil-square-solid"
                                disabled="{{ $disableAction }}">
                                Ubah Data
                            </x-core::button>
                        </div>
                    @endif
                @endif
            </div>
            <div class="card__body table-max table-max_absolute grid cols-1">
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
                                if (isset($nilaiPresentasiProposal[$item['id']])) {
                                    $nilaiSkala =
                                        $nilaiPresentasiProposal[$item['id']]->skala_nilai_presentasi_proposal;
                                    $totalNilai = $nilaiSkala * $item['bobot_pertanyaan_presentasi_proposal'];
                                } else {
                                    $nilaiSkala = 0;
                                }
                                $sumTotalNilai += $totalNilai;
                            @endphp
                            <tr>
                                <td width="10">{{ $item['no'] }}</td>
                                <td>
                                    <x-litabmas::fields.textarea_breakline :value="$item['pertanyaan_presentasi_proposal']" />
                                </td>
                                <td id="bobot-{{ $item['id'] }}">
                                    {{ Format::number($item['bobot_pertanyaan_presentasi_proposal']) }}</td>
                                @if ($isReviewer && $isEdit == true)
                                    <td>
                                        <x-core::select class="nilai-komponen" data-komponen="{{ $item['id'] }}"
                                            name="nilaiPresentasi[{{ $item['id'] }}]" :options="$skalaNilaiMapped"
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
                <x-litabmas::layouts.detail.card-keterangan-penilaian-aspek :bobotPenilaian="$bobotPenilaianProposal"/>
            </div>
        </div>
    </x-core::form>


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

            .card .row-data__value ol {
                padding-left: 0.75rem;
            }

            .card .row-data__value ul {
                padding-left: 16px;
            }

            .row-data__title {
                font-size: 0.75rem;
                font-weight: 600;
                line-height: 1.125rem;
                display: inline-flex;
                padding-right: 0.75rem;
            }

            .badge.badge_outline-warning.badge_sm {
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

            })()
        </script>
    @endpush
</x-core::layouts.main>
