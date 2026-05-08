@php
    use Carbon\Carbon;

    $startDate = Carbon::parse($data['tanggal_mulai'])->translatedFormat('d M Y');
    $endDate = Carbon::parse($data['tanggal_selesai'])->translatedFormat('d M Y');
@endphp

{{ $startDate }} - {{ $endDate }}
