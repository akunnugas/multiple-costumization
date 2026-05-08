<div id="prinout">
    <div class="card card_details-custom">
        <h3>{{ $data['pertanyaan-penelitian']['title'] }}</h3>
        <div class="card__body" style="margin-top: 18px;">
            <div class="grid cols-3">
                @foreach ($data['pertanyaan-penelitian']['items'] as $item)
                    <label class="row-data-name">{{ $item['label'] }}</label>
                    <div class="col-2">
                        @if ($item['field'] === 'nominal_anggaran_disetujui')
                            <span class="row-data-value">
                                <span class="row-data-value__colon">: </span>{{ !empty($item['original']) ? money($item['original']) : '-' }}
                            </span>
                        @else
                            <span class="row-data-value">
                                <span class="row-data-value__colon">: </span>{{ $item['text'] }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <br>

        <hr>
        <h3>Komponen Proposal</h3>
        <div class="card__body" style="margin-top: 18px;">
            <div class="grid cols-3">
                @foreach ($dataIsianProposal as $item)
                    @php
                        $txtData = $item->isian_proposal;
                        $txtData = str_replace("\n", '<br>', $txtData);
                    @endphp
                    <label class="row-data-name">{{ $item->nama_isian_proposal }}</label>
                    <div class="col-2">
                        <span class="row-data-value"><span
                                class="row-data-value__colon">:</span>{!! $txtData !!}</span>
                    </div>
                @endforeach
                <label class="row-data-name">Dokumen Proposal</label>
                <div class="col-2">
                    <span class="row-data-value"><span class="row-data-value__colon">:</span>
                        @php
                            $simpleFile = null;
                            if (!empty($currentData['id_dokumen_proposal'])) {
                                $simpleFile = Modules\DMS\Models\Dokumen::where(
                                    'id',
                                    $currentData['id_dokumen_proposal'],
                                )->first();
                            }
                        @endphp
                        @if ($simpleFile)
                            @php
                                $ext = $simpleFile->extension_versi_terbaru;
                                $ext = $ext == 'docx' ? 'doc' : $ext;
                                $assetUrl = asset("images/$ext-solid.svg");
                                $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                            @endphp
                            <a href="{{ $tempUrl }}" rel="noopener" target="_blank"
                                class="util_d-flex util_flex-center-vertical" style="color: #0F6AF5; gap: 10px;">
                                <img height="20px;" src="{{ $assetUrl }}"
                                    alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                                {{ $simpleFile['nama_dokumen'] }}
                            </a>
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <br>
        <hr>
        <h3>Data Peneliti</h3>
        <br>
        {{-- Anggota Dosen --}}
        @php
            $noDosen = 0;

            $fieldDosen = [];
            foreach ($dataPenelitiDosen as $key => $item) {
                $fieldDosen[$key] = $data['pertanyaan-penelitian']['item_dosen'];
                foreach ($fieldDosen[$key] as $fieldKey => $fieldItem) {
                    $fieldDosen[$key][$fieldKey]['is_ketua'] = $item->apakah_ketua;
                    $fieldDosen[$key][$fieldKey]['apakah_approve'] = $item->apakah_undangan_diterima;
                    $fieldDosen[$key][$fieldKey]['jenis_anggota'] = $item->jenis_anggota;
                    $fieldDosen[$key][$fieldKey]['text'] = $item->{$fieldItem['field']};
                    $fieldDosen[$key][$fieldKey]['original'] = $item->{$fieldItem['field']};
                }
            }
        @endphp
        <div class="card__body">
            <div class="grid cols-3">
                @foreach ($fieldDosen as $item)
                    @php
                        $isKetua = $item[0]['is_ketua'];
                        $isApprove = $item[0]['apakah_approve'];
                        if (!$isKetua) {
                            $noDosen++;
                        }
                        foreach ($item as $key => $value) {
                            if ($value['field'] === 'nama') {
                                if ($item[$key]['jenis_anggota'] === 1) {
                                    $item[$key]['text'] .= ' - Dosen Internal';
                                    $item[$key]['original'] .= ' - Dosen Internal';
                                } else {
                                    $item[$key]['text'] .= ' - Dosen Eksternal';
                                    $item[$key]['original'] .= ' - Dosen Eksternal';
                                }
                            }
                        }
                        $rowTitle = $isKetua ? 'Data Ketua Dosen' : "Data Anggota Dosen $noDosen";
                    @endphp
                    <label class="row-data-name">
                        <div style="display: flex; gap: 10px;">
                            <h4>{{ $rowTitle }}</h4>
                            @if ($currentData['apakah_butuh_approve_semua_anggota'] && !$isKetua)
                                @if (is_null($isApprove))
                                    <x-core::badge variant="warning" type="outline" size="sm" class="util_ml-8">
                                        Menunggu Persetujuan
                                    </x-core::badge>
                                @elseif (!$isApprove)
                                    <x-core::badge variant="danger" type="outline" size="sm" class="util_ml-8">
                                        Ditolak
                                    </x-core::badge>
                                @elseif ($isApprove)
                                    <x-core::badge variant="success" type="outline" size="sm" class="util_ml-8">
                                        Diterima
                                    </x-core::badge>
                                @endif
                            @endif
                        </div>
                    </label>
                    <label for=""></label>
                    <label for=""></label>
                    @foreach ($item as $obj)
                        <label class="row-data-name">{{ $obj['label'] }}</label>
                        <div class="col-2">
                            <span class="row-data-value"><span
                                    class="row-data-value__colon">:</span>{{ $obj['text'] }}</span>
                        </div>
                    @endforeach
                    <label for=""></label>
                    <label for=""></label>

                    @if (!$loop->last)
                        <br>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Anggota Mahasiswa --}}
        @php
            $noMahasiswa = 1;

            $fieldMahasiswa = [];
            foreach ($dataPenelitiMahasiswa as $key => $item) {
                $fieldMahasiswa[$key] = $data['pertanyaan-penelitian']['item_dosen'];
                foreach ($fieldMahasiswa[$key] as $fieldKey => $fieldItem) {
                    $fieldMahasiswa[$key][$fieldKey]['is_ketua'] = $item->apakah_ketua;
                    $fieldMahasiswa[$key][$fieldKey]['text'] = $item->{$fieldItem['field']};
                    $fieldMahasiswa[$key][$fieldKey]['original'] = $item->{$fieldItem['field']};

                    if ($fieldItem['field'] == 'kustom_kode') {
                        $fieldMahasiswa[$key][$fieldKey]['label'] = 'NIM';
                    }
                }
            }
        @endphp
        <div class="card__body">
            <div class="grid cols-3">
                @foreach ($fieldMahasiswa as $item)
                    @php
                        $rowTitle = 'Data Anggota Mahasiswa ' . $noMahasiswa++;
                    @endphp
                    <label class="row-data-name">
                        <div style="display: flex; gap: 10px;">
                            <h4>{{ $rowTitle }}</h4>
                        </div>
                    </label>
                    <label for=""></label>
                    <label for=""></label>
                    @foreach ($item as $obj)
                        <label class="row-data-name">{{ $obj['label'] }}</label>
                        <div class="col-2">
                            <span class="row-data-value"><span
                                    class="row-data-value__colon">:</span>{{ $obj['text'] }}</span>
                        </div>
                    @endforeach
                    <label for=""></label>
                    <label for=""></label>

                    @if (!$loop->last)
                        <br>
                    @endif
                @endforeach
            </div>
        </div>
        <hr>
        <h3>Aktivitas Peneliti</h3>
        <br>
        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Aktivitas</th>
                            <th>Nama Aktivitas</th>
                            <th>Tempat Aktivitas</th>
                            <th>Dokumen Pendukung</th>
                            <th class="cell-center">Feedback Pembimbing @if (!empty($currentData['nama_pembimbing']))
                                    <br> {{ $currentData['nama_pembimbing'] }}
                                @endif
                            </th>
                            <th>Dokumen Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataAktivitasPenelitian as $item)
                            @php
                                $item = (object) $item;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Carbon\Carbon::parse($item->tanggal_aktivitas_penelitian)->translatedFormat('d F Y') }}
                                </td>
                                <td>{{ $item->nama_aktivitas_penelitian }}</td>
                                <td>{{ $item->lokasi_aktivitas_penelitian }}</td>
                                <td>
                                    @if ($item->id_dokumen_logbook)
                                        @php
                                            $simpleFile = Modules\DMS\Models\Dokumen::where(
                                                'id',
                                                $item->id_dokumen_logbook,
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
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->feedback_logbook ?? '-' }}</td>
                                <td>
                                    @if ($item->id_dokumen_feedback_logbook)
                                        @php
                                            $simpleFile = Modules\DMS\Models\Dokumen::where(
                                                'id',
                                                $item->id_dokumen_feedback_logbook,
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
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if (count($dataAktivitasPenelitian) == 0)
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">
                                Belum ada Aktivitas Peneliti
                            </h1>
                            <p style="text-align: start; max-width: 550px;">
                                Tidak ada aktivitas penelitian yang tercatat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <hr>
        <h3>Laporan Antara</h3>
        <br>
        @php
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
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $reviewerFeedbacks = Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::join(
                                'litabmas.pengajuan_pendanaan_laporan_progres',
                                'pengajuan_pendanaan_laporan_progres.id',
                                '=',
                                'penilaian_reviewer_laporan_progres.id_pengajuan_pendanaan_laporan_progres',
                            )
                                ->whereIn('id_pengajuan_pendanaan_reviewer', $dataReviewerAntaraOnly->pluck('id'))
                                ->whereIn('jenis_laporan_progres', collect($tables)->pluck('jenis_laporan'))
                                ->get();

                            $grouped = [];
                            foreach ($dataPenilaianReviewerOutput as $val) {
                                if (!isset($grouped[$val->jenis_laporan_progres])) {
                                    $grouped[$val->jenis_laporan_progres] = [
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI => 0,
                                    ];
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
                                $arrStatus = isset($grouped[$item['jenis_laporan']])
                                    ? $grouped[$item['jenis_laporan']]
                                    : [
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI => 0,
                                    ];

                                $currentStatus = key($arrStatus);

                                $compareWith =
                                    $currentStatus ===
                                    Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI
                                        ? Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI
                                        : Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI;

                                if (
                                    $currentStatus !=
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI &&
                                    $arrStatus[$currentStatus] === $arrStatus[$compareWith]
                                ) {
                                    $currentStatus =
                                        Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI;
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
                                        $feedback = $reviewerFeedbacks
                                            ->where('id_pengajuan_pendanaan_reviewer', $reviewer->id)
                                            ->where('jenis_laporan_progres', $item['jenis_laporan'])
                                            ->first();
                                    @endphp
                                    <td>{{ $feedback->feedback_laporan_progres ?? 'Belum ada Feedback' }}</td>
                                @endforeach
                                <td>
                                    @php
                                        $colorVariant =
                                            $currentStatus ===
                                            Modules\Litabmas\Models\PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI
                                                ? 'success'
                                                : 'warning';
                                    @endphp
                                    <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                        {{ ucwords(str_replace('_', ' ', $currentStatus)) }}
                                    </x-core::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <hr>
        <h3>Laporan Luaran</h3>
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
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $reviewerFeedbacks = Modules\Litabmas\Models\PenilaianReviewerOutput::join(
                                'litabmas.pengajuan_pendanaan_output_penelitian',
                                'litabmas.penilaian_reviewer_output.id_pengajuan_pendanaan_output_penelitian',
                                '=',
                                'litabmas.pengajuan_pendanaan_output_penelitian.id',
                            )
                                ->whereIn('id_pengajuan_pendanaan_reviewer', $dataReviewerLuaranOnly->pluck('id'))
                                ->select(
                                    'litabmas.penilaian_reviewer_output.*',
                                    'litabmas.pengajuan_pendanaan_output_penelitian.id_jenis_output_penelitian',
                                )
                                ->get();

                            $grouped = [];
                            foreach ($dataPenilaianReviewerLuaran as $val) {
                                if (!isset($grouped[$val->id_pengajuan_pendanaan_output_penelitian])) {
                                    $grouped[$val->id_pengajuan_pendanaan_output_penelitian] = [
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI => 0,
                                    ];
                                }

                                $grouped[$val->id_pengajuan_pendanaan_output_penelitian][
                                    $val->status_penilaian_output
                                ]++;
                            }

                            foreach ($grouped as $key => $val) {
                                arsort($val);
                                $grouped[$key] = $val;
                            }

                            $dataOutputPenelitian = $dataOutputPenelitian->sortByDesc('apakah_wajib');
                        @endphp
                        @foreach ($dataOutputPenelitian as $item)
                            @php
                                $arrStatus = isset($grouped[$item->id])
                                    ? $grouped[$item->id]
                                    : [
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI => 0,
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI => 0,
                                    ];

                                $currentStatus = key($arrStatus);

                                $compareWith =
                                    $currentStatus === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI
                                        ? Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI
                                        : Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI;

                                if (
                                    $currentStatus !=
                                        Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI &&
                                    $arrStatus[$currentStatus] === $arrStatus[$compareWith]
                                ) {
                                    $currentStatus = Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI;
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_output }} @if ($item->apakah_wajib)
                                        <span style="color: red;">(Wajib)</span>
                                    @endif
                                </td>
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
                                        $feedback = $reviewerFeedbacks
                                            ->where('id_pengajuan_pendanaan_reviewer', $reviewer->id)
                                            ->where('id_jenis_output_penelitian', $item->id_jenis_output_penelitian)
                                            ->first();
                                    @endphp
                                    <td>{{ $feedback->feedback_output ?? 'Belum ada Feedback' }}</td>
                                @endforeach
                                <td>
                                    @php
                                        $colorVariant =
                                            $currentStatus ===
                                            Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI
                                                ? 'success'
                                                : 'warning';
                                    @endphp
                                    <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                        {{ ucwords(str_replace('_', ' ', $currentStatus)) }}
                                    </x-core::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <hr>
        <h3>Publikasi Artikel</h3>
        <br>
        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max" wire:ignore.self>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Artikel</th>
                            <th>Tempat Jurnal</th>
                            <th>Volume & Nomor Terbitan</th>
                            <th>URL Artikel</th>
                            <th>Tanggal Pengumpulan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataArtikelPublikasi->items as $item)
                            @php
                                $item = (object) $item;
                                $judul = $item->judul_artikel;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $judul }}
                                </td>
                                <td>{{ $item->situs_publikasi_jurnal }}</td>
                                <td>{{ $item->volume_dan_nomor_terbitan }}</td>
                                <td>
                                    <a href="{{ $item->url_artikel }}">{{ $item->url_artikel }}</a>
                                </td>
                                <td>{{ Carbon\Carbon::parse($item->waktu_dibuat)->translatedFormat('d F Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if (count($dataArtikelPublikasi->items) == 0)
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">Belum ada Artikel yang dipublikasikan</h1>
                            <p style="text-align: start; max-width: 550px;">
                                Saat ini, belum ada data publikasi yang diunggah. Hasil publikasi yang telah dilakukan
                                oleh peneliti akan ditampilkan di sini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <hr>
        <h3>Publikasi Buku</h3>
        <br>
        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max" wire:ignore.self>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Penerbit Buku</th>
                            <th>ISBN</th>
                            <th>Tahun Terbit</th>
                            <th>Tanggal Pengumpulan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataBukuPublikasi->items as $item)
                            @php
                                $item = (object) $item;
                                $judul = $item->judul_buku;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $judul }}
                                </td>
                                <td>{{ $item->penerbit_buku }}</td>
                                <td>{{ $item->isbn }}</td>
                                <td>{{ $item->tahun_terbit_buku }}</td>
                                <td>{{ Carbon\Carbon::parse($item->waktu_dibuat)->translatedFormat('d F Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if (count($dataBukuPublikasi->items) == 0)
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">Belum ada Buku yang dipublikasikan</h1>
                            <p style="text-align: start; max-width: 550px;">
                                Saat ini, belum ada data publikasi yang diunggah. Hasil publikasi yang telah dilakukan
                                oleh peneliti akan ditampilkan di sini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div id="prinout-ttd">
        <br>
        <div style="display: flex; justify-content:space-between">
            <div></div>
            <div>
                <div style="text-align: center">
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <br>
                <br>
                <br>
                @php
                    $biodata = auth()->user()->biodata;
                    $pegawai = $biodata->pegawai ?? null;
                @endphp
                <div style="text-align: center">
                    <span>{{ $pegawai->nip . ' - ' . ($pegawai->gelar_depan ?? '') . ' ' . $biodata->nama . ' ' . ($pegawai->gelar_belakang ?? '') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #prinout-ttd {
        display: none;
    }

    @media print {
        @page {
            size: A4;
            margin-right: 10;
            margin-left: 10;
        }

        * {
            font-size: 16px;
        }

        body * {
            visibility: hidden;
        }

        .sidebar {
            display: none;
        }

        #prinout,
        #prinout * {
            visibility: visible;
        }

        #prinout-ttd {
            display: block;
        }

        #prinout {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
