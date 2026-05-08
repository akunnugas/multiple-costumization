@php
    use Modules\SPMI\Models\PenilaianMatriks;
    $value = PenilaianMatriks::CATEGORIES[$value] ?? null;
@endphp

{{ $value }}
