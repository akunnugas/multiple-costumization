@php
    use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
    use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
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

    // handle $dataJadwalPresentasi
    if (!empty($sumberPendanaanMemilikiPresentasiProposal)) {
        $offlineField = 'link_presentasi_kegiatan';
        $onlineField = 'tempat_pelaksanaan';
        foreach ($dataJadwalPresentasi as $key => $item) {
            if ($item['field'] === 'tipe_kegiatan') {
                $tipeKegiatan = $item['original'];
                $fieldToUnset = $tipeKegiatan === PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE
                                ? $offlineField
                                : $onlineField;

                // Remove the respective field based on the type of activity
                foreach ($dataJadwalPresentasi as $innerKey => $value) {
                    if ($value['field'] === $fieldToUnset) {
                        unset($dataJadwalPresentasi[$innerKey]);
                        break;
                    }
                }
            }
        }
    } else {
        $staticAlert = [
            'message' => 'Sumber Pendanaan pada Proposal ini tidak menggunakan agenda presentasi proposal.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    }
@endphp

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$infoHeader[0]['items']"/>
        </div>
    </div>

    @if (!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert" />
    @endif

    <x-litabmas::layouts.detail.card title="Jadwal Presentasi Proposal" :data="$dataJadwalPresentasi"/>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Penilaian Presentasi Proposal">
        <div class="col-12" id="content-presentasi-proposal">
            <div class="grid cols-1">
                <x-core::table>
                    @php
                        $showAction = $showDetail = $isEditInline = false;
                        $canDelete = $canUpdate = $canCreate = false;
                        $showNumber = true;
                        $bobotPenilaianProposal = AspekPenilaianPresentasiProposal::getOptionsBobotPenilaianPresentasi();
                        $nilaiMemenuhiKriteriaProposal = AspekPenilaianPresentasiProposal::NILAI_MEMENUHI_KRITERIA;
                    @endphp
                    <x-core::table.data :header="$headerNilaiPresentasiProposal"
                                        :data="$rekapNilaiKomposisiProposal['data']"
                                        :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate
                                        :$showDetail :$showNumber :$isEditInline :resourceTitle="$title">
                        <x-slot:customRowInsideBody>
                            <tr class="custom-background-gray">
                                <td colspan="{{ count($headerNilaiPresentasiProposal) }}">
                                    <b>Total Nilai Keseluruhan</b>
                                </td>
                                <td class="util_text-right">
                                    <b>{{ $rekapNilaiKomposisiProposal['total_nilai_keseluruhan'] }}</b>
                                </td>
                            </tr>
                            <tr class="custom-background-gray">
                                <td colspan="{{ count($headerNilaiPresentasiProposal) }}">
                                    <b>Rata-Rata Nilai</b>
                                </td>
                                <td>
                                    <span class="util_d-flex util_flex-center-vertical util_flex-end">
                                        @if($rekapNilaiKomposisiProposal['rata_rata_nilai'] >= $nilaiMemenuhiKriteriaProposal)
                                            <span class="icon icon-check-circle-solid util_mr-4" height="20"></span>
                                        @else
                                            <span class="icon icon-x-circle-solid util_mr-4" height="20"></span>
                                        @endif
                                        <b>{{ $rekapNilaiKomposisiProposal['rata_rata_nilai'] }}</b>
                                    </span>
                                </td>
                            </tr>
                        </x-slot:customRowInsideBody>
                    </x-core::table.data>
                </x-core::table>

                <x-litabmas::layouts.detail.card-keterangan-penilaian-aspek :bobotPenilaian="$bobotPenilaianProposal"/>
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @pushonce('head')
        <style>
            .card .card__header .card__header-block .header__title {
                font-size: .875rem;
            }

            tr.custom-background-gray {
                background-color: #f8fafc;
            }

            #content-presentasi-proposal .box-table__content {
                padding: 0 !important;
                border-top: unset !important;
            }

            #content-presentasi-proposal .box-table__footer {
                padding: unset !important;
            }

            tr.custom-background-gray {
                background-color: #f8fafc;
            }

            table .icon.icon-x-circle-solid {
                color: var(--qn-danger);
                font-size: 1.5rem;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
