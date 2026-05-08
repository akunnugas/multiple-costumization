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
    if ($isArray) { // replace name yg name.[index] menjadi name.index
        $tempName = preg_replace('/\[(\d+)\]/', '.$1', $name);
        unset($attributes['is_array']);
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
@if (!empty($dynamicComponent))
    <x-dynamic-component :component="$dynamicComponent" :$label purpose="form" {{ $attributes }} />
@else
    @switch($control)
        @case('radio')
            <x-core::quantum-3.radio :selected="$value" {{ $attributes }} />
        @break

        @case('textarea')
            <x-core::quantum-3.textarea {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }}>
                {{ $value }}
            </x-core::textarea>
        @break

        @case('wysiwyg')
            <x-core::quantum-3.wysiwyg {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }}>
                {{ $value }}
            </x-core::wysiwyg>
        @break

        @case('checkbox')
            @if (isset($options))
                <x-core::quantum-3.checkbox-multiple
                    {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
            @else
            <x-core::quantum-3.checkbox {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
            @endif
        @break

        @case('switch')
            <x-core::quantum-3.switch {{ $attributes->merge(['id' => 'form-control-' . $name, 'label' => $label]) }} />
        @break

        @case('datetime')
            <x-core::quantum-3.input type="datetime-local"
                {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} />
        @break

        @case('select-multiple')
            <x-core::quantum-3.controls.select-multiple :$label purpose="form" {{ $attributes }} />
        @break

        @case('file')
            <x-core::quantum-3.file {{  $attributes->merge(['id' => 'form-control-' . $name]) }} />
        @break

        @case('autocomplete')
            <x-core::quantum-3.autocomplete 
                {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder]) }} 
            />
        @break

        @default
            @if (isset($options) && !isset($fileType))
                <x-core::quantum-3.controls.select :$label purpose="form" {{ $attributes }} />
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
                <x-core::quantum-3.input {{ $attributes->merge(['id' => 'form-control-' . $name, 'placeholder' => $placeholder, 'class' => 'placeholder:text-gray-500']) }} />
                @if ($attributes['type'] == 'password')
                    <span data-visibility="input" data-type="password"></span>
                @endif
            @endif
    @endswitch
@endif