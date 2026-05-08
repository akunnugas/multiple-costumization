@php
    use Modules\Litabmas\Models\PenilaianReviewerOutput;

    $emptyStatus = PenilaianReviewerOutput::STATUS_BELUM_DINILAI;
    $info = PenilaianReviewerOutput::STATUS_PENILAIAN_OUTPUT[$data['status_penilaian_output'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant'] ?? null" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
