@if (!empty($value))
    {{ Carbon\Carbon::parse($value)->translatedFormat('d F Y') }}
@endif
