@props([
    'isDetailV2' => false,
])

@php
    use Modules\Litabmas\Models\KlasterPendanaan;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    // set jika kategori klaster adalah individu, maka unset field minimal & maksimal anggota
    foreach ($data[0]['items'] as $item) {
        if ($item['field'] === 'kategori_klaster' && $item['original'] === KlasterPendanaan::KATEGORI_INDIVIDU) {
            // Gunakan array_filter() untuk menghapus 'minimal_anggota' dan 'maksimal_anggota' dan 'apakah_butuh_approve_semua_anggota'
            $data[0]['items'] = array_filter($data[0]['items'], function($value) {
                return $value['field'] !== 'minimal_anggota' &&
                    $value['field'] !== 'maksimal_anggota' &&
                    $value['field'] !== 'apakah_butuh_approve_semua_anggota';
            });
            break;
        }
    }

    $editUrl = route('litabmas.klaster-pendanaan.edit', $resourceId);
@endphp

<x-core::layouts.detail-v2 :data="$data" :isFullwidth="true" />
