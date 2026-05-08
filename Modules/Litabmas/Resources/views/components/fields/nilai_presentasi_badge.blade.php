@php
    use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
    $batasLolosNominasi = AspekPenilaianPresentasiProposal::NILAI_MEMENUHI_KRITERIA;
    $icon = 'x-circle-solid';
    if ($value >= $batasLolosNominasi) {
        $icon = 'check-circle-solid';
    }
@endphp
<span class="nilai-item">
    <x-core::icon type="{{ $icon }}" /> {{ $value }}
</span>
@pushonce('head')
    <style>
        .icon.icon-x-circle-solid {
            /* The check icon doesn't need this as it's already in qn.css */
            color: var(--qn-danger);
            font-size: 1.5rem;
        }

        .nilai-item {
            display: flex;
            align-items: center;
            gap: 0.2rem;
            /* Aligns the content to the right */
        }
    </style>
@endpushonce
