@props([
    'footer' => null,
    'style' => [],
])

<div class="modal-body" @style($style)>
    {{ $slot }}
</div>
@if (!empty($footer))
    <div class="modal-footer">
        {{ $footer }}
    </div>
@endif

