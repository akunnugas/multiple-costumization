@props([
    'inline' => true,
    'options' => [],
    'selected' => null,
])

@php
    //KALO MAU SET DEFAULT VALUE DARI LIVEWIRE HARUS SET RECORD DI FORMNYA
    $dataCy = $attributes->get('data-cy') ?? $attributes->get('name') ?? null;
@endphp

<div class="radio-button-wrapper" data-cy="radio__{{ $dataCy }}">
    @foreach ($options as $value => $label)
        @php($id = $attributes['name'] . '_' . strtolower($value))
        <div @class(['radio-button' . ($inline ? '-inline' : '')])>
            <input type="radio" class="form-control__radio" id="{{ $id }}" value="{{ $value }}" @checked($value == $selected) {{ $attributes }}>
            <label for="{{ $id }}" class="form-control__label-radio">{{ $label }}</label>
        </div>
    @endforeach
</div>
