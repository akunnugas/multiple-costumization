@php
    if ($data['status_usulan'] == \Modules\Litabmas\Models\DosenEksternal::STATUS_BERHASIL_DIBUAT) {
        $variant = 'success';
    }else {
        $variant = 'warning';
    }
@endphp
<x-core::badge :variant="$variant" type="outline" size="sm">
    {{ $value }}
</x-core::badge>
