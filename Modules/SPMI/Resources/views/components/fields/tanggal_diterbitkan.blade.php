@php
    use Carbon\Carbon;

    if (!empty($value)) {
        $value = Carbon::parse($value)->translatedFormat('d M Y');
    }
@endphp

{{ $value }}
