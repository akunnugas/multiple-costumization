@php
    use Modules\Litabmas\Models\PengumumanPendanaan;

    if ($data['status_keaktifan_dan_ordering'] === PengumumanPendanaan::STATUS_KLASTER_BELUM_DIBUKA) {
        $info = [
            'variant' => 'secondary',
            'text' => 'Belum Dibuka',
        ];
    } elseif ($data['status_keaktifan_dan_ordering'] === PengumumanPendanaan::STATUS_KLASTER_DIBUKA) {
        $info = [
            'variant' => 'success',
            'text' => 'Dibuka',
        ];
    } else {
        $info = [
            'variant' => 'danger',
            'text' => 'Ditutup',
        ];
    }
@endphp

<x-core::badge :variant="$info['variant']" type="secondary" size="sm">
    {{ $info['text'] }}
</x-core::badge>
