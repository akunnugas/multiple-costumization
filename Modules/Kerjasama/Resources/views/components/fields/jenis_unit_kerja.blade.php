@php
    $typesFlipped = array_flip(\Modules\Core\Models\UnitKerja::TYPES);

    $variant = 'danger';
    $label = $value;

    switch ($value) {
        case 'Universitas':
            $variant = 'primary';
            break;
        case 'Program Studi':
            $variant = 'success';
            break;
        case 'Fakultas':
            $variant = 'info';
            break;
        case 'Jurusan':
            $variant = 'warning';
            break;
        case 'Unit Non Akademik':
            $variant = 'primary';
            break;
    }

    if (array_key_exists($value, $typesFlipped)) {
        $label = $value;
    } else {
        $label = 'Unit Non Akademik';
        $variant = 'primary';
    }
@endphp
<div class="w-100 d-flex justify-content-center">
    <span class="badge badge-outline-{{ $variant }} badge-sm">
     {{ $label }}
    </span>
</div>