@php
    $isDokumen = isset($data['extension_versi_terbaru']);

    if ($isDokumen) {
        $ext = $data['extension_versi_terbaru'];
        if ($ext == 'doc' || $ext == 'docx') {
            $ext = 'doc';
        }

        $assetUrl = asset("images/$ext-solid.svg");
        $url = route('dms.files.preview', $data['slug']);
        $value = $data['nama_dokumen'] . '.' . $data['extension_versi_terbaru'];
    } else {
        $url = route('dms.folder.show', $data['kode_folder']);
        $assetUrl = asset("images/folder-solid.svg");
    }
@endphp

<div class="name-field">
    <img width="24px" src="{{ $assetUrl }}">

    <div>
        @if ($raw ?? false)
            {{$value}}
        @else
            <a href="{{ $url }}">{{ $value }}</a>
        @endif

        @if (isset($data['nama_folder']))
            <div class="name-field__folder">
                {{ $data['nama_folder'] }}
            </div>
        @endif
    </div>
</div>
