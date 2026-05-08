@php
    use Modules\Kerjasama\Models\StatusKerjasama;
    $dataKey = 'id_status_kerjasama';
    if (empty($data[$dataKey])) {
        $dataKey = 'text';
    }

    $icon = StatusKerjasama::getIcon($data[$dataKey]);
@endphp
<div class="w-100 d-flex justify-content-{{ $dataKey == 'text' ? 'start' : 'center' }}">
    <span class="badge badge-outline-{{ StatusKerjasama::getBadgeType($data[$dataKey]) }} badge-sm">
       @if (!empty($icon)) <x-core::quantum-3.icon :$icon /> @endif {{ $data[$dataKey] }}
    </span>
</div>