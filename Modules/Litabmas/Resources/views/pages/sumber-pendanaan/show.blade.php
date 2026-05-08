@props([
    'isDetailV2' => false,
])

@php
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $data[0]['title'] = 'Sumber Pendanaan & Penentuan Tahapan Kegiatan';
@endphp

<x-core::layouts.detail-v2 :data="$data" :title="$title" :isFullwidth="true" />
