@php
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
@endphp

@php
    $info = PengajuanPendanaanStatus::getStatusInfo($field, $value);
@endphp

<x-core::badge :variant="$info['variant']" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
