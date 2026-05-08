@php
    use Carbon\Carbon;

    $firstDate = Carbon::parse($data['tanggal_awal_pengisian']);
    $secondDate = Carbon::parse($data['tanggal_akhir_pengisian']);

    if ($firstDate->month == $secondDate->month) {
        $value = $firstDate->translatedFormat('d') . ' - ' . $secondDate->translatedFormat('d M Y');
    } else {
        $formatFirstDate = 'd M';
        if ($firstDate->year != $secondDate->year) {
            $formatFirstDate .= ' Y';
        }

        $value = $firstDate->translatedFormat($formatFirstDate) . ' - ' . $secondDate->translatedFormat('d M Y');
    }
@endphp

{{ $value }}
