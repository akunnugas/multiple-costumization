<x-core::badge :variant="$value == true ? 'success' : 'danger'" type="secondary">
    {{ $value == true ? 'Aktif' : 'Tidak Aktif' }}
</x-core::badge>
