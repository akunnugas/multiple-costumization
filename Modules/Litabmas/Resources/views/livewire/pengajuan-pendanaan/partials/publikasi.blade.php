@php
    $lastTimeline = end($timelines);

    $isAct = $isLolosPendanaan && $isDosen;

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

    $isRevisi = false;
    foreach ($dataOutputPenelitian as $item) {
        $arrStatus = $grouped[$item->id] ?? $defaultData;

        $currentStatus = key($arrStatus);

        $compareWith = $currentStatus === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI ?
            Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI : Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DISETUJUI;

        if ($currentStatus != Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI && $arrStatus[$currentStatus] === $arrStatus[$compareWith]) {
            $currentStatus = Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI;
        }

        // cek ada yang revisi wajib
        if ($currentStatus === Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_DIREVISI && $item->apakah_wajib === true) {
            $isRevisi = true;
            break;
        }

        // cek apabila 2 reviewer luaran
        if (count($dataReviewerLuaranOnly) == 2 && array_sum($arrStatus) != 2) {
            $isRevisi = true;
            break;
        }
    }

    // cek agenda reviewer
    $agendaReviewer = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
    });

    if (empty($agendaReviewer) && empty($dataPenilaianReviewerOutput->count())) {
        $isRevisi = true;
    }
@endphp

@if (!$isRevisi)
    <div class="alert alert_helper">
        <div class="alert__content">
            @if ($isDosen)
                <p>Anda wajib memasukkan sampai pada tanggal <b>{{ Carbon\Carbon::parse($lastTimeline->waktu_selesai)->translatedFormat('d F Y') }}</b>, jika lebih dari tanggal tersebut Anda akan diblokir pada pengajuan
                    pendanaan periode selanjutnya.</p>
            @else
                <p>
                    Batas publikasi penelitian adalah pada tanggal <b>{{ Carbon\Carbon::parse($lastTimeline->waktu_selesai)->translatedFormat('d F Y') }}</b>.
                </p>
            @endif
        </div>
    </div>
@else
    @if ($isDosen)
        <div class="alert alert_warning">
            <div class="alert__content">
                <p>
                    Anda tidak bisa melakukan publikasi, karena masih ada luaran penelitian yang perlu direvisi.
                </p>
            </div>
        </div>
    @endif
@endif
<br>

<style>
    .tab {
        display: flex;
        border-bottom: 1px solid #ccc;
        margin-top: 10px;
    }

    .tab button {
        background-color: inherit;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
        font-size: 16px;
        color: #4B5565;
        font-weight: 500;
    }

    .tab button.active {
        color: #007bff;
        border-bottom: 2px solid #007bff;
        font-weight: bold;
    }

    .tab-content {
        display: none;
        padding: 16px;
    }

    .tab-content.active {
        display: block;
    }
</style>

<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Hasil Publikasi {{ str_replace('_', ' ', $currentData['kode_jenis_pendanaan']) }}</h3>
            @if (!$isDosen)
                <p style="color: #697586; margin-top: 5px;">Daftar hasil publikasi {{ strtolower(str_replace('_', ' ', $currentData['kode_jenis_pendanaan'])) }} yang dilakukan oleh peneliti.</p>
            @else
                <p style="color: #697586; margin-top: 5px;">Silakan mengunggah hasil publikasi {{ strtolower(str_replace('_', ' ', $currentData['kode_jenis_pendanaan'])) }} sesuai dengan kegiatan yang dilakukan.</p>
            @endif
        </div>
        @if ($isAct && !$isRevisi)
            <div>
                <a href="javascript::void(0)" wire:click="publikasi_addForm" class="btn btn_primary btn_xs" style="">
                    <span class="icon icon-plus"></span>
                    <span class="btn__text">Tambahkan Publikasi</span></a>
            </div>
        @endif
    </div>

    <div class="tab">
        <button class="tablinks {{ $activeTab == 1 ? 'active' : '' }}" wire:click="publikasi_ChangeTab(1)">Artikel</button>
        <button class="tablinks {{ $activeTab == 2 ? 'active' : '' }}" wire:click="publikasi_ChangeTab(2)">Buku</button>
    </div>

    <br>

    <div class="box-table__content" id="table-docs" style="border: 0px;">
        <div class="table-max" wire:ignore.self>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Outcome</th>
                        <th>Judul {{ $activeTab == 1 ? 'Artikel' : 'Buku' }}</th>
                        @if ($activeTab == 1)
                            <th>Tempat Jurnal</th>
                        @endif
                        <th>{{ $activeTab == 1 ? 'Volume & Nomor Terbitan' : 'Penerbit Buku' }}</th>
                        <th>{{ $activeTab == 1 ? 'URL Artikel' : 'ISBN' }}</th>
                        @if ($activeTab == 2)
                            <th>
                                Tahun Terbit
                            </th>
                        @endif
                        <th>Tanggal Pengumpulan</th>
                        @if ($isAct && !$isRevisi)
                            <th class="cell-action cell-center" style="width: 10%;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataArtikelPublikasi->items as $item)
                        @php
                            $item = (object) $item;
                            $judul = $activeTab == 1 ? $item->judul_artikel : $item->judul_buku;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_outcome }}</td>
                            <td>
                                {{ $judul }}
                            </td>
                            @if ($activeTab == 1)
                                <td>{{ $item->situs_publikasi_jurnal }}</td>
                            @endif
                            @if ($activeTab == 1)
                                <td>{{ $item->volume_dan_nomor_terbitan }}</td>
                            @else
                                <td>{{ $item->penerbit_buku }}</td>
                            @endif
                            @if ($activeTab == 1)
                                <td>
                                    <a href="{{ $item->url_artikel }}" target="_blank">{{ $item->url_artikel }}</a>
                                </td>
                            @else
                                <td>{{ $item->isbn }}</td>
                            @endif
                            @if ($activeTab == 2)
                                <td>{{ $item->tahun_terbit_buku }}</td>
                            @endif
                            <td>{{ Carbon\Carbon::parse($item->waktu_dibuat)->translatedFormat('d F Y') }}</td>
                            @if ($isAct && !$isRevisi)
                                <td class="cell-action cell-center">
                                    <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                        <button type="button" class="btn btn_outline btn_xs"
                                            wire:click="publikasi_editForm({{ $item->id }})">
                                            <span class="icon icon-pencil-solid"></span>
                                        </button>
                                        <button type="button" class="btn btn_outline btn_xs"
                                            wire:click="removeForm({{ $item->id }}, '{{ $judul }}', 'publikasi')">
                                            <span class="icon icon-trash-solid"></span>
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

    @if (count($dataArtikelPublikasi->items) == 0)
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">Belum ada {{ $activeTab == 1 ? 'Artikel' : 'Buku' }} yang dipublikasikan</h1>
                        <p style="text-align: start; max-width: 550px;">
                            Saat ini, belum ada data publikasi yang diunggah. Hasil publikasi yang telah dilakukan oleh peneliti akan ditampilkan di sini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} Publikasi" variant="primary"
    id="modal-publikasi" width="600px" wire:ignore.self>
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

                    <x-core::button variant="primary" class="util_ml-8" wire:click="publikasi_submitForm">
                        {{ $isEdit ? 'Ubah' : 'Tambah' }} Publikasi
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>
