@props([
    'label' => null,
    'isEmpty' => false,
    'placeholder' => null,
    'options' => [],
    'purpose' => null,
    'value' => null,
    'disabled' => false,
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

    $dataCy = $attributes['data-cy'] ?? $name ?? null;
    if ($dataCy) {
        $attributes['data-cy'] = $dataCy;
    }
@endphp
<x-core::select :$options :selected="$value" {{ $attributes }} :disabled="$disabled" />
