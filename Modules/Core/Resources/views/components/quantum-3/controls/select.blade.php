@props([
    'label' => null,
    'isEmpty' => false,
    'placeholder' => null,
    'options' => [],
    'purpose' => null,
    'value' => null,
])
@php
    if (!$isEmpty) {
        $optionEmpty = ['' => 'Pilih ' . $label];
        $optionNotEmpty = ['' => 'Semua ' . $label];

        if ($purpose == 'form') {
            $options = $placeholder ? ['' => $placeholder] + $options : $optionEmpty + $options;
        } elseif ($purpose == 'filter') {
            $options = $placeholder ? ['' => $placeholder] + $options : $optionNotEmpty + $options;
        }
    }
@endphp
<x-core::quantum-3.select :$options :selected="$value" {{ $attributes }} />
