@php
    $now = now();
    $start = isset($data['mulai']) ? \Carbon\Carbon::parse($data['mulai']) : null;
    $end = isset($data['selesai']) ? \Carbon\Carbon::parse($data['selesai']) : null;
@endphp

@if ($end && $now->gt($end->endOfDay()))
    <div class="w-100 d-flex justify-content-center">
        <span class="badge badge-outline-danger">Kadaluwarsa</span>
    </div>
@elseif ($start && $now->lt($start))
    <div class="w-100 d-flex justify-content-center">
        <span class="badge badge-outline-info">Dijadwalkan</span>
    </div>
@elseif ($data['is_published'] == 1)
    <div class="w-100 d-flex justify-content-center">
        <span class="badge badge-outline-success">Aktif</span>
    </div>
@else
    <div class="w-100 d-flex justify-content-center">
        <span class="badge badge-outline-warning">Tidak Aktif</span>
    </div>
@endif