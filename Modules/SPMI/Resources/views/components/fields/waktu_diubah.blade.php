@php
    $value = \Carbon\Carbon::parse($value)->translatedFormat('d M Y H:i');
@endphp

{{ $value }}
