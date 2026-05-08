@php
    if ($data['status_pengumuman'] == \Modules\Litabmas\Models\PengumumanPendanaan::STATUS_TERPUBLIKASI) {
        $variant = 'success';
    }else {
        $variant = 'default';
    }
@endphp
<x-core::badge :variant="$variant" type="outline" size="sm">
    {{ $value }}
</x-core::badge>
