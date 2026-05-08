@props([
    'showLabel' => true,
    'showHelper' => true,
    'class' => null,
    'column' => 12
])
@php
    $label = $attributes['label'] ?? null;
    $name = $attributes['name'] ?? null;
    $required = $attributes['required'] ?? false;
    $isHidden = $attributes['type'] === 'hidden';
    $helper = $attributes['helper'] ?? null;
    $isArray = $attributes['is_array'] ?? false;

    if ($showLabel && empty($label) && !empty($name)) {
        $label = Page::defineLabelByField($name, $urlInfo ?? null);
    }

    if ($errors->isEmpty()) {
        $errors = Session::get('errors');
    }

    if ($isArray) {
        $name = preg_replace('/\[(\d+)\]/', '.$1', $name);
        $attributes['is_array'] = true;
    }

    $isError = $errors?->has($name);
    if ($isError) {
        $helper = $errors->first($name);
    }
@endphp

<div class="form-group col-md-{{ $column }} {{ $class ?? '' }} {{ $isHidden ? 'd-none' : '' }}">
    @if ($showLabel)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
        <br/>
    @endif
    @if ($slot->isEmpty())
        <x-core::quantum-3.controls.input {{ $attributes->class(['is-invalid' => $isError]) }} />
    @else
        {{ $slot }}
    @endif
    @if($showHelper && $isError)
        <div class="invalid-feedback d-block">
            {{ $helper }}
        </div>
    @elseif($showHelper && $helper)
        <div class="form-text">
            {{ $helper }}
        </div>
    @endif
</div>
