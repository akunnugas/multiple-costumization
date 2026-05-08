@props([
    'options' => [],
    'disabled' => [],
])

@php
    $id = $attributes['id'] ?? null;
    $name = ($attributes['name'] ?? null) . '[]';
    if (empty($label) && !empty($name)) {
        $label = Page::defineLabelByField($name);
    }
    $attributes['name'] = $name;
    $values = $attributes['value'] ?? [];
    $wireModel = $attributes['wire:model'];
    $isValueHtml = $attributes['isValueHtml'] ?? false;
    unset($attributes['selected']);
@endphp

<div class="grid">
    @foreach ($options as $key => $option)
        @php
            $attributes['disabled'] = false;
            $attributes['id'] = $id . '_' . $key;
            $attributes['value'] = $key;
            $attributes['checked'] = false;

            // Jika selected
            if (is_array($values)) {
                $attributes['checked'] = in_array($key, $values);
            }

            // Jika disabled
            if (in_array($key, $disabled)) {
                $attributes['disabled'] = true;
            }

            // Jika livewire
            if (isset($wireModel)) {
                $attributes['wire:model'] = $wireModel . '.' . $key;
            }

            $attributes['isValueHtml'] = $isValueHtml;
        @endphp
        <div class="col-6">
            <x-core::checkbox {{ $attributes }} :label='$option' />
        </div>
    @endforeach
</div>
