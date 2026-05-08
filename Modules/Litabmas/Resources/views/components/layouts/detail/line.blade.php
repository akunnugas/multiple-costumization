@props([
    'data' => [],
    'customPage' => null,
    'customPageData' => [],
])
@php
    $dataFiles = [];
    $dataForComponent = [];
    foreach ($data as $key => $item) {
        $dataForComponent[$item['field']] = $item['original'] ?? null;
    }
@endphp
@foreach ($data as $key => $item)
    @php
        $hidden = !empty($item['type']) && $item['type'] === 'hidden';
        if ($hidden) {
            continue;
        }

        $label = $item['label'] ?? null;
        if (empty($label) && !empty($item['field'])) {
            $label = Page::defineLabelByField($item['field']);
        }

        $simpleFile = null;
        if (!empty($item['showSimpleFile'])) {
            $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item['original'] ?? null)->first();
            // kalo nggk ada set text ke null biar bukan id yg tampil
            $item['text'] = $simpleFile ? ($simpleFile->nama_dokumen . '.' . $simpleFile->extension_versi_terbaru) : null;
        } elseif (isset($item['file_type'])) {
            $dataFiles[] = $item;
            continue;
        }

        if (!empty($item['prefix']) && !empty($item['text'])) {
            $item['text'] = $item['prefix'] . $item['text'];
        }

        if (!empty($item['suffix']) && !empty($item['text'])) {
            $item['text'] = $item['text'] . $item['suffix'];
        }

        // dynamic component
        $dynamicComponent = Page::defineFieldComponent(($item), $urlInfo ?? null);
    @endphp
    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
        <label class="row-data__name">{{ $label }}</label>
    </div>
    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
        <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
            <span class="row-data__colon">:</span>
            @if(empty($dynamicComponent))
                @if(!empty($simpleFile))
                    @php
                        $ext = $simpleFile->extension_versi_terbaru;
                        $ext = ($ext == 'docx') ? 'doc' : $ext;
                        $assetUrl = asset("images/$ext-solid.svg");
                        $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                    @endphp
                    <a href="{{ $tempUrl }}" rel="noopener" target="_blank" class="util_d-flex util_flex-center-vertical">
                        <img height="20px;" src="{{ $assetUrl }}" alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                        &nbsp; Lihat File
                    </a>
                @elseif (isset($item['boolean']))
                    {{ isset($item['text']) ? 'Ya' : 'Tidak' }}
                @else
                    {!! $item['text'] ?? null !!}
                @endif
            @else
                <x-dynamic-component :component="$dynamicComponent" :isDetailLine="true" :$item
                    :data="$dataForComponent ?? []"/>
            @endif
        </span>
    </div>
@endforeach

@if (isset($customPage) && !empty($customPage))
    <x-dynamic-component :component="$customPage" :data="$customPageData" />
@endif

@if (!empty($dataFiles))
    <br />
    <x-core::layouts.detail.files :data="$dataFiles" />
@endif

@pushonce('head')
    <style>
        .card.card_details-primary:has(.badge) .col-lg-3 {
            margin-left: unset !important;
        }
        .card.card_details-primary:has(.badge) .badge {
            position: unset !important;
        }
        .card.card_details-primary .badge.badge_outline-primary {
            padding: var(--qn-badge-padding-size);
        }
    </style>
@endpushonce
