@props([
    'checked' => false,
    'description' => null,
    'disabled' => false,
    'choiceLabel' => null,
    'label' => null,
    'size' => null,
    'isValueHtml' => false
])

@php
    $id = $attributes['id'] ?? null;
    $name = $attributes['name'] ?? null;
    $dataCy = $attributes['data-cy'] ?? $name ?? null;

    if ($dataCy) {
        $attributes['data-cy'] = $dataCy;
    }

    if (empty($label) && !empty($name)) {
        $label = Page::defineLabelByField($name);
    }

    $label = $choiceLabel ?? $label;
@endphp

<div @class(['checkbox', 'checkbox-' . $size => $size])>
    <x-core::checkbox.control {{ $attributes }} :$checked :$disabled />
    @empty($description)
        <x-core::checkbox.label :id="$id" :$disabled>
            @if($isValueHtml)
                {!! $label !!}
            @else
                {{ $label }}
            @endif
        </x-core::checkbox.label>
    @else
        <div class="form-control__label">
            <x-core::checkbox.label :id="$id" :$disabled>
                {{ $label }}
            </x-core::checkbox.label>
            <span class="form-control__label-desc">{{ $description }}</span>
        </div>
    @endempty
</div>
