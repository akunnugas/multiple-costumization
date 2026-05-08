@php
    $agendaLaporanAntara = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA;
    });

    $tables = [
        [
            'nama_laporan' => 'Laporan Progress',
            'jenis_laporan' => Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES,
        ],
        [
            'nama_laporan' => 'Laporan Keuangan Sementara',
            'jenis_laporan' =>
                Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA,
        ],
    ];
@endphp

@if (!empty($agendaLaporanAntara))
    @php
        $agendaLaporanAntara = array_values($agendaLaporanAntara)[0];

        $isAct = $isDosen && $isLolosPendanaan && $agendaLaporanAntara->active;
    @endphp
    <div class="alert alert_helper">
        <div class="alert__content">
            @if ($isAct)
            <p><b>Upload file Laporan Kegiatan Penelitian di sini</b><br>
                1. File hanya dalam format PDF dengan ukuran maksimum 10 MB <br>
                2. Laporan Antara berisi laporan perkembangan penelitian hingga penyajian data dan revisi proposal berdasarkan masukan reviewer (Bab 1-3)</p>
            @else
            <p>
                Pengisian Laporan Antara dapat dilakukan selama agenda <b>Pengumpulan Laporan Antara</b> berlangsung. yaitu pada tanggal <b>{{ Carbon\Carbon::parse($agendaLaporanAntara->waktu_mulai)->translatedFormat('d F Y') }}</b> sampai
                <b>{{ Carbon\Carbon::parse($agendaLaporanAntara->waktu_selesai)->translatedFormat('d F Y') }}</b>
            </p>
            @endif
        </div>
    </div>
    <br>

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Laporan Antara</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan mengunggah laporan antara penelitian atau pengabdian yang akan tinjau oleh reviewer</p>
            </div>
        </div>
        <hr>

        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Laporan</th>
                            <th>Dokumen yang Diupload</th>
                            @foreach ($dataReviewerAntaraOnly as $item)
                                <th style="text-align: center;">Reviewer
                                    {{ $item->reviewer_ke }}<br>{{ $item->nama_reviewer }}</th>
                            @endforeach
                            <th style="width: 13%;">Status Antara</th>
                            @if ($isAct)
                                <th class="cell-action cell-center" style="width: 5%;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $reviewerFeedbacks = Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::join(
                                'litabmas.pengajuan_pendanaan_laporan_progres',
                                'pengajuan_pendanaan_laporan_progres.id',
                                '=',
                                'penilaian_reviewer_laporan_progres.id_pengajuan_pendanaan_laporan_progres'
                            )
                            ->whereIn('id_pengajuan_pendanaan_reviewer', $dataReviewerAntaraOnly->pluck('id'))
                            ->whereIn('jenis_laporan_progres', collect($tables)->pluck('jenis_laporan'))
                            ->get();

                            $defaultData = [
                                Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI => 0,
                                Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI => 0,
                                Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI => 0,
                            ];

                            $grouped = [];
                            foreach ($dataPenilaianReviewerOutput as $val) {
                                if (!isset($grouped[$val->jenis_laporan_progres])) {
                                    $grouped[$val->jenis_laporan_progres] = $defaultData;
                                }

                                $grouped[$val->jenis_laporan_progres][$val->status_laporan_progres_reviewer]++;
                            }

                            foreach ($grouped as $key => $val) {
                                arsort($val);
                                $grouped[$key] = $val;
                            }
                        @endphp
                        @foreach ($tables as $item)
                            @php
                                $is2Reviewer = false;

                                if (isset($grouped[$item['jenis_laporan']])) {
                                    if (count($dataReviewerAntaraOnly) == 2 && array_sum($grouped[$item['jenis_laporan']]) != 2) {
                                        $is2Reviewer = true;

                                        $currentStatus = Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI;
                                    }
                                }

                                if (!$is2Reviewer) {
                                    $arrStatus = $grouped[$item['jenis_laporan']] ?? $defaultData;

                                    $currentStatus = key($arrStatus);

                                    $compareWith = $currentStatus === Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI ?
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI : Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI;

                                    if ($currentStatus != Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI && $arrStatus[$currentStatus] === $arrStatus[$compareWith]) {
                                        $currentStatus = Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI;
                                    }
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['nama_laporan'] }}</td>
                                <td>
                                    @if (isset($dataLaporanAntara[$item['jenis_laporan']]))
                                        @php
                                            $simpleFile = Modules\DMS\Models\Dokumen::where(
                                                'id',
                                                $dataLaporanAntara[$item['jenis_laporan']]->id_dokumen_laporan_progres,
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
                                @foreach ($dataReviewerAntaraOnly as $reviewer)
                                    @php
                                        $feedback = $reviewerFeedbacks->where('id_pengajuan_pendanaan_reviewer', $reviewer->id)
                                            ->where('jenis_laporan_progres', $item['jenis_laporan'])
                                            ->first();
                                        $colorVariant = $feedback?->status_laporan_progres_reviewer === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI ? 'success' : 'warning';
                                    @endphp
                                    <td>
                                        @if (!empty($feedback))
                                            <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                                {{ ucwords(str_replace('_', ' ', $feedback->status_laporan_progres_reviewer)) }}
                                            </x-core::badge> <br>
                                        @endif
                                        {{ $feedback->feedback_laporan_progres ?? 'Belum ada Feedback' }}
                                    </td>
                                @endforeach
                                <td>
                                    @php
                                    $colorVariant = $currentStatus === Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI ? 'success' : 'warning';
                                @endphp
                                    <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                        {{ ucwords(str_replace('_', ' ', $currentStatus)) }}
                                    </x-core::badge>
                                </td>
                                @if ($isAct)
                                    <td class="cell-action cell-center">
                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                            <button type="button"
                                                wire:click="progreport_addForm('{{ $item['jenis_laporan'] }}')"
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
                $reviewerFeedbacks = Modules\Litabmas\Models\PengajuanPendanaanReviewer::whereIn('id', $dataReviewerAntaraOnly->pluck('id'))->get();
            @endphp

            @foreach ($dataReviewerAntaraOnly as $reviewer)
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
                        {!! $feedback->komentar_umum_reviewer_progress_report ? ('&bull; '.$feedback->waktu_diubah->translatedFormat('d F Y')) : '' !!}
                    </div>
                    <p
                        style="margin-top: 10px; max-width: 450px; color: {{ !$feedback->komentar_umum_reviewer_progress_report ? '#697586' : '' }}">
                        {{ $feedback->komentar_umum_reviewer_progress_report ?? 'Reviewer belum memberikan komentar umum' }}
                    </p>
                </div>
                <br>
            @endforeach
        </div>
    </div>

    <x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} Dokumen Laporan Antara" variant="primary"
        id="modal-laporan-antara" width="600px" wire:ignore.self>
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

                        <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit" wire:click="progreport_submitForm">
                            Simpan
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
@else
    <div class="card card_details-custom">
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">
                            Tidak ada Tahapan Kegiatan Pengumpulan Laporan Antara
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Klaster pada proposal ini tidak menggunakan agenda Pengumpulan Laporan Antara, Anda bisa mengabaikan halaman ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
