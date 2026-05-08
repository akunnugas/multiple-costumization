@php
    $dataKey = 'link_dokumentasi';
    if (empty($data[$dataKey])) {
        $dataKey = 'text';
    }

    $icon = 'link-external';
@endphp
<div class="w-100 d-flex text-wrap justify-content-{{ $dataKey == 'text' ? 'start' : 'center' }}">
    @if (!empty($data[$dataKey]))        
    <a class="text-wrap truncate-2" href="{{ $data[$dataKey] }}" target="_blank">
        {{ $data[$dataKey] }}
    </a>
    @endif
</div>