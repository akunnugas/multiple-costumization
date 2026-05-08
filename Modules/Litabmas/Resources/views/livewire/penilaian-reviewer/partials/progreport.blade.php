@php
    $apakahLaporanAntara = $currentReviewer->apakah_review_antara;
@endphp

@if ($apakahLaporanAntara)
    @php
        $msgType = 'helper';
        $msgInfo = '';
    @endphp

    @if (!$isCanLaporanAntara)
        @php
            $msgInfo =
                'Anda dapat memberikan penilaian pada tanggal awal <b>' .
                Carbon\Carbon::parse($timelineLaporanAntara->waktu_mulai)->translatedFormat('d F Y') .
                '</b> sampai <b>' .
                Carbon\Carbon::parse($timelineLaporanAntara->waktu_selesai)->translatedFormat('d F Y') .
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
                <h3>Berikan Penilaian Laporan Antara</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan berikan penilaian berdasarkan dokumen & hasil presentasi </p>
            </div>
            @if ($isCanLaporanAntara)
                @if (!$isEdit)
                    <div>
                        <a href="javascript::void(0)" wire:click="progreport_editPenilaianAntara"
                            class="btn btn_primary btn_xs" style="">
                            <span class="icon icon-pencil"></span>
                            <span class="btn__text">Beri Penilaian</span></a>
                    </div>
                @else
                    <div style="display: flex; gap: 8px;">
                        <a href="javascript::void(0)" wire:click="progreport_cancelPenilaianAntara"
                            class="btn btn_outline btn_xs" style="">
                            <span class="btn__text">Batal</span></a>
                        <a href="javascript::void(0)" wire:click="progreport_savePenilaianAntara"
                            class="btn btn_primary btn_xs" style="">
                            <span class="btn__text">Simpan</span></a>
                    </div>
                @endif
            @endif
        </div>
        <hr>

        <br>

        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Laporan</th>
                            <th>Dokumen yang Diupload</th>
                            <th style="width: 30%;">Feedback Reviewer</th>
                            <th style="width: 25%;">Status Antara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $optionStatus = Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres::STATUS_LAP_OPTIONS;
                            unset($optionStatus[Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres::STATUS_LAP_BELUM_DINILAI]);
                        @endphp
                        @foreach ($fields as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres::JENIS_LAP[$item['jenis_laporan_progres']] }}</td>
                                <td>
                                    @if ($item['id_dokumen_laporan_progress'])
                                        @php
                                            $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item['id_dokumen_laporan_progress'])->first();
                                            $ext = $simpleFile->extension_versi_terbaru;
                                            $ext = $ext == 'docx' ? 'doc' : $ext;
                                            $assetUrl = asset("images/$ext-solid.svg");
                                            $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                                        @endphp
                                        <a href="{{ $tempUrl }}" rel="noopener" target="_blank"
                                            class="util_d-flex util_flex-center-vertical" style="gap: 10px;">
                                            <img height="20px;" src="{{ $assetUrl }}"
                                                alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                                            {{ $simpleFile['nama_dokumen'] }}
                                        </a>
                                    @else
                                        Belum diunggah
                                    @endif
                                </td>
                                <td>
                                    <div class="form-control__group {{ isset($fieldErrors[$item['jenis_laporan_progres']]) ? 'error' : '' }}">
                                        @if (!$isEdit)
                                            {{ $item['feedback_reviewer'] ?? 'Tidak ada Feedback' }}
                                        @else
                                            <textarea wire:change="progreport_setFeedback('{{ $item['jenis_laporan_progres'] }}', $event.target.value)"
                                                class="form-control__input textarea">{{ $records['feedback'][$item['jenis_laporan_progres']] ?? null }}</textarea>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if (!$isEdit)
                                        @php
                                            $colorVariant = $item['status'] === Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI ? 'success' : 'warning';
                                        @endphp
                                        <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                            {{ $item['status'] ? ucfirst($item['status']) : 'Belum Dinilai' }}
                                        </x-core::badge>
                                    @else
                                        <div class="form-control__group {{ empty($records['status'][$item['jenis_laporan_progres']]) ? 'error' : '' }}">
                                            <x-core::controls.select label="Status" purpose="form"
                                                :selected="$records['status'][$item['jenis_laporan_progres']] ?? null"
                                                wire:change="progreport_setStatusPenilaian('{{ $item['jenis_laporan_progres'] }}', $event.target.value)"
                                                :options="$optionStatus" />
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <br>
        <h4>Komentar umum</h4>
        <p style="margin-top: 10px; color: #697586;">
            @if (!$isEdit)
                {{ $currentReviewer['komentar_umum_reviewer_progress_report'] ?? 'Anda belum memberikan komentar umum'}}
            @else
                <div class="form-control__group">
                    <textarea wire:change="progreport_setKomentarUmum($event.target.value)"
                        class="form-control__input textarea">{{ $records['komentar_umum'] ?? null }}</textarea>
                </div>
            @endif
        </p>
    </div>
@else
    <div class="card card_details-custom">
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">
                            Tidak bertugas sebagai Reviewer Antara
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Anda tidak ditunjuk sebagai <b>Reviewer Antara</b> pada proposal ini, Anda bisa mengabaikan halaman ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
