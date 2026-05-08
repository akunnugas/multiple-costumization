@php
    $attributes = $attributes->merge([
        'title' => 'Hapus ' . ($resourceTitle ?? 'Data'),
        'formMethod' => 'DELETE',
        'variant' => 'error',
    ]);
@endphp
<x-core::modal {{ $attributes }}>
    {{ $slot }}
</x-core::modal>
