@php
    if (!empty($data['waktu_mulai']) && !empty($data['waktu_selesai'])) {
        $tanggal = \Modules\Core\Helpers\Date::formatDateRange($data['waktu_mulai'], $data['waktu_selesai'], isoFormatMonth: 'MMMM');
    } else {
        $tanggal = null;
    }
@endphp

{{ $tanggal }}
