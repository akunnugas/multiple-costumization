@php
    $simpleFile = null;
    if (!empty($item['original'])) {
        $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item['original'])->first();
    }
@endphp
@if ($simpleFile)
    @php
        $ext = $simpleFile->extension_versi_terbaru;
        $ext = $ext == 'docx' ? 'doc' : $ext;
        $assetUrl = asset("images/$ext-solid.svg");
        $tempUrl = $simpleFile->lastVersionTemporaryUrl();
    @endphp
    <a href="{{ $tempUrl }}" rel="noopener" target="_blank"
        class="util_d-flex util_flex-center-vertical" style="color: #0F6AF5; gap: 10px;">
        <img height="20px;" src="{{ $assetUrl }}"
            alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
        {{ $simpleFile['nama_dokumen'] }}
    </a>
@else
    -
@endif
