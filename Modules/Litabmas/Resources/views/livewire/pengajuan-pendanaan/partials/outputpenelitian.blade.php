@php
    $agendaLuaran = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENILAIAN_LUARAN;
    });

    $agendaLuaran = array_values($agendaLuaran)[0];

    // pengecekan status Dosen
    $isAct = $isDosen && $isLolosPendanaan && $agendaLuaran->active;

    // pengecekan status Admin
    if (!$isDosen && $isLolosPendanaan && $agendaLuaran->active) {
        $agendaReviewer = array_filter($timelines, function ($item) {
            return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
        });

        if (empty($agendaReviewer)) {
            $isAct = true;
        }
    }
@endphp

@if (!$isAct)
    <div class="alert alert_helper">
        <div class="alert__content">
            <p>
                Pengisian Luaran dapat dilakukan selama agenda <b>Pengumpulan Luaran</b> berlangsung. yaitu pada tanggal <b>{{ Carbon\Carbon::parse($agendaLuaran->waktu_mulai)->translatedFormat('d F Y') }}</b> sampai
                    <b>{{ Carbon\Carbon::parse($agendaLuaran->waktu_selesai)->translatedFormat('d F Y') }}</b>
            </p>
        </div>
    </div>
    <br>
@endif

<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Laporan Luaran Penelitian</h3>
            @if (!$isDosen)
                <p style="color: #697586; margin-top: 5px;">Daftar luaran beserta hasil peninjauan dari reviewer dan komentar umum pada presentasi luaran</p>
            @else
                <p style="color: #697586; margin-top: 5px;">Silakan mengunggah laporan luaran dari penelitian atau pengabdian yang dilakukan untuk ditinjau oleh reviewer</p>
            @endif
        </div>

        @if ($isAct && !$isDosen)
            @if (!$isEditNilai)
                <div>
                    <a href="javascript::void(0)" wire:click="outputpenilaian_editNilai"
                        class="btn btn_primary btn_xs" style="">
                        <span class="icon icon-pencil"></span>
                        <span class="btn__text">Beri Penilaian</span></a>
                </div>
            @else
                <div style="display: flex; gap: 8px;">
                    <a href="javascript::void(0)" wire:click="outputpenilaian_cancelNilai"
                        class="btn btn_outline btn_xs" style="">
                        <span class="btn__text">Batal</span></a>
                    <a href="javascript::void(0)" wire:click="outputpenilaian_saveNilai"
                        class="btn btn_primary btn_xs" style="">
                        <span class="btn__text">Simpan</span></a>
                </div>
            @endif
        @endif
    </div>

    <br>

    <div class="box-table__content" id="table-docs" style="border: 0px;">
        <div class="table-max">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Laporan</th>
                        <th>Dokumen yang Diupload</th>
                        @foreach ($dataReviewerLuaranOnly as $item)
                            <th style="text-align: center;">Reviewer
                                {{ $item->reviewer_ke }}<br>{{ $item->nama_reviewer }}</th>
                        @endforeach
                        <th style="width: 13%;">Status Luaran</th>
                        @if ($isAct && $isDosen)
                            <th class="cell-action cell-center" style="width: 5%;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $reviewerFeedbacks = Modules\Litabmas\Models\PenilaianReviewerOutput::join('litabmas.pengajuan_pendanaan_output_penelitian', 'litabmas.penilaian_reviewer_output.id_pengajuan_pendanaan_output_penelitian', '=', 'litabmas.pengajuan_pendanaan_output_penelitian.id')
                            ->whereIn('id_pengajuan_pendanaan_reviewer', $dataReviewerLuaranOnly->pluck('id'))
                            ->select('litabmas.penilaian_reviewer_output.*', 'litabmas.pengajuan_pendanaan_output_penelitian.id_jenis_output_penelitian')
                            ->get();

                        $defaultData = [
                            Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI => 0,
                            Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI => 0,
                            Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI => 0,
                        ];

                        $grouped = [];
                        foreach ($dataPenilaianReviewerOutput as $val) {
                            if (!isset($grouped[$val->id_pengajuan_pendanaan_output_penelitian])) {
                                $grouped[$val->id_pengajuan_pendanaan_output_penelitian] = $defaultData;
                            }

                            $grouped[$val->id_pengajuan_pendanaan_output_penelitian][$val->status_penilaian_output]++;
                        }

                        foreach ($grouped as $key => $val) {
                            arsort($val);
                            $grouped[$key] = $val;
                        }

                        $dataOutputPenelitian = $dataOutputPenelitian->sortByDesc('apakah_wajib');
                    @endphp
                    @foreach ($dataOutputPenelitian as $item)
                        @php
                            $is2Reviewer = false;

                            if (isset($grouped[$item->id])) {
                                if (count($dataReviewerLuaranOnly) == 2 && array_sum($grouped[$item->id]) != 2) {
                                    $is2Reviewer = true;

                                    $currentStatus = Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI;
                                }
                            }

                            if (!$is2Reviewer) {
                                $arrStatus = $grouped[$item->id] ?? $defaultData;

                                $currentStatus = key($arrStatus);

                                $compareWith = $currentStatus === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI ?
                                    Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI : Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI;

                                if ($currentStatus != Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI && $arrStatus[$currentStatus] === $arrStatus[$compareWith]) {
                                    $currentStatus = Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_output }} @if($item->apakah_wajib) <span style="color: red;">(Wajib)</span> @endif</td>
                            <td>
                                @if ($item->id_dokumen_output)
                                    @php
                                        $simpleFile = Modules\DMS\Models\Dokumen::where(
                                            'id',
                                            $item->id_dokumen_output,
                                        )->first();
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
                            @foreach ($dataReviewerLuaranOnly as $reviewer)
                                @php
                                    $feedback = $reviewerFeedbacks->where('id_pengajuan_pendanaan_reviewer', $reviewer->id)
                                        ->where('id_jenis_output_penelitian', $item->id_jenis_output_penelitian)
                                        ->first();
                                    $colorVariant = $feedback?->status_penilaian_output === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI ? 'success' : 'warning';
                                @endphp
                                <td>
                                    @if (!empty($feedback))
                                        <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                            {{ ucwords(str_replace('_', ' ', $feedback->status_penilaian_output)) }}
                                        </x-core::badge> <br>
                                    @endif
                                    {{ $feedback->feedback_output ?? 'Belum ada Feedback' }}
                                </td>
                            @endforeach
                            <td>
                                @if (!$isEditNilai)
                                    @php
                                        $colorVariant = $currentStatus === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI ? 'success' : 'warning';
                                    @endphp
                                    <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                        {{ ucwords(str_replace('_', ' ', $currentStatus)) }}
                                    </x-core::badge>
                                @else
                                    @php
                                        $optionStatus = Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_OPTIONS;
                                        unset($optionStatus[Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI]);
                                    @endphp
                                    <div class="form-control__group">
                                        <x-core::controls.select label="Status" purpose="form"
                                            :selected="$records['status'][$item->id] ?? null"
                                            wire:change="outputpenilaian_setPenilaian('{{ $item->id }}', $event.target.value)"
                                            :options="$optionStatus" />
                                    </div>
                                @endif
                            </td>
                            @if ($isAct && $isDosen)
                                <td class="cell-action cell-center">
                                    <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                        <button type="button"
                                            wire:click="outputpenilaian_addForm('{{ $item->id }}')"
                                            class="btn btn_outline btn_xs">
                                            <span class="icon icon-arrow-up-tray"></span>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <br>
    <h4>Komentar umum</h4>
    <br>
    <div>
        @php
            $reviewerFeedbacks = Modules\Litabmas\Models\PengajuanPendanaanReviewer::whereIn('id', $dataReviewerLuaranOnly->pluck('id'))->get();
        @endphp

        @foreach ($dataReviewerLuaranOnly as $reviewer)
            @php
                $feedback = $reviewerFeedbacks->where('id', $reviewer->id)->first();
                $urut = $reviewer->reviewer_ke;
            @endphp
            <div class="color-auto">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar__circle" style="min-width: 35px; width: 35px; min-height: 35px; height: 35px;">
                        <p class="avatar__acronym">
                            {{ substr($reviewer->nama_reviewer, 0, 2) }}
                        </p>
                    </div>
                    <h4 style="color: #222222">{{ $reviewer->nama_reviewer }} (Reviewer {{ $urut }})</h4>
                    {!! $feedback->komentar_umum_reviewer_output ? ('&bull; '.$feedback->waktu_diubah->translatedFormat('d F Y')) : '' !!}
                </div>
                <p
                    style="margin-top: 10px; max-width: 450px; color: {{ !$feedback->komentar_umum_reviewer_output ? '#697586' : '' }}">
                    {{ $feedback->komentar_umum_reviewer_output ?? 'Reviewer belum memberikan komentar umum' }}
                </p>
            </div>
            <br>
        @endforeach
    </div>
</div>

<br>

<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Hasil Penilaian Akhir Luaran (Bersama)</h3>
            <p style="color: #697586; margin-top: 5px;">Penilaian dilakukan setelah peninjauan luaran penelitian oleh semua reviewer berdasarkan kesepakatan bersama</p>
        </div>
    </div>

    <br>

    <div class="box-table__content" id="table-docs" style="border: 0px;" wire:ignore>
        <div class="table-max">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kriteria Penilaian Luaran</th>
                        <th>Jawaban Reviewer</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $jawabanReviewerBersama = Modules\Litabmas\Models\PenilaianReviewerOutputBersama::where('id_pengajuan_pendanaan', $currentData['id'])
                            ->join('litabmas.aspek_penilaian_output_jawaban as jawaban', 'litabmas.penilaian_reviewer_output_bersama.id_aspek_penilaian_output_jawaban', '=', 'jawaban.id')
                            ->select('litabmas.penilaian_reviewer_output_bersama.*', 'jawaban.jawaban_penilaian_output')
                            ->get();
                    @endphp
                    @foreach ($dataAspekPenilaianOutput as $item)
                        @php
                            $jawaban = $jawabanReviewerBersama->where('id_aspek_penilaian_output_pertanyaan', $item->id)->first();
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->pertanyaan_penilaian_output }}</td>
                            <td>
                                {{ $jawaban->jawaban_penilaian_output ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <br>
    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Catatan Khusus</h3>
            </div>
            @if (!$isDosen)
                @if (!$isEdit)
                    <div style="color: #0F6AF5; cursor: pointer;" wire:click="outputpenilaian_editNote">
                        <b><span class="icon icon-pencil-solid"></span> Tambah Catatan</b>
                    </div>
                @else
                    <div style="display: flex; gap: 8px;">
                        <a href="javascript::void(0)" wire:click="outputpenilaian_cancelEditNote" class="btn btn_outline btn_xs"
                            style="">
                            <span class="btn__text">Batal</span></a>
                        <a href="javascript::void(0)" wire:click="outputpenilaian_saveNote" class="btn btn_primary btn_xs"
                            style="">
                            <span class="btn__text">Simpan Catatan</span></a>
                    </div>
                @endif
            @endif
        </div>
        @if (!$isEdit)
            <p style="margin-top: 10px; color: #697586;">{{ $currentData['kesimpulan_output_bersama'] ?? (!$isDosen ? 'Masukkan catatan khusus berdasarkan keseluruhan proses penelitian serta hasil rekomendasi dari reviewer' : 'Catatan hasil penelitian dan rekomendasi reviewer akan muncul disini setelah diisi oleh reviewer') }}</p>
        @else
            <textarea wire:change="outputpenilaian_setNote($event.target.value)"
                class="form-control__input textarea" style="margin-top: 10px; height: 100px;">{{ $currentData['kesimpulan_output_bersama'] ?? '' }}</textarea>
        @endif
    </div>
</div>

<x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} Dokumen Laporan Luaran" variant="primary"
    id="modal-laporan-luaran" width="600px" wire:ignore.self>
    <x-core::form method="POST">
        <x-core::modal.body>
            @foreach ($fields as $item)
                @php
                    $item['name'] ??= $item['field'];
                    unset($item['field']);

                    $attributes = Page::buildAttributes($item);
                @endphp
                <x-core::controls.form {{ $attributes }} />
            @endforeach

            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>

                    <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit" wire:click="outputpenilaian_submitForm">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>
