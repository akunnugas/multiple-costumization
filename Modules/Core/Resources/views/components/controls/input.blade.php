@props([
    'control' => null,
    'label' => null,
])
@php
    // ignore validation model, bisa bertambah sesuai kebutuhan
    $ignore = ['unique', 'validation'];
    $attributes = \Illuminate\Support\Arr::except($attributes->getAttributes(), $ignore) + [
        'name' => $attributes['name'] ?? $attributes['field'],
        'value' => $attributes['value'] ?? null,
    ];
    $attributes = Page::buildAttributes($attributes);

    // tidak dimasukkan props untuk diteruskan ke attributes
    $name = $attributes['name'] ?? null;
    $placeholder = $attributes['placeholder'] ?? null;
    $required = $attributes['required'] ?? false;
    $options = $attributes['options'] ?? null;
    $fileType = $attributes['file_type'] ?? null;
    $isArray = $attributes['is_array'] ?? false;
    $dataCy = $attributes['data-cy'] ?? $name;

    if ($isArray) { // replace name yg name.[index] menjadi name.index
        $tempName = preg_replace('/\[(\d+)\]/', '.$1', $name);
        unset($attributes['is_array']);
    }
    if ($dataCy) {
        $attributes['data-cy'] = $dataCy;
    }
    if (empty($label) && !empty($name)) {
        $label = Page::defineLabelByField($name, $urlInfo ?? null);
    }
    if (empty($placeholder) && !empty($label)) {
        $placeholder = 'Masukkan ' . $label;
    }
    if (!empty($placeholder) && empty($required)) {
        $placeholder .= ' (opsional)';
    }
    if (isset($fileType)) {
        $control = 'file';
    }

    // value
    $oldValue = ($isArray && !empty($tempName)) ? old($tempName) : old($name);
    $value = $attributes['value'] ?? ($oldValue ?? ($attributes['default'] ?? null));
    $attributes['value'] = $value;

    // dynamic component
    if (!empty($options) && !is_array($options)) {
        $dynamicComponent = Page::defineOptionComponent($options, true);

        if (empty($dynamicComponent)) {
            $options = $options::options();
            $attributes['options'] = $options;
        }
    }

    if (isset($attributes['component']) && isset($attributes['module']) && $attributes['component']) {
        $dynamicComponent = Page::defineDynamicComponent('forms', $attributes['name'], $attributes['module']);
    }

    // cek error
    if ($errors->isEmpty()) {
        $errors = Session::get('errors');
    }

    $forCheckName = ($isArray && !empty($tempName)) ? $tempName : $name;
    $isError = $errors?->has($forCheckName);

    // cek group
    $isGrouped = $control != 'radio' && $control != 'file' && $control != 'select';

    if (isset($fileType)) {
        $control = 'file';
        $attributes['type'] = 'file';
    }
@endphp
@if ($isGrouped)
    <div @class(['form-control__group', 'error' => $isError])>
@endif
@if (!empty($dynamicComponent))
    <x-dynamic-component :component="$dynamicComponent" :$label purpose="form" {{ $attributes }} />
@else
    @switch($control)
        @case('radio')
            <x-core::radio :selected="$value" {{ $attributes }} />
        @break

        @case('textarea')
            <x-core::textarea {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }}>
                {{ $value }}
            </x-core::textarea>
        @break

        @case('wysiwyg')
            <x-core::wysiwyg {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }}></x-core::wysiwyg>
        @break

        @case('checkbox')
            @if (isset($options))
                <x-core::checkbox-multiple
                    {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
            @else
                <x-core::checkbox {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
            @endif
        @break

        @case('switch')
            <x-core::switch {{ $attributes->merge(['id' => 'form-control-' . $name, 'label' => $label]) }} />
        @break

        @case('switch-invert')
            <x-core::switch-invert {{ $attributes->merge(['id' => 'form-control-' . $name, 'label' => $label]) }} />
        @break

        @case('datetime')
            <x-core::input type="datetime-local"
                {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
        @break

        @case('date')
            <x-core::input type="date"
                {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
        @break

        @case('select-multiple')
            <x-core::controls.select-multiple :$label purpose="form" {{ $attributes }} />
        @break

        @case('select-multiple-v2')
            <x-core::controls.select-multiple-v2 :$label purpose="form" {{ $attributes }} />
        @break

        @case('currency')
            <x-core::currency {{ $attributes->except(['type'])->merge([
                'type' => 'text',
                'id' => 'form-control-' . $name,
                'placeholder' => $placeholder,
            ]) }} />
        @break

        @default
            @if (isset($options) && !isset($fileType))
                <x-core::controls.select :$label purpose="form" {{ $attributes }} />
            @else
                @if ($attributes['type']  == 'date' && empty($attributes['defaultTypeDate']))
                    @php
                        $attributes['type'] = 'text';
                        $attributes['input-format'] = 'date';
                        $attributes['style'] = 'cursor: pointer';
                        $attributes['placeholder'] = 'DD/MM/YYYY';
                        $attributes['autocomplete'] = 'off';
                    @endphp
                @endif
                <x-core::input {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
                @if ($attributes['type'] == 'password')
                    <span data-visibility="input" data-type="password"></span>
                @endif
            @endif
    @endswitch
@endif
@if ($isGrouped)
    </div>
@endif
