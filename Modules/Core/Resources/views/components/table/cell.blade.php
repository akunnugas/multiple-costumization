@props([
    'data' => [],
    'header' => [],
])
@php
    $field = $header['name'] ?? ($header['field'] ?? null);

    // Pengecekan jika field tidak ada, maka return null
    if (!empty($field)) {
        $value = $data[$field] ?? null;
    } else {
        $value = null;
    }

    // options
    $options = $header['options'] ?? null;

    // options diambil dari model
    if (!empty($options) && !is_array($options)) {
        $value = $options::optionValue($value);
    }

    // get value by options
    if (!empty($options) && is_array($options)) {
        $value = $options[$value] ?? $value;
    }

    // dynamic component
    $dynamicComponent = Page::defineFieldComponent(($tempHeader ?? $header), $urlInfo ?? null);
    unset($tempHeader);

    $isTreeView = false;
    if (!empty($header['is_tree_view'])) {
        $isTreeView = true;
        $value = str_repeat('&nbsp', $data['info_level'] * 4) . ' ' . $value;
    }

    $align = 'left';
    if (!empty($header['styleAlign'])) {
        $align = $header['styleAlign'];
    }
@endphp
<td rowspan="{{ $header['_rowspan'] ?? 1 }}" class="util_text-{{ $align }}">
    @if (!empty($dynamicComponent))
        <x-dynamic-component :component="$dynamicComponent" :$value :$data :$field :$header />
    @elseif ($isTreeView)
        {!! $value !!}
    @else
        {{ $value }}
    @endif
</td>
