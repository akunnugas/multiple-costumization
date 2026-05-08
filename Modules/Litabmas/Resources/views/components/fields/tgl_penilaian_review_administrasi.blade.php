@php
    $tanggalReview = \Modules\Core\Helpers\Date::formatDateRange($data['tanggal_mulai_penilaian_reviewer'], $data['tanggal_selesai_penilaian_reviewer']);
@endphp

{{ $tanggalReview }}
