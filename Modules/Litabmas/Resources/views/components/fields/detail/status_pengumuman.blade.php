@php
    $value = $item['text'] ?? '';
    if (!empty($item['original']) && $item['original'] == \Modules\Litabmas\Models\PengumumanPendanaan::STATUS_TERPUBLIKASI) {
        $variant = 'success';
    }else {
        $variant = 'default';
    }
@endphp
<x-core::badge :variant="$variant" type="secondary" size="sm">
    {{ $value }}
</x-core::badge>
