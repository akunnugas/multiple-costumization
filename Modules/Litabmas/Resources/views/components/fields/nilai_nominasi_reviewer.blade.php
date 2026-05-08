@php
    use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
    $batasLolosNominasi = AspekPenilaianKomposisiProposal::NILAI_MEMENUHI_KRITERIA;
@endphp

@if($data['status_penilaian_isian_proposal'] === \Modules\Litabmas\Models\PengajuanPendanaanStatus::PENILAIAN_ISIAN_PROPOSAL_BELUM_DINILAI)
    -- Belum Dinilai --
@else
    @if ($value >= $batasLolosNominasi)
        <span class="nilai-item">
            <x-core::icon type="check-circle-solid"/> {{ $value }}
        </span>
    @else
        <span class="nilai-item">
            <x-core::icon type="x-circle-solid" /> {{ $value }}
        </span>
    @endif
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
@endif
