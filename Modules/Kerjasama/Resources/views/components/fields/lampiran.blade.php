@php
    $ext = $data['extension_versi_terbaru'];
    if ($ext == 'doc' || $ext == 'docx') {
        $ext = 'doc';
    }

    $assetUrl = asset("images/$ext-solid.svg");
    if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
        $assetUrl = $data['lampiran'];
    }
@endphp

<div class="attachment" style="border: none">
    <div class="attachment__wrapper">
        <div class="attachment__wrapper-icon">
            <img src="{{ $assetUrl }}">
        </div>
        <div class="attachment__wrapper-text">
            <div class="attachment__title">
                <h3 class="attachment__heading">
                    {{ $data['nama_dokumen'] . '.' . $data['extension_versi_terbaru'] }}
                </h3>
                <span class="attachment__description">
                    {{ Format::formatBytes($data['ukuran']) }}
                </span>
            </div>
        </div>
        <div class="attachment__wrapper-action">
            <x-core::quantum-3.button 
                variant="outline-secondary" 
                size="sm" 
                leadingIcon="download-cloud" 
                :icon="true" 
                href="{!! $data['lampiran'] !!}"
                download
                target="_blank"
            />
        </div>
    </div>
</div>