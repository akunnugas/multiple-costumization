<x-core::badge :variant="empty($value) ? 'warning' : 'success'">
    {{ empty($value) ? 'Belum' : 'Sudah' }}
</x-core::badge>
