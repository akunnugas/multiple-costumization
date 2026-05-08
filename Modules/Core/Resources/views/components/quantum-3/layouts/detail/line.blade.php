@props([
    'data' => [],
    'customPage' => null,
    'customPageData' => [],
])
@php
    use Carbon\Carbon;

    $isArray = false;
    $dataFiles = [];
@endphp
@foreach ($data as $key => $item)
    @php
        if (!empty($item['defaultTypeDate'])) {
            $item['text'] = Carbon::parse($item['original'])->translatedFormat($item['defaultTypeDate']);
        }

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

        $dynamicComponent = null;
        if (!empty($item['dynamicComponent'])) {
            $dynamicComponent = $item['dynamicComponent'];
        }
    @endphp
    <div class="col-4">
        <div class="d-flex gap-1 justify-content-between">
            <span class="text-secondary">{{ $label }}</span>
            <span>:&nbsp;</span>
        </div>
    </div>
    <div class="col-8">
        @if (isset($item['text']) && strpos($item['text'], '::::') !== false)
            @php
                $arrData = explode('::::', $item['text']);
            @endphp
            <x-core::quantum-3.layouts.detail.grid :data="$arrData" :grid="false" />
        @else
            <span>
                @if(!empty($simpleFile))
                    @php
                        $ext = $simpleFile->extension_versi_terbaru;
                        $ext = ($ext == 'docx') ? 'doc' : $ext;
                        $assetUrl = asset("images/$ext-solid.svg");
                        $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                    @endphp
                    <a href="{{ $tempUrl }}" rel="noopener" target="_blank">
                        <div class="d-flex align-items-center">
                            <img height="18px;" src="{{ $assetUrl }}" alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                            <span class="ps-1">
                                {{ $simpleFile->nama_dokumen }}.{{ $ext }}
                            </span>
                        </div>
                    </a>
                @elseif (!empty($dynamicComponent))
                    <x-dynamic-component :component="$dynamicComponent" :data="$item" />
                @elseif (isset($item['boolean']))
                    {{ isset($item['text']) ? 'Ya' : 'Tidak' }}
                @else
                    {!! $item['text'] ?? null !!}
                @endif
            </span>
        @endif
    </div>
    {{-- @if (isset($item['separator']))
        <div class="util_d-flex util_mt-40">
            <h3>{{ $item['label'] }}</h3>
        </div>
        <hr class="linebreak">
    @else
        <div class="row-data util_mt-15">
            <label class="row-data__name" style="font-size: 0.8rem !important">{{ $label }}</label>
            @if (isset($item['text']) && strpos($item['text'], '::::') !== false)
                @php
                    $arrData = explode('::::', $item['text']);
                    $isArray = true;
                @endphp
            @else
                <span class="row-data__value" style="font-size: 0.8rem !important">
                    <span class="row-data__colon">:&nbsp;</span>
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
                </span>
            @endif
        </div>

        @if (isset($item['field']))
            @if (count($data) - 1 !== $key)
                <hr class="linebreak">
            @endif
        @else
            <br />
        @endif
    @endif --}}
@endforeach

@if (isset($customPage) && !empty($customPage))
    <x-dynamic-component :component="$customPage" :data="$customPageData" />
@endif

@if (!empty($dataFiles))
    <br />
    <x-core::quantum-3.layouts.detail.files :data="$dataFiles" />
@endif
