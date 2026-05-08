@php
    $percentage = 0;
    $totalFilled = $data['total_temuan_terisi'];
    $totalTemuan = $data['total_temuan'];

    // Hitung persentase
    if ($totalTemuan > 0) {
        $percentage = round(($totalFilled / $totalTemuan) * 100);
    }
@endphp

<div @style([
    'background: #D6F5E4' => $percentage == 100,
    'background: #FDF3EC' => $percentage > 0 && $percentage < 100,
    'background: #EEF2F6' => $percentage == 0,
    'height: 100%',
    'display: flex',
    'align-items: center',
    'padding: 0 8px',
    'border-radius: 6px',
    'min-width: 280px',
])>
    <b>{{ $percentage }}%</b> (Terisi {{ $totalFilled }} dari {{ $totalTemuan }} indikator)
</div>
