@props(['nilai', 'reviewer', 'aspek'])
@php
    use Illuminate\Support\Carbon;
    use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;

    $staticTableHeader = [
        ['field' => 'no', 'label' => 'no'],
        ['field' => 'aspek_penilaian', 'label' => 'Aspek Penilaian'],
        ['field' => 'bobot', 'label' => 'Bobot'],
    ];

    $totalReviewer = count($reviewer);

    $bobotPenilaianProposal = AspekPenilaianKomposisiProposal::OPTION_SKALA_BOBOT;
    $batasLolosNominasi = AspekPenilaianKomposisiProposal::NILAI_MEMENUHI_KRITERIA;

    $sumTotalNilai = 0;
@endphp
@pushonce('head')
@endpushonce
<div class="card__body table-max table-max_absolute">
    <table style="padding-bottom: 1rem">
        <thead>
            <tr>
                @foreach ($staticTableHeader as $item)
                    <th>{{ $item['label'] }}</th>
                @endforeach
                @for ($i = 0; $i < $totalReviewer; $i++)
                    <th>Reviewer {{ $reviewer[$i]->reviewer_ke }}</th>
                @endfor
                <th>Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($aspek as $item)
                <tr>
                    <td>{{ $item['no'] }}</td>
                    <td>
                        <x-litabmas::fields.textarea_breakline :value="$item['nama_komposisi_proposal']" />
                    </td>
                    <td id="bobot-{{ $item['id'] }}">
                        {{ Format::number($item['bobot_komposisi_proposal']) }}</td>
                    @php
                        $totalNilai = 0;
                    @endphp
                    @for ($i = 0; $i < $totalReviewer; $i++)
                        <td>
                            @php
                                $nilaiSkala =
                                    ($nilai[$reviewer[$i]->reviewer_ke][$item['id']] ?? 0) *
                                    $item['bobot_komposisi_proposal'];
                                $totalNilai += $nilaiSkala;
                            @endphp
                            {{ $nilaiSkala ?: 0 }}
                        </td>
                    @endfor
                    @php
                        $sumTotalNilai += $totalNilai;
                    @endphp
                    <td id="total-nilai-{{ $item['id'] }}">{{ $totalNilai }}</td>
                </tr>
            @endforeach
            @php
                //Determine the appropriate label based on $sumTotalNilai
                $labelAfterSumTotalNilai = '';
                foreach ($bobotPenilaianProposal as $bobot => $label) {
                    if ($sumTotalNilai <= $bobot) {
                        $labelAfterSumTotalNilai = $label;
                        break;
                    }
                }

                $totalRataRata = $sumTotalNilai / $totalReviewer;
                $colorRata = '#EC3D27';
                $textRata = 'Tidak Lolos';
                if ($totalRataRata >= $batasLolosNominasi) {
                    $colorRata = '#15B79E';
                    $textRata = 'Lolos';
                }
            @endphp
            <tr>
                <td colspan="{{ 3 + $totalReviewer }}">Total Nilai Keseluruhan</td>
                <td id="sum-nilai">{{ $sumTotalNilai }}</td>
            </tr>
            <tr>
                <td colspan="{{ 3 + $totalReviewer }}">Rata-rata nilai</td>
                <td id="rata-nilai"><span style="color: {{ $colorRata }}">
                        {{ $totalRataRata }} ({{ $textRata }})<br>
                    </span></td>
        </tbody>
    </table>
    <div class=""
        style="border:1px solid #E3E8EF; border-radius:8px; padding: 1rem 0.75rem; display:flex; flex-direction: column; gap:0.75rem">
        <h2 class="card__content_title" style="margin-top: 0">Keterangan Penilaian</h2>
        <ul class="" style="display: flex; width:100%; padding-left: 1.25rem">
            @php
                $width = 100 / count($bobotPenilaianProposal);
                $start = 0;
            @endphp
            @foreach ($bobotPenilaianProposal as $bobot => $label)
                <li class="col" style="width:{{ $width }}%">
                    {{ $start }}-{{ $bobot }} =
                    {{ $label }}</li>
                @php
                    $start = $bobot + 1;
                @endphp
            @endforeach
        </ul>
    </div>
</div>
