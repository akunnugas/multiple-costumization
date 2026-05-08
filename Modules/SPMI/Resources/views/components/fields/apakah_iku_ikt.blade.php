@props([
    'decoration' => false,
    'type' => null,
    'value' => null,
])
<x-core::badge :variant="empty($value) ? 'warning' : 'success'" :type="$type ?? null" :decoration="$decoration ?? false">
    {{ empty($value) ? 'Indikator Kinerja Tambahan' : 'Indikator Kinerja Utama' }}
</x-core::badge>
