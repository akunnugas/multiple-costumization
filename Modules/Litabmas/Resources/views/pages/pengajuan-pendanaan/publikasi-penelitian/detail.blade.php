@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $currentEditUrl = $data[0]['edit_url'];
    // tambahkan edit url dengan $jenisPublikasi
    $data[0]['edit_url'] = $currentEditUrl . '?tab=' . $jenisPublikasi;
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>
    <x-core::layouts.html.alert />
    @if (!empty($header))
        <x-core::layouts.detail.header :data="$header" />
    @endif
    <x-core::layouts.detail.cards :$data />
</x-core::layouts.main>
