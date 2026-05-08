@php
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
@endphp

@php
    $info = PengajuanPendanaanStatus::getStatusInfo($item['field'], $item['original'] ?? null);
@endphp

<x-core::badge :variant="$info['variant']" type="secondary" size="sm">
    {{ $info['text'] }}
</x-core::badge>

