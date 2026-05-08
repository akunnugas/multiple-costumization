@php
    use Modules\Core\Helpers\Format;
    use Modules\DMS\Services\DokumenManagementService;

    $maxSizeByte = Format::formatKbToBytes($attributes['max_size'] ?? 1024);
    $maxSizeKb = $attributes['max_size'];
    $maxSizeLabel = Format::formatBytes($maxSizeByte);

    if (!empty($attributes['file_type'])) {
        $accept = '.' . implode(',.', $attributes['file_type']);
        $acceptJsValidate = implode('|', $attributes['file_type']);

        $acceptDesc = '';
        foreach ($attributes['file_type'] as $key => $value) {
            if ($key > 0 && count($attributes['file_type']) - 1 == $key) {
                $acceptDesc .= 'atau ';
                $acceptDesc .= strtoupper($value);
            } else {
                $acceptDesc .= strtoupper($value) . (count($attributes['file_type']) - 2 == $key || count($attributes['file_type']) == 1 ? ' ' : ', ');
            }
        }
    }

    // multiple
    if (!empty($attributes['multiple'])) {
        $attributes['name'] = $attributes['name'] . '[]';
    }

    if ($attributes['value'] && empty($attributes['multiple'])) {
        $temporaryUrl = '#';
        if (is_int($attributes['value'])) {
            $document = (new DokumenManagementService())->show($attributes['value']);
            $temporaryUrl = $document->lastVersionTemporaryUrl();
        }
        $attributes['required'] = false;
        $attributes['value'] = null;
    }

    // image placement
    $allowedPlacement = ['top', 'bottom'];
    $imagePlacement = $attributes['image_placement'] ?? 'bottom';
    if (!in_array($imagePlacement, $allowedPlacement)) {
        $imagePlacement = 'bottom';
    }

    $dataCy = $attributes['data-cy'] ?? $attributes['name'] ?? null;
    if ($dataCy) {
        $attributes['data-cy'] = $dataCy;
    }

    // unset attribute yg tidak diperlukan di tag html
    unset($attributes['file_type'], $attributes['image_placement'], $attributes['max_size'], $attributes['options']);
@endphp

@if (isset($document) && $imagePlacement == 'top')
    <x-core::file-preview :id="$attributes['id']" :disabled="$attributes['disabled'] ?? false" :document="$document" :temporaryUrl="$temporaryUrl" :placement="'top'" />
@endif

@if (!(isset($attributes['disabled']) && $attributes['disabled']))
    <div class="upload-draggable upload-draggable_inline" wire:ignore.self>
        <div class="upload-draggable__box-inline" wire:ignore.self>
            <input type="file" class="upload-draggable__file-input" accept="{{ $accept ?? 'application/pdf,.doc' }}"
                onchange="fileValidation(this, '{{ $acceptJsValidate ?? 'pdf|doc' }}', {{ $maxSizeKb }})"
                {{ $attributes }}>
            <div class="upload-draggable__inline-wrapper" wire:ignore>
                <label class="upload-draggable__icon"><span class="icon icon-cloud-arrow-up"></span></label>
                <div class="upload-draggable__wrapper">
                    <h2 class="upload-draggable__title">Klik untuk pilih file <span class="upload-draggable__subtitle">atau
                            seret file ke sini</span></h2>
                    <p class="upload-draggable__support">{{ ($acceptDesc ?? 'PDF atau DOC') . " (Max. $maxSizeLabel)" }}</p>
                </div>
            </div>
        </div>
        <div class="upload-draggable__success" wire:ignore>
            Berhasil
        </div>
    </div>
@endif

@if (isset($document) && $imagePlacement == 'bottom')
    <x-core::file-preview :id="$attributes['id']" :disabled="$attributes['disabled'] ?? false" :remove="$attributes['remove'] ?? true" :document="$document" :temporaryUrl="$temporaryUrl" />
@endif

@if (isset($document))
    @pushOnce('scripts')
        <script>
            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault();
                let fileInput = document.querySelectorAll('input[type="file"]');
                let error = false;
                fileInput.forEach(function(element) {
                    // // jika tidak ada attribute required maka skip
                    // if (!element.hasAttribute('required')) {
                    //     return;
                    // }

                    const previewElement = document.getElementById(`${element.id}-preview`)
                    const value = element.value;
                    if (!previewElement && (value == null || value == '')) {
                        error = true;
                        alert('Mohon lengkapi file yang akan diunggah');
                    }
                });

                if (!error) {
                    this.submit();
                }
            });

            document.querySelector('.delete-file').addEventListener('click', function(e) {
                e.preventDefault();
                const {
                    parentElement
                } = this.parentElement.parentElement.parentElement;
                parentElement.remove();
            });
        </script>
    @endPushOnce
@endif
