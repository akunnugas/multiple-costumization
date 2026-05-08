<x-core::badge :variant="$value == true ? 'success' : 'warning'" type="secondary" :decoration="true">
    {{ $value == true ? 'Sudah Dimapping' : 'Belum Dimapping' }}
</x-core::badge>
