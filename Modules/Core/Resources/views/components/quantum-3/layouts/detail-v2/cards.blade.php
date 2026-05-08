@foreach ($data as $section)
    @php
        $attributes = Page::buildAttributes(
            Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + [
                'data' => $section['items'],
            ],
        );

        $cards = $attributes['data'];
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
                    // Jika type hidden, maka tidak perlu ditampilkan
                    if (isset($card['type']) && $card['type'] === 'hidden') {
                        continue;
                    }

                    $label = $card['label'] ?? null;
                    if (empty($label) && !empty($card['field'])) {
                        $label = Page::defineLabelByField($card['field']);
                    }

                    $dataFiles = [];
                    if (isset($card['file_type'])) {
                        $dataFiles[] = $card;
                        continue;
                    }
                @endphp
                <div class="grid">
                    <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                        <label class="row-data__name">{{ $label }}</label>
                    </div>
                    <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                        <span class="row-data__value">
                            <span class="row-data__colon">:</span>
                            @if (isset($card['text']) && strpos($card['text'], '::::') !== false)
                                @php
                                    $arrData = explode('::::', $card['text']);
                                    $isArray = true;
                                @endphp
                            @else
                                @if (isset($card['boolean']))
                                    {{ isset($card['text']) ? 'Ya' : 'Tidak' }}
                                @elseif (!empty($card['type']) && $card['type'] === 'date')
                                    {{ !empty($card['text']) ? \Carbon\Carbon::parse($card['text'])->translatedFormat('d F Y') : null}}
                                @else
                                    {!! $card['text'] ?? null !!}
                                @endif
                            @endif
                        </span>
                    </div>
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
            @if (!empty($section['page_conf']['custom_page']))
                <x-dynamic-component :component="$section['page_conf']['custom_page']" :data="$section['page_conf']['custom_page_data']" />

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
