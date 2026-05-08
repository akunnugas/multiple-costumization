@props([
    'button' => null,
    'syncMessage' => null,
    'syncTitle' => null
])
@php
    $attributes = $attributes->merge([
        'title' => $syncTitle ?? 'Sinkronisasi ' . ($resourceTitle ?? 'Data'),
        'formMethod' => 'POST',
        'variant' => 'primary',
    ]);
@endphp
<x-core::modal {{ $attributes }}>
    <x-core::modal.body>
        @if (!empty($syncMessage))
            {!! $syncMessage !!}
        @else
            Apakah Anda yakin mensinkronisasi data {{ strtolower($resourceTitle ?? null) }}
        @endif
    <x-slot:footer>
        <div class="grid cols-1 cols-sm-2">
            <x-core::button variant="outline" data-dismiss="modal">
                Batal
            </x-core::button>
            <x-core::button variant="primary" id="btn_sync_checked">
                {{ $button ?? 'Ya' }}
            </x-core::button>
        </div>
    </x-slot:footer>
</x-core::modal.body>
</x-core::modal>

