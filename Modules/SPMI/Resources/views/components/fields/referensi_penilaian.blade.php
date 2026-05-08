@php
    use Modules\SPMI\Models\PenilaianMatriks;
    $value = PenilaianMatriks::REFERENCES[$value] ?? null;
@endphp

{{ $value }}
