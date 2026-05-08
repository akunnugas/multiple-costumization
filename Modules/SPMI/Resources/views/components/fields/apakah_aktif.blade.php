<x-core::badge :variant="$value == true ? 'success' : 'danger'" type="secondary">
    {{ $value == true ? 'Berlaku' : 'Tidak Berlaku' }}
</x-core::badge>
