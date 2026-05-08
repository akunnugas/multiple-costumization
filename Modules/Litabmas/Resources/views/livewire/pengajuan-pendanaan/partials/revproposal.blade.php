@php
    $agendaReviewer = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
    });
@endphp

@if (!empty($agendaReviewer))
    @if (!$isDosen)
        <div class="card card_details-custom">
            <h3>Hasil Kriteria Penilaian</h3>

            <br>

            <div class="box-table__content" id="table-docs" style="border: 0px;">
                <div class="table-max">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kriteria Penilaian</th>
                                <th>Bobot Penilaian(%)</th>

                                @foreach ($dataReviewerReviewProposal as $item)
                                    <th style="text-align: center;">Reviewer {{ $item->reviewer_ke }}<br>{{ $item->nama_reviewer }}</th>
                                @endforeach

                                <th style="text-align: center;">Total Nilai Reviewer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalNilai = 0;
                            @endphp
                            @foreach ($dataPenilaianAspekProposal as $item)
                                @php
                                    $totalNilaiAspek = 0;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->nama_komposisi_proposal }}</td>
                                    <td style="text-align: end;">{{ $item->bobot_komposisi_proposal }}</td>
                                    @foreach ($dataReviewerReviewProposal as $reviewer)
                                        @php
                                            $skala = $dataNilaiAspekReviewer[$reviewer->id][$item->id] ?? null;
                                            $nilai = null;
                                            if ($skala) {
                                                $skala *= 100;
                                                $nilai = $skala * $item->bobot_komposisi_proposal / 100;

                                                $totalNilaiAspek += $nilai;
                                            }
                                        @endphp
                                        <td style="text-align: end;">{{ number_format($nilai, 2) }}</td>
                                    @endforeach
                                    @php
                                        $totalNilai += $totalNilaiAspek;
                                    @endphp
                                    <td style="text-align: end;">{{ number_format($totalNilaiAspek, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="background: #F8FAFC;">
                                <td colspan="3"><b>Total Nilai Keseluruhan</b></td>
                                @if (count($dataReviewerReviewProposal) > 0)
                                    <td colspan="{{ count($dataReviewerReviewProposal) }}"></td>
                                @endif
                                <td style="text-align: end;"><b>{{ number_format($totalNilai, 2) }}</b></td>
                            </tr>
                            <tr style="background: #F8FAFC;">
                                <td colspan="3"><b>Rata-Rata Nilai</b></td>
                                @if (count($dataReviewerReviewProposal) > 0)
                                    <td colspan="{{ count($dataReviewerReviewProposal) }}">Rata-Rata Nilai = Total Nilai Keseluruhan / Jumlah Reviewer</td>
                                @endif
                                @php
                                    $finalScore = count($dataReviewerReviewProposal) > 0 ? $totalNilai / count($dataReviewerReviewProposal) : 0;
                                @endphp
                                <td style="display: flex; gap: 5px; align-items: center; justify-content: end;">
                                    @if($finalScore >= 300){{ checkListSVG() }}@else{{ crossListSVG() }} @endif <b>{{ number_format($finalScore, 2) }} ({{ $finalScore >= 300 ? 'Lolos' : 'Tidak Lolos' }})</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <br>
            <div class="card card_details-custom">
                <h3>Keterangan Penilaian</h3>
                <div style="display: flex; justify-content: space-between; margin-top: 14px;">
                    <li>0-100 = Tidak Baik</li>
                    <li>101-200 = Kurang Baik</li>
                    <li>201-300 = Cukup</li>
                    <li>301-400 = Baik</li>
                    <li>401-500 = Sangat Baik</li>
                </div>
                <br>
                <span>*Kriteria penilaian untuk lolos ke tahap selanjutnya <b>rentang nilai 300-500</b>, jika nilai <b>kurang dari 300</b> maka dinyatakan tidak lolos untuk ke tahap selanjutnya.</span>
            </div>
        </div>
    @endif

    <style>
        .color-auto>* {
            color: #697586;
        }
    </style>

    <br>
    <div class="card card_details-custom">
        <h3>Feedback Reviewer</h3>

        @foreach ($dataIsianPenilaianProposal as $item)
            <br>
            <div class="card card_details-custom color-auto" style="box-shadow: rgba(149, 157, 165, 0.2) 0px 2px 2px;">
                <h3 style="color: #222222">{{ $loop->iteration . '. ' . $item->nama }}</h3>
                <p style="margin-top: 5px;">{!! $item->deskripsi !!}</p>
                <hr>

                @if (!isset($dataFeedbackPenilaianProposal[$item->id]))
                    <span>Belum ada feedback dari reviewer</span>
                @else
                    @foreach ($dataFeedbackPenilaianProposal[$item->id] as $feedback)
                        @php
                            $urut = $feedback->pengajuan_pendanaan_reviewer->reviewer_ke;
                            $biodata = $feedback->pengajuan_pendanaan_reviewer->biodata;
                            $textFeedback = $feedback->feedback_isian_proposal;
                            $textFeedback = str_replace("\n", "<br>", $textFeedback);
                        @endphp
                        <div class="color-auto">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="avatar__circle"
                                    style="min-width: 35px; width: 35px; min-height: 35px; height: 35px;">
                                    <p class="avatar__acronym">
                                        {{ substr($biodata->nama, 0, 2) }}
                                    </p>
                                </div>
                                <h4 style="color: #222222">{{ $biodata->nama }} (Reviewer {{ $urut }})</h4>
                                &bull; {{ $feedback->waktu_diubah->translatedFormat('d F Y') }}
                            </div>
                            <p style="margin-top: 10px; max-width: 450px;">{!! $textFeedback !!}</p>
                        </div>
                        <br>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="card card_details-custom">
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">
                            Tidak ada Tahapan Kegiatan Peninjauan Proposal
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Klaster pada proposal ini tidak menggunakan agenda Peninjauan Proposal, Anda bisa mengabaikan halaman ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
