@php
    $apakahLaporanProposal = $currentReviewer->apakah_review_proposal;
@endphp

@if (isset($timelinePresentasi) && !empty($timelinePresentasi))
    @if ($apakahLaporanProposal)
        @php
            $msgType = 'helper';
            $msgInfo = '';
        @endphp

        @if (!$isCanPresentasi)
            @php
                $msgInfo =
                    'Anda dapat memberikan penilaian pada tanggal awal <b>' .
                    Carbon\Carbon::parse($timelinePresentasi->waktu_mulai)->translatedFormat('d F Y') .
                    '</b> sampai <b>' .
                    Carbon\Carbon::parse($timelinePresentasi->waktu_selesai)->translatedFormat('d F Y') .
                    '</b>.';
            @endphp
        @endif

        @if (!empty($msgInfo) && !isset($alert))
            <div class="alert alert_{{ $msgType }}">
                <div class="alert__content">
                    <p>{!! $msgInfo !!}</p>
                </div>
            </div>
            <br>
        @endif

        @include('litabmas::livewire.penilaian-reviewer.partials.section_jadwal')

        <div class="card card_details-custom">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <h3>Berikan Penilaian</h3>
                    <p style="color: #697586; margin-top: 5px;">Silakan berikan feedback berupa komentar dan berikan
                        penilaian pada Proposal Penelitian ini </p>
                </div>
                @if ($isCanPresentasi)
                    @if (!$isEdit)
                        <div>
                            <a href="javascript::void(0)" wire:click="penilaianpresentasi_editPenilaianAspek"
                                class="btn btn_primary btn_xs" style="">
                                <span class="icon icon-pencil"></span>
                                <span class="btn__text">Beri Penilaian</span></a>
                        </div>
                    @else
                        <div style="display: flex; gap: 8px;">
                            <a href="javascript::void(0)" wire:click="penilaianpresentasi_cancelPenilaianAspek"
                                class="btn btn_outline btn_xs" style="">
                                <span class="btn__text">Batal</span></a>
                            <a href="javascript::void(0)" wire:click="penilaianpresentasi_savePenilaianAspek"
                                class="btn btn_primary btn_xs" style="">
                                <span class="btn__text">Simpan</span></a>
                        </div>
                    @endif
                @endif
            </div>
            <hr>

            @php
                $optionAspek = Modules\Litabmas\Models\PenilaianReviewerKomposisiProposal::SKALA_NILAI;
            @endphp

            <div class="box-table__content" id="table-docs" style="border: 0px;">
                <div class="table-max">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kriteria Penilaian Presentasi</th>
                                <th>Bobot Penilaian (%)</th>
                                <th style="width: 25%;">Skala Nilai (0-5)</th>
                                <th style="text-align: center;">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalNilai = 0;
                            @endphp
                            @foreach ($dataPenilaianAspekPresentasiProposal as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->pertanyaan_presentasi_proposal }}</td>
                                    <td style="text-align: end;">{{ $item->bobot_pertanyaan_presentasi_proposal }}</td>
                                    <td>
                                        @if (!$isEdit)
                                            {{ isset($records['nilai'][$item->id]) ? Modules\Litabmas\Models\PenilaianReviewerKomposisiProposal::SKALA_NILAI[$records['nilai'][$item->id]] : '-' }}
                                        @else
                                            <x-core::controls.select :isEmpty="'false'" label="Jenis Pendanaan" purpose="filter"
                                                :selected="$records['nilai'][$item->id] ?? null"
                                                wire:change="penilaianpresentasi_setNilai({{ $item->id }}, $event.target.value)"
                                                :options="$optionAspek" />
                                        @endif
                                    </td>
                                    <td style="text-align: end;">
                                        @php
                                            $nilai = isset($records['nilai'][$item->id])
                                                ? ($records['nilai'][$item->id] * 100 * $item->bobot_pertanyaan_presentasi_proposal) /
                                                    100
                                                : 0;

                                            $totalNilai += $nilai;
                                        @endphp

                                        {{ $nilai }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr style="background: #F8FAFC;">
                                <td colspan="4"><b>Total Nilai Keseluruhan</b></td>
                                <td style="display: flex; gap: 5px; align-items: center; justify-content: end;">
                                    @if (!empty($records['nilai']) && !$isEdit)
                                        <b>{{ $totalNilai }} </b>
                                    @endif
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
    @else
        <div class="card card_details-custom">
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">
                                Tidak bertugas sebagai Reviewer Proposal
                            </h1>
                            <p style="text-align: start; max-width: 550px;">
                                Anda tidak ditunjuk sebagai <b>Reviewer Proposal</b> pada proposal ini, Anda bisa mengabaikan halaman ini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
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

