@php
    if ($value === true) {
        $variant = 'success';
        $value = 'Dibuka';
    } elseif ($value === false) {
        $variant = 'default';
        $value = 'Draft';
    }
@endphp

@if(!empty($value))
    <x-core::badge :variant="$variant" type="outline" size="sm">
        {{ $value }}
    </x-core::badge>
@endif
