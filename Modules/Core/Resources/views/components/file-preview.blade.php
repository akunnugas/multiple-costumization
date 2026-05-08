@props([
    'id',
    'document',
    'temporaryUrl',
    'placement' => 'bottom',
    'disabled' => false,
    'remove' => true
])

<div class="dl-item" style="display: flex; flex-direction: column;
    @if (!$disabled) margin-{{ $placement == 'top' ? 'bottom' : 'top' }}: 18px @endif" id="{{ $id }}-preview">
    <div class="attachment attachment_loading">
        <div class="attachment__wrapper">
            <div class="attachment__wrapper-icon">
                @php
                    $ext = $document->extension_versi_terbaru;
                    if ($ext == 'doc' || $ext == 'docx') {
                        $ext = 'doc';
                    }

                    $assetUrl = asset("images/$ext-solid.svg");
                    if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
                        $assetUrl = $document->lastVersionTemporaryUrl();
                    }
                @endphp
                <img src="{{ $assetUrl }}">
            </div>
            <div class="attachment__wrapper-text">
                <div class="attachment__title">
                    <h3 class="attachment__heading util_pb-0">
                        {{ $document->nama_dokumen . '.' . $document->extension_versi_terbaru }}
                    </h3>
                    <span
                        class="attachment__description">{{ Format::formatBytes($document->last_version_size) }}</span>
                </div>
            </div>
            <div class="attachment__wrapper-action">
                <a class="btn btn_icon btn_outline btn_xs" href="{{ $temporaryUrl }}" download="foo"
                   target="_blank">
                    <span class="icon icon-eye"></span>
                </a>
                @if (!$disabled && $remove)
                    <button type="button" class="delete-file btn btn_icon btn_outline btn_xs" data-toggle="modal"
                            data-target="#danger-confirmation">
                        <span class="icon icon-trash"></span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
