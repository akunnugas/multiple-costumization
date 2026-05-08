@php
    $percentage = 0;
    $totalFilled = $data['total_target_terisi'] ?? 0;
    $totalMatrixIndicator = $data['total_indikator_matriks'];

    // Hitung persentase
    if ($totalMatrixIndicator > 0) {
        $percentage = round($totalFilled / $totalMatrixIndicator * 100);
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
    <b>{{ $percentage }}%</b> (Terisi {{ $totalFilled }} dari {{ $totalMatrixIndicator }} indikator)
</div>
