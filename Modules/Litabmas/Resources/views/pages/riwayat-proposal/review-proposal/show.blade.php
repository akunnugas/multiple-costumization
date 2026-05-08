@php use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal; @endphp
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

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1" title="Informasi Reviewer">
        <div class="col-12">
            <div class="util_d-flex">
                @foreach($reviewers as $reviewerKe => $reviewer)
                    <span class="util_mr-12 nama-reviewer">
                        Reviewer {{ $reviewer['reviewer_ke'] }}
                        &nbsp;
                        ({{ $reviewer['nip_nama'] }})
                    </span>
                @endforeach
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Penilaian Aspek Proposal">
        <div class="col-12" id="content-komposisi-proposal">
            <div class="grid cols-1">
                <x-core::table>
                    @php
                        $showAction = $showDetail = $isEditInline = false;
                        $canDelete = $canUpdate = $canCreate = false;
                        $showNumber = true;
                        $bobotPenilaianProposal = AspekPenilaianKomposisiProposal::OPTION_SKALA_BOBOT;
                        $nilaiMemenuhiKriteriaProposal = AspekPenilaianKomposisiProposal::NILAI_MEMENUHI_KRITERIA;
                    @endphp
                    <x-core::table.data :header="$headerNilaiKomposisiProposal" :data="$rekapNilaiKomposisiProposal['data']"
                                        :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate
                                        :$showDetail :$showNumber :$isEditInline :resourceTitle="$title">
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

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1" title="Feedback Reviewer">
        <div class="col-12">
            <div class="grid cols-1">
                <x-litabmas::pages.pengajuan-pendanaan.review-proposal.feedback-section :data="$feedbackIsianProposal" />
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @pushOnce('headVendor')
        <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
    @endPushOnce

    @pushonce('head')
        <style>
            .card .card__header .card__header-block .header__title {
                font-size: .875rem;
            }
            tr.custom-background-gray {
                background-color: #f8fafc;
            }
            #content-komposisi-proposal .box-table__content {
                padding: 0 !important;
                border-top: unset !important;
            }
            #content-komposisi-proposal .box-table__footer {
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
