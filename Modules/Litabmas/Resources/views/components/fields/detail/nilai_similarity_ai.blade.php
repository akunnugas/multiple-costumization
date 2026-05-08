@php
    $similarityAi = $item['original'] ?? null;
    $nilai = Format::removeTrailingZeroes($similarityAi['nilai'] ?? 0);
@endphp

@if (!empty($similarityAi))
    <span class="{{ $similarityAi['melebihi_toleransi'] == false ? 'ai-cukup' : 'ai-tidak-cukup' }}">
        {{ $nilai }}%
    </span>
@endif
@pushonce('head')
    <style>
        .ai-cukup {
            color: var(--qn-success-300);
            ;
        }

        .ai-tidak-cukup {
            color: var(--qn-danger-300);
        }
    </style>
@endpushonce
