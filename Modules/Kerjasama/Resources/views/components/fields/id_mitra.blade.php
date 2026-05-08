@php
    $dataKey = 'id_mitra';
    $dataOriginal = null;
    if (empty($data[$dataKey])) {
        $dataKey = 'text';
        $dataOriginal = $data['original'];
    }
@endphp

@if (!empty($dataOriginal))
<a href="{{ route('kerjasama.mitra.show', $dataOriginal) }}" target="_blank">
    {!! $data[$dataKey] !!}
</a>
@else
<span>{!! $data[$dataKey] !!}</span>
@endif
