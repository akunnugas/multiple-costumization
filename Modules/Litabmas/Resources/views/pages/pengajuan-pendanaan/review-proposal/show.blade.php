@php use Modules\Core\Helpers\Date; @endphp
@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
    'isPeneliti' => false,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    // handle alert
    $tidakLolosNominasi = !empty($infoRevisiProposal['tidak_lolos_nominasi']);
    $tidakAdaAgendaPresentasi = !empty($infoRevisiProposal['tidak_ada_presentasi_proposal']);
    $sudahMasukMasaRevisi = !empty($infoRevisiProposal['sudah_masuk_masa_revisi_proposal']);
    $sudahSampaiDanLewatMasaRevisi = !empty($infoRevisiProposal['sudah_sampai_dan_melewati_masa_revisi_proposal']);

    if ($tidakLolosNominasi) { // tidak lolos nominasi
        $staticAlert = [
            'message' => 'Anda tidak bisa melakukan perbaikan proposal karena anda tidak lolos tahap selanjutnya.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif ($tidakAdaAgendaPresentasi) { // dari klaster tidak ada jadwal agenda presentasi (karena memang opsional)
        if ($sudahSampaiDanLewatMasaRevisi) { // sudah melewati masa revisi
            $staticAlert = [
                'message' => 'Anda sudah dinyatakan lolos pendanaan, tidak perlu melakukan upload perbaikan proposal.',
                'type' => 'helper',
                'dismissible' => false
            ];
        } elseif (!empty($infoRevisiProposal['waktu_mulai'])) { // belum menyentuh tgl awal revisi
            $tanggal = Date::formatDate($infoRevisiProposal['waktu_mulai']);
            $staticAlert = [
                'message' => 'Anda hanya dapat melakukan perbaikan pada tanggal ' . $tanggal . '.',
                'type' => 'warning',
                'dismissible' => false
            ];
        }
    } elseif ($sudahMasukMasaRevisi) { // ada agenda presentasi & sudah masuk masa revisi
        $staticAlert = [
            'type' => 'helper',
            'message' => 'Silahkan unggah perbaikan proposal berdasarkan feedback reviewer
                <a href="#feedback-reviewer" class="link">di sini</a>.',
            'isHtml' => true,
        ];
    } elseif (!empty($infoRevisiProposal['waktu_mulai']) && !empty($infoRevisiProposal['waktu_selesai'])) {
        // tidak masuk dalam range waktu revisi proposal
        $tanggalRevisi = Date::formatDateRange($infoRevisiProposal['waktu_mulai'], $infoRevisiProposal['waktu_selesai'], isoFormatMonth: 'MMMM');
        $staticAlert = [
            'message' => 'Anda tidak dapat melakukan perbaikan proposal karena bukan dalam waktu revisi proposal.
                Anda hanya dapat melakukan perbaikan pada tanggal ' . $tanggalRevisi . '.',
            'type' => 'warning',
            'dismissible' => false
        ];
    }

    $canUpdate = true;
    if ($tidakLolosNominasi || !$sudahMasukMasaRevisi) {
        $canUpdate = false;
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

    @if(!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert"/>
    @endif

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1">
        <div class="col-12">
            <div class="grid cols-1">
                <x-litabmas::pages.pengajuan-pendanaan.review-proposal.feedback-section :data="$feedbackIsianProposal"
                                                                                        :$isPeneliti />
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card title="Perbaikan Proposal"
                                     subtitle="Silahkan Unggah berkas perbaikan proposal penelitian Anda berdasarkan feedback reviewer"
                                     customClassBody="util_d-flex util_flex-column util_gap-1">
        <x-slot:action>
            <div class="util_d-flex">
                {{-- tampilan bimbingan tidak ada aksi--}}
                @if(empty($isFromBimbingan))
                    @if($canUpdate)
                        <x-core::button href="#" leading-icon="arrow-up-tray-solid" size="sm" data-toggle="modal" class="util_mr-12"
                                        data-target="#modal-upload-revisian-proposal">
                            Unggah Proposal
                        </x-core::button>
                    @else
                        <x-core::button href="#" leading-icon="arrow-up-tray-solid" size="sm" class="util_mr-12" disabled>
                            Unggah Proposal
                        </x-core::button>
                    @endif
                @endif
            </div>
        </x-slot:action>

        <div class="box-table__content" id="feedback-reviewer">
            <x-core::table>
                @php
                    $showCheck = $showDetail = $canCreate = false;
                    $create = $edit = $isReference = $isEditInline = false;
                    $showNumber = true;
                    $canDelete = $canUpdate;
                    $showAction = empty($isFromBimbingan); // tampilan bimbingan tidak ada aksi
                @endphp
                <x-core::table.data :header="$headerRevisi" :data="$daftarRevisiProposal" :paginateInfo="$data"
                                    :$edit :sortable="false" :sort="null" :sortDesc="null" :$showAction
                                    :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate
                                    :$showCheck :$showDetail :$showNumber :$isEditInline :resourceTitle="$title"/>
            </x-core::table>
        </div>
    </x-litabmas::layouts.detail.card>

    @if(empty($isFromBimbingan))
        <x-litabmas::pages.pengajuan-pendanaan.review-proposal.modal-show-page/>
    @endif

    @pushOnce('headVendor')
        <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
    @endPushOnce

    @pushonce('head')
        <style>
            .box-table__content#feedback-reviewer {
                border-top: none;
            }
            .box-table__content#feedback-reviewer .box-table__content {
                border-top: none;
                padding: 0;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
