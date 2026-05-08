@props([
    'disabled' => $disabled,
    'id' => $id,
])
<label @if (!empty($id)) for="{{ $id }}" @endif @class([
    'form-control__label-checkbox',
    'label-checkbox_disabled' => $disabled,
])>
    {{ $slot }}
</label>
