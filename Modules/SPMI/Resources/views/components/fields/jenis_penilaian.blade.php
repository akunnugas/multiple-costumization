@php
    use Modules\SPMI\Models\PenilaianMatriks;
    $value = PenilaianMatriks::TYPES[$value] ?? null;
@endphp

{{ $value }}
