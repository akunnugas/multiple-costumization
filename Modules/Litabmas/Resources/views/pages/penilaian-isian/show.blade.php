@php
    use Modules\Core\Helpers\Page;
    use Modules\Core\Helpers\Date;
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

    $primaryData = $data[0]['items'];
    $editUrl = \Modules\Core\Helpers\Page::buildURL(['edit' => true]);

    if ($isEdit === true) {
        $methodForm = 'PUT';
        $actionForm = route('litabmas.penilaian-isian.update', $resourceId);
    }

    $isReviewer = $rawData['is_reviewer']; // apakah yg di assign sebagai reviewer
    $sudahMasaReviewProposal = !empty($infoReviewProposal['sudah_masuk_masa_review_proposal']);
    $tanggalReview = Date::formatDateRange(
        $infoReviewProposal['waktu_mulai'],
        $infoReviewProposal['waktu_selesai'],
        isoFormatMonth: 'MMMM',
    );

    if (!$isReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak dapat memberikan feedback karena bukan reviewer dari proposal ini.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    } elseif (!$sudahMasaReviewProposal) {
        $staticAlert = [
            'message' =>
                'Anda tidak dapat memberikan feedback karena bukan dalam waktu review proposal.
                Anda dapat memberikan feedback pada tanggal ' .
                $tanggalReview .
                '.',
            'type' => 'warning',
            'dismissible' => false,
        ];
    } elseif ($sudahMasaReviewProposal && $isReviewer) {
        $staticAlert = [
            'message' => 'Anda dapat memberikan feedback pada proposal ini pada tanggal ' . $tanggalReview . '.',
            'type' => 'helper',
            'dismissible' => false,
        ];
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

    <x-core::form :action="$actionForm ?? null" :method="$methodForm ?? null" id="form-action">
        <x-litabmas::layouts.detail.card title="Berikan Feedback"
            customClassBody="util_d-flex util_flex-column util_gap-1"
            subtitle="Silakan berikan feedback berupa komentar untuk proposal dibawah ini">

            @if($isReviewer)
                <x-slot:action>
                    <div class="util_d-flex">
                        @if (!$isEdit)
                            @if ($sudahMasaReviewProposal && $isReviewer)
                                <x-core::button href="{{ $editUrl }}" size="sm" leading-icon="pencil-square-solid">
                                    Berikan Feedback
                                </x-core::button>
                            @else
                                <x-core::button href="#" disabled size="sm" leading-icon="pencil-square-solid">
                                    Berikan Feedback
                                </x-core::button>
                            @endif
                        @else
                            <x-core::button href="{{ route('litabmas.penilaian-isian.show', $resourceId) }}" size="sm"
                                variant="outline" class="util_mr-8">
                                Batalkan
                            </x-core::button>
                            <x-core::button type="submit" size="sm" form="form-action">
                                Simpan
                            </x-core::button>
                        @endif
                    </div>
                </x-slot:action>
            @endif

            <div class="col-12">
                <div class="grid cols-1">
                    @if ($isEdit && $isReviewer)
                        @foreach ($editFields as $item)
                            <div class="card">
                                <div class="card__body">
                                    <div class="form-control">
                                        <label for="form-control-{{ $item['field'] }}" class="form-control__label">
                                            <b>{{ $item['label'] }}</b><span class="important">*</span>
                                        </label>
                                    </div>
                                    <span class="ql-snow">
                                        <span class="ql-editor"
                                            style="display: flex; flex-direction: column; padding: 0;">
                                            {!! $item['isian_proposal'] !!}
                                        </span>
                                    </span>
                                    <div class="util_pl-8 util_pr-8">
                                        @php
                                            $item['name'] ??= $item['field'];
                                            unset($item['field'], $item['isian_proposal']);

                                            $attributes = Page::buildAttributes($item);
                                        @endphp
                                        <x-core::controls.form {{ $attributes }} :showLabel="false"
                                            class="util_pt-8" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-litabmas::pages.pengajuan-pendanaan.review-proposal.feedback-section :data="$dataIsianProposal" />
                    @endif
                </div>
            </div>
        </x-litabmas::layouts.detail.card>
    </x-core::form>

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
