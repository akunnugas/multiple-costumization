@php
    $agendaReviewer = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
    });
@endphp

@if (!empty($agendaReviewer))
    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Hasil Penilaian Presentasi Proposal</h3>
            </div>
        </div>

        <br>

        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kriteria Penilaian Presensi</th>
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
                        @foreach ($dataPenilaianPresentasiProposal as $item)
                            @php
                                $totalNilaiAspek = 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{!! str_replace("\n", "<br>", $item->pertanyaan_presentasi_proposal) !!}</td>
                                <td style="text-align: end;">{{ $item->bobot_pertanyaan_presentasi_proposal }}</td>
                                @foreach ($dataReviewerReviewProposal as $reviewer)
                                    @php
                                        $skala = $dataNilaiAspekReviewer[$reviewer->id][$item->id] ?? null;
                                        $nilai = null;
                                        if ($skala) {
                                            $skala *= 100;
                                            $nilai = $skala * $item->bobot_pertanyaan_presentasi_proposal / 100;

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
                        @if (count($dataPenilaianPresentasiProposal) > 0)
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
                        @endif
                    </tbody>
                </table>
            </div>
            <br>
            <span>*Kriteria penilaian untuk lolos ke tahap selanjutnya <b>rentang nilai 300-500</b>, jika nilai <b>kurang dari 300</b> maka dinyatakan tidak lolos untuk ke tahap selanjutnya.</span>
        </div>

        @if (count($dataPenilaianPresentasiProposal) == 0)
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">Belum ada Penilaian Presentasi Proposal</h1>
                            <p style="text-align: start; max-width: 550px;">
                                Tidak ada penilaian presentasi proposal.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@else
    <div class="card card_details-custom">
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">
                            Tidak ada Tahapan Kegiatan Penilaian Hasil Presentasi
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Klaster pada proposal ini tidak menggunakan agenda Penilaian Hasil Presentasi, Anda bisa mengabaikan halaman ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
