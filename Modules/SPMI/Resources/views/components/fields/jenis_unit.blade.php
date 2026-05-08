@php
    $variant = 'danger';
    $label = $value;

    switch ($value) {
        case \Modules\Core\Models\UnitKerja::UNIVERSITY:
            $variant = 'primary';
            break;
        case \Modules\Core\Models\UnitKerja::STUDY_PROGRAM:
            $variant = 'success';
            break;
        case \Modules\Core\Models\UnitKerja::FACULTY:
            $variant = 'info';
            break;
        case \Modules\Core\Models\UnitKerja::MAJOR:
            $variant = 'warning';
            break;
        case \Modules\Core\Models\UnitKerja::UNIT_NON_PRODI:
            $variant = 'secondary';
            break;
    }

    $types = \Modules\Core\Models\UnitKerja::TYPES;
    if (array_key_exists($value, $types)) {
        $label = $types[$value];
    } else {
        $label = 'Tidak Diketahui';
    }
@endphp
<x-core::badge :variant="$variant" type="secondary">
    {{ $label }}
</x-core::badge>
