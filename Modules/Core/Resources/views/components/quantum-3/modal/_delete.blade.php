@php
    $attributes = $attributes->merge([
        'title' => 'Hapus ' . ($resourceTitle ?? 'Data'),
        'formMethod' => 'DELETE',
        'variant' => 'error',
    ]);
@endphp
<x-core::quantum-3.modal {{ $attributes }}>
    {{ $slot }}
</x-core::quantum-3.modal>
