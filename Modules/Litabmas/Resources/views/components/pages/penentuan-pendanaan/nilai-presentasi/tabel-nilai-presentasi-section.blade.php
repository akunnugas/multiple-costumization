@props(['nilai', 'reviewer'])
@php
    use Illuminate\Support\Carbon;
    use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;

    $staticTableHeader = [
        ['field' => 'no', 'label' => 'No'],
        ['field' => 'aspek_penilaian', 'label' => 'Pertanyaan'],
        ['field' => 'bobot', 'label' => 'Bobot'],
    ];

    $totalReviewer = count($reviewer);

    $bobotPenilaianProposal = AspekPenilaianPresentasiProposal::OPTION_SKALA_BOBOT;
    $batasLolosNominasi = AspekPenilaianPresentasiProposal::NILAI_MEMENUHI_KRITERIA;
    $noTable = 1;
@endphp
@pushonce('head')
@endpushonce
<div class="box-table">
    <div class="box-table_content">
        <div class="table-max table-max_absolute">
            <table style="padding-bottom: 1rem">
                <thead>
                    <tr>
                        @foreach ($staticTableHeader as $item)
                            <th>{{ $item['label'] }}</th>
                        @endforeach
                        @for ($i = 0; $i < $totalReviewer; $i++)
                            <th>Nilai Reviewer {{ $reviewer[$i]->reviewer_ke }} <br>
                                ({{ $reviewer[$i]->nama_user }})</th>
                            </th>
                        @endfor
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nilai['data'] as $item)
                        <tr>
                            <td>{{ $noTable++ }}</td>
                            <td>
                                <x-litabmas::fields.textarea_breakline :value="$item['pertanyaan_presentasi_proposal']" />
                            </td>
                            <td>{{ $item['bobot_pertanyaan_presentasi_proposal'] }}</td>
                            @foreach ($reviewer as $value)
                                <td>{{ $item['nilai_reviewer_' . $value->reviewer_ke] }}</td>
                            @endforeach
                            <td>{{ $item['total_nilai_reviewer'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan='{{ 3 + $totalReviewer }}'><b>Total Nilai Keseluruhan</b></td>
                        <td>{{ $nilai['total_nilai_keseluruhan'] }}</td>
                    </tr>
                    <tr>
                        <td colspan='{{ 3 + $totalReviewer }}'><b>Rata Rata Nilai</b></td>
                        <td>{{ $nilai['rata_rata_nilai'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
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
