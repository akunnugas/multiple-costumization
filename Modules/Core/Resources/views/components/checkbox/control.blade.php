@props([
    'checked' => false,
    'disabled' => false,
    'label' => null,
])

<div class="flex items-center justify-center gap-2">
    <input
        type="checkbox"
        {{ $attributes->class(['form-control__checkbox']) }}
        @checked($checked)
        @disabled($disabled)
    >
    @if (!empty($label))
        <label
            class="form-control__checkbox-label text-center"
            @if (!empty($attributes['id'])) for="{{ $attributes['id'] }}" @endif
        >
            {{ $label }}
        </label>
    @endif
</div>

