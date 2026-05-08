@props(['data'])
@php
    use Carbon\Carbon;
@endphp
<div class="text-nowrap">
    <b>{{ Carbon::parse($data['mulai'])->translatedFormat('d M Y') }}</b> s.d. <b>{{ Carbon::parse($data['selesai'])->translatedFormat('d M Y') }}</b>
</div>