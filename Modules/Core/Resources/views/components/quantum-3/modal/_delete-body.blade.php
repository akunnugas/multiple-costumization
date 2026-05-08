@props([
    'button' => null,
    'message' => null,
    'resourceTitle' => null,
])
<x-core::quantum-3.modal.body>
    <div class="modal-body">
        @if (empty($message))
            Apakah Anda yakin ingin menghapus data {{ $resourceTitle ?? null }}
            <span id="span_text">{{ $slot }}</span>?
            Karena data yang telah dihapus tidak dapat dikembalikan lagi.
        @else
            {!! $message !!}
        @endif
    </div>

    <x-slot:footer>
            <x-core::quantum-3.button variant="secondary" data-bs-dismiss="modal">
                Batal
            </x-core::quantum-3.button>
            @php
                if (!empty($button)) {
                    $attributes = $button->attributes;
                } else {
                    $attributes = Page::buildAttributes();
                }
            @endphp
            <x-core::quantum-3.button variant="danger" {{ $attributes }}>
                {{ $button ?? 'Hapus' }}
            </x-core::quantum-3.button>
    </x-slot:footer>
</x-core::quantum-3.modal.body>



