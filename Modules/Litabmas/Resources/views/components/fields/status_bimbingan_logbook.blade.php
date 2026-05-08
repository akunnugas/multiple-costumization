@php
    $info = \Modules\Litabmas\Models\PengajuanPendanaanPembimbing::STATUS_BIMBINGAN_LOGBOOK[$value] ?? [];
    $value = $info['text'];
    $variant = $info['variant'];
@endphp

<x-core::badge :variant="$variant ?? null" type="outline" size="sm">
    {{ $value }}
</x-core::badge>
