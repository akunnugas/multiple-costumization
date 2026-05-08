@php
    $originalExt = $data['extension_versi_terbaru'];
    $ext = $originalExt;
    if ($ext == 'doc' || $ext == 'docx') {
        $ext = 'doc';
    }

    $assetUrl = asset("images/$ext-solid.svg");
@endphp
@if (!empty($value))
    <div style="display: flex; align-items: center; gap: 8px">
        <img width="24px" src="{{ $assetUrl }}">
        <p>{{ $value }}.{{ $originalExt }}</p>
    </div>
@endif
