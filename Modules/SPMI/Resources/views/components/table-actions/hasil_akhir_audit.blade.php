@php
    $detailUrl = Page::detailURL($data['id']);
@endphp

<x-core::button :href="$detailUrl" size="xs" variant="primary" @style(['color: #fff !important' => true])>
    Lihat Detail
</x-core::button>
