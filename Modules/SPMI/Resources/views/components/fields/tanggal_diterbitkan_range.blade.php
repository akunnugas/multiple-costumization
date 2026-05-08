@php
    use Carbon\Carbon;

    $startDate = Carbon::parse($data['tanggal_awal_berlaku'])->translatedFormat('d M Y');
    $endDate = Carbon::parse($data['tanggal_akhir_berlaku'])->translatedFormat('d M Y');
@endphp

{{ $startDate }} - {{ $endDate }}
