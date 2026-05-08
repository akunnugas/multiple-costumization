@php
    use Carbon\Carbon;

    if (isset($value)) {
        $startDate = Carbon::parse($value);
        $value = $startDate->translatedFormat('d M Y');
    }
@endphp

{{ $value }}
