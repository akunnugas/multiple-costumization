@php
    $tanggal = \Modules\Core\Helpers\Date::formatDateRange($data['tanggal_mulai_penilaian'], $data['tanggal_selesai_penilaian']);
@endphp

{{ $tanggal }}
