@php
    $agenda = $item['original'];

    // Urutkan agenda berdasarkan tanggal
    uasort($agenda, function ($a, $b) {
        return Carbon\Carbon::parse($a[0])->timestamp <=> Carbon\Carbon::parse($b[0])->timestamp;
    });

    $now = Carbon\Carbon::now();

    $agendaAktif = null;
    $firstAgenda = null;
    $lastAgenda = null;
    $nextAgenda = null;

    foreach ($agenda as $label => $tanggal) {
        // Simpan agenda pertama dan terakhir
        if (!$firstAgenda) {
            $firstAgenda = $label;
        }
        $lastAgenda = $label;

        $startDate = Carbon\Carbon::parse($tanggal[0])->startOfDay();
        $endDate = Carbon\Carbon::parse($tanggal[1])->endOfDay();

        // Cek apakah tanggal sekarang berada di antara rentang agenda
        if ($now->between($startDate, $endDate)) {
            $agendaAktif = $label;
            break;
        }

        // Simpan agenda berikutnya jika tanggal sekarang lebih kecil dari tanggal mulai agenda ini
        if ($now->lt($startDate) && !$nextAgenda) {
            $nextAgenda = $label;
        }
    }

    // Jika tidak ada agenda aktif
    if (!$agendaAktif) {
        $firstAgendaDates = $agenda[$firstAgenda];
        $lastAgendaDates = $agenda[$lastAgenda];

        if ($now->lt(Carbon\Carbon::parse($firstAgendaDates[0])->startOfDay())) {
            // Tanggal sekarang lebih kecil dari semua agenda, ambil yang pertama
            $agendaAktif = $firstAgenda;
        } elseif ($now->gt(Carbon\Carbon::parse($lastAgendaDates[1])->endOfDay())) {
            // Tanggal sekarang lebih besar dari semua agenda, ambil yang terakhir
            $agendaAktif = $lastAgenda;
        } else {
            // Tanggal berada di antara agenda pertama dan terakhir, ambil agenda n+1
            $agendaAktif = $nextAgenda;
        }
    }

    $mapped = [
        'Review Proposal' => 'status_penilaian_isian_proposal',
        'Review Luaran' => 'status_penilaian_output',
        'Review Antara' => 'status_penilaian_progress_report',
    ];

    $val = $item['text'][$mapped[$agendaAktif]] ?? 'belum_dinilai';

    $variant = strpos($val, 'belum') !== false ? 'warning' : 'success';
    $text = strpos($val, 'belum') !== false ? 'Belum' : 'Sudah';
@endphp

<x-core::badge :variant="$variant" type="secondary" size="sm">
    {{ $text }} Dinilai
</x-core::badge>
