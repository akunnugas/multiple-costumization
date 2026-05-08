@props([
    'title' => null,
    'variant' => null,
    'visible' => false,
    'width' => null,
])


<div {{ $attributes->class(['modal', 'modal_confirmation-' . $variant => $variant, 'is-visible' => $visible]) }}>
    <div class="modal__overlay" data-dismiss="modal"></div>
    <div class="modal__wrapper" style="{{ $width }}">
        <div class="modal__header">
            <div class="modal__header-wrapper">
                <h3 class="modal__title">{{ $title }}</h3>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
        </div>
        {{ $slot }}
    </div>
</div>
