@php
    $percentage = 0;

    $totalFilled = $data['total_indikator_terisi'];
    $totalIndicator = $data['total_indikator'];
    // Hitung persentase
    if ($totalIndicator > 0) {
        $percentage = round($totalFilled / $totalIndicator * 100);
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
    <b>{{ $percentage }}%</b> (Terisi {{ $totalFilled }} dari {{ $totalIndicator }} indikator)
</div>
