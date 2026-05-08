@php
    $daftarReviewer = \Illuminate\Support\Str::replaceLast(', ', ' & ', $data['daftar_reviewer']);
@endphp

{{ $daftarReviewer }}
