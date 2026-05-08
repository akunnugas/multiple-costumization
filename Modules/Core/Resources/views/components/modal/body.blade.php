@props([
    'footer' => null,
    'style' => [],
])
<div class="modal__body" @style($style)>
    {{ $slot }}
</div>
@if (!empty($footer))
    <div class="modal__footer">
        {{ $footer }}
    </div>
@endif
