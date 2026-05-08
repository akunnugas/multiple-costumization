<style>
    .sidebar_within {
        width: 290px !important;
        max-width: 290px !important;
    }
</style>

@foreach ($data as $section)
    @php
        $attributes = Page::buildAttributes(
            Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + [
                'data' => $section['items'],
            ],
        );

        $cards = $attributes['data'];

        $dataFiles = [];
    @endphp
    <div>
        <div class="content__header">
            <h3 class="content__title">
                {{ $attributes['title'] }}
            </h3>
        </div>

        <div class="content__body">
            @foreach ($cards as $card)
                @php
                    $isArray = false;
                    $isTextarea = false;
                    $isBadge = false;
                    
                    // Jika type hidden, maka tidak perlu ditampilkan
                    if (isset($card['type']) && $card['type'] === 'hidden') {
                        continue;
                    }

                    $label = $card['label'] ?? null;
                    if (empty($label) && !empty($card['field'])) {
                        $label = Page::defineLabelByField($card['field']);
                    }

                    $isFile = false;
                    if (isset($card['file_type'])) {
                        $isFile = true;
                        if (!empty($card['showSimpleFile'])) {
                            $simpleFile = Modules\DMS\Models\Dokumen::where('id', $card['original'] ?? null)->first();
                            $card['text'] = $simpleFile
                                ? $simpleFile->nama_dokumen . '.' . $simpleFile->extension_versi_terbaru
                                : null;
                        } else {
                            $dataFiles[] = $card;
                            continue;
                        }
                    }
                @endphp
                <div class="grid">
                    <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                        <label class="row-data__name">{{ $label }}</label>
                    </div>
                    <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                        <span class="row-data__value" style="font-size: 0.75rem;">
                            <span class="row-data__colon">:</span>
                            @if (isset($card['text']) && strpos($card['text'], '::::') !== false)
                                @php
                                    $arrData = explode('::::', $card['text']);
                                    $isArray = true;
                                @endphp
                            @else
                                @php
                                    $isTextarea = false;
                                    $isBadge = false;
                                    if(isset($card['badge'])) {
                                        $isBadge = true;
                                    }
                                @endphp
                                @if (isset($card['boolean']))
                                    <p>{{ isset($card['text']) ? 'Ya' : 'Tidak' }}</p>
                                @elseif (!empty($card['type']) && $card['type'] === 'date')
                                    <p>{{ !empty($card['text']) ? \Carbon\Carbon::parse($card['text'])->translatedFormat('d F Y') : null }}</p>
                                @elseif ($isFile)
                                    @php
                                        $ext = $simpleFile->extension_versi_terbaru;
                                        $ext = $ext == 'docx' ? 'doc' : $ext;
                                        $assetUrl = asset("images/$ext-solid.svg");
                                        $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                                    @endphp
                                    <p><a href="{{ $tempUrl }}" rel="noopener" style="color: #0F6AF5;" target="_blank"
                                        class="util_d-flex util_flex-center-vertical">
                                        <img height="20px;" src="{{ $assetUrl }}"
                                            alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                                        &nbsp; Lihat File
                                    </a></p>
                                @elseif ($isBadge)
                                    <span class="badge badge_outline-{{ $card['badge'][$card['original']] }} badge_sm">
                                        {{ $card['text'] }}
                                    </span>
                                @else
                                    @if (isset($card['control']) && $card['control'] == 'wysiwyg')
                                        @php
                                            $isTextarea = true;
                                        @endphp
                                    @else
                                        <p>{!! $card['text'] ?? null !!}</p>
                                    @endif
                                @endif
                            @endif
                        </span>
                    </div>
                    @if ($isTextarea)
                        <div class="col-12">
                            <p>{!! $card['text'] ?? null !!}</p>
                        </div>
                    @endif
                    <div class="col-12">
                        @if ($isArray ?? false)
                            @php
                                $isGrid = $card['grid'] ?? true;
                            @endphp
                            <x-core::layouts.detail.grid :data="$arrData" :grid="$isGrid" />
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Halaman Kustom --}}
            @if (!empty($section['page_conf']['custom_page']))
                <x-dynamic-component :component="$section['page_conf']['custom_page']" :data="$section['page_conf']['custom_page_data']" />
        </div>
    </div>
    @continue;
@endif

{{-- File Lampiran --}}
@if (!empty($dataFiles))
    <br />
    <br />
    <x-core::layouts.detail.files :data="$dataFiles" />
@endif
</div>
</div>
@endforeach
