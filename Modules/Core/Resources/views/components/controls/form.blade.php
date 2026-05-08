@props([
    'showLabel' => true,
    'showHelper' => true,
    'class' => null,
])
@php
    // tidak menggunakan props untuk meneruskan attributes
    $label = $attributes['label'] ?? null;
    $name = $attributes['name'] ?? null;
    $required = $attributes['required'] ?? false;
    $isHidden = $attributes['type'] === 'hidden';
    $helper = $attributes['helper'] ?? null;
    $isHelperHtml = $attributes['is_helper_html'] ?? false;
    $isArray = $attributes['is_array'] ?? false;

    // label
    if ($showLabel && empty($label) && !empty($name)) {
        $label = Page::defineLabelByField($name, $urlInfo ?? null);
    }

    // cek error
    if ($errors->isEmpty()) {
        $errors = Session::get('errors');
    }

    if ($isArray) { // pecah name yg name.[index] menjadi name.index
        $name = preg_replace('/\[(\d+)\]/', '.$1', $name);
        $attributes['is_array'] = true;
    }

    $isError = $errors?->has($name);
    if ($isError) {
        $helper = $errors->first($name);
    }
@endphp
<div class="form-control {{ $class ?? null }}" @style([
    'display: none' => $isHidden,
])>
    @if ($showLabel)
        <label for="form-control-{{ $name }}" class="form-control__label">
            {{ $label }}
            @if ($required)
                <span class="important">*</span>
            @endif
        </label>
    @endif
    @if ($slot->isEmpty())
        <x-core::controls.input {{ $attributes }} />
    @else
        {{ $slot }}
    @endif
    @if($showHelper)
        <div @class(['form-control__helper', 'error' => $isError])>
            @if(!$isHelperHtml)
                <div>
                    {!! $helper ?? null !!}
                </div>
            @else
                {!! strip_tags($helper ?? null, '<a>') !!}
            @endif
        </div>
    @endif
</div>
