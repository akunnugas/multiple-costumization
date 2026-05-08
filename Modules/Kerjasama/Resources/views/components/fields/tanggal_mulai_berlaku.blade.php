@props(['data'])
@php
    use Carbon\Carbon;
@endphp
<div class="text-nowrap">
    <b>{{ Carbon::parse($data['tanggal_mulai_berlaku'])->translatedFormat('d M Y') }}</b> s.d. <b>{{ Carbon::parse($data['tanggal_akhir_berlaku'])->translatedFormat('d M Y') }}</b>
</div>