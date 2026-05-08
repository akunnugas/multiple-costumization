@props([
    'button' => null,
    'message' => null,
    'resourceTitle' => null,
])
<x-core::modal.body>
    @if (empty($message))
        Apakah Anda yakin ingin menghapus data {{ $resourceTitle ?? null }}
        <span id="span_text">{{ $slot }}</span>?
        Karena data yang telah dihapus tidak dapat dikembalikan lagi.
    @else
        {!! $message !!}
    @endif

    <x-slot:footer>
        <div class="grid cols-1 cols-sm-2">
            <x-core::button variant="outline" data-dismiss="modal">
                Batal
            </x-core::button>
            @php
                if (!empty($button)) {
                    $attributes = $button->attributes;
                } else {
                    $attributes = Page::buildAttributes();
                }
            @endphp
            <x-core::button variant="destructive" {{ $attributes }}>
                {{ $button ?? 'Hapus' }}
            </x-core::button>
        </div>
    </x-slot:footer>
</x-core::modal.body>
