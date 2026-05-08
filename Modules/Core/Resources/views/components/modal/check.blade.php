@props([
    'type' => 'delete',
])
<x-core::modal variant="primary" title="Pilih {{ $resourceTitle }} Terlebih Dahulu" {{ $attributes }}>
    <x-core::modal.body>
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @elseif ($type == 'delete')
            Silakan centang {{ strtolower($resourceTitle) }} yang ingin dihapus terlebih dahulu.
        @endif
        <x-slot:footer>
            <div class="grid cols-1">
                <x-core::button variant="outline" data-dismiss="modal">
                    Mengerti
                </x-core::button>
            </div>
        </x-slot:footer>
    </x-core::modal.body>
</x-core::modal>
