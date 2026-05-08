@props([
    'title' => null,
    'variant' => null,
    'visible' => false,
    'width' => null,
    'modalDialogClass' => null,
])


<div {{ $attributes->class(['modal fade']) }} tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div @class(['modal-dialog', $modalDialogClass])>
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal__header-wrapper">
                    <h5 class="modal-title">{{ $title }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
