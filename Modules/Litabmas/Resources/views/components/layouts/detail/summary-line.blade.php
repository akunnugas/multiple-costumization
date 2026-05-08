@props([
    'data' => [],
])
@php
    use Modules\Litabmas\Models\KlasterPendanaan;
    $isFirstKey = true;
    $kategoriKlaster = array_filter($data, function ($item) {
        return isset($item['field']) && $item['field'] === 'kategori_klaster';
    });

    $kategoriKlaster = reset($kategoriKlaster)['original'];
@endphp
@foreach ($data as $key => $item)
    @php
        $isFirstKey = $isFirstKey && $key === 0;
        $tableLayouts = !empty($item['table-layout']);

        $hidden = !empty($item['type']) && $item['type'] === 'hidden';
        if ($hidden) {
            continue;
        }

        if ($kategoriKlaster === KlasterPendanaan::KATEGORI_INDIVIDU) {
            if (isset($item['field']) && in_array($item['field'], ['anggota_dosen', 'anggota_mahasiswa'])) {
                continue;
            }
        } else {
            if (isset($item['field']) && in_array($item['field'], ['anggota_dosen', 'anggota_mahasiswa'])) {
                if (empty($item['original'])) {
                    continue;
                }
            }
        }

        $label = $item['label'] ?? null;
        if (empty($label) && !empty($item['field'])) {
            $label = Page::defineLabelByField($item['field']);
        }

        $simpleFile = null;
        if (!empty($item['showSimpleFile'])) {
            $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item['original'] ?? null)->first();
            // kalo nggk ada set text ke null biar bukan id yg tampil
            $item['text'] = $simpleFile ? $simpleFile->nama_dokumen . '.' . $simpleFile->extension_versi_terbaru : null;
        }

        if (!empty($item['prefix']) && !empty($item['text'])) {
            $item['text'] = $item['prefix'] . $item['text'];
        }

        if (!empty($item['suffix']) && !empty($item['text'])) {
            $item['text'] = $item['text'] . $item['suffix'];
        }

        // dynamic component
        $dynamicComponent = Page::defineFieldComponent($item, $urlInfo ?? null);
    @endphp
    @if (isset($item['separator']))
        <div @class([
            'col-12',
            !empty($item['class_break']) ? $item['class_break'] : null,
        ])>
            @if (!$isFirstKey)
                <hr class="dashed" style="margin-bottom: 1rem;">
            @endif
            <div class="util_d-flex">
                <h3>{{ $item['label'] }}</h3>
            </div>
        </div>
        @continue
    @endif
    @if (!$tableLayouts || empty($dynamicComponent))
        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
            <label class="row-data__name">{{ $label }}</label>
        </div>
        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
            <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                <span class="row-data__colon">:</span>
                @if (empty($dynamicComponent))
                    @if (!empty($simpleFile))
                        @php
                            $ext = $simpleFile->extension_versi_terbaru;
                            $ext = $ext == 'docx' ? 'doc' : $ext;
                            $assetUrl = asset("images/$ext-solid.svg");
                            $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                        @endphp
                        <a href="{{ $tempUrl }}" rel="noopener" target="_blank"
                            class="util_d-flex util_flex-center-vertical">
                            <img height="20px;" src="{{ $assetUrl }}"
                                alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                            &nbsp; Lihat File
                        </a>
                    @elseif (isset($item['boolean']))
                        {{ isset($item['text']) ? 'Ya' : 'Tidak' }}
                    @else
                        {!! $item['text'] ?? null !!}
                    @endif
                @else
                    <x-dynamic-component :component="$dynamicComponent" :isDetailLine="true" :$item />
                @endif
            </span>
        </div>
    @else
        <div class="col-12">
            <x-dynamic-component :component="$dynamicComponent" :isDetailLine="true" :$item />
        </div>
    @endif
@endforeach
