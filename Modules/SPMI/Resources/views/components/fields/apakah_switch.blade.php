<x-core::badge :variant="$value == false ? 'success' : 'danger'" type="secondary">
    {{ $value == false ? 'Ya' : 'Tidak' }}
</x-core::badge>
