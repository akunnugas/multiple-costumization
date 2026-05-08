@if ($timelineActive->kode_agenda != \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI && !$isBypassDisabled)
    @php
        $timelineSeleksiAdministrasi = array_filter($timelines, function ($timeline) {
            return $timeline->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI;
        });
    @endphp
    @if ($timelineSeleksiAdministrasi)
        @php
            $timelineSeleksiAdministrasi = array_values($timelineSeleksiAdministrasi)[0];
        @endphp
        <div class="alert alert_warning">
            <div class="alert__content">
                <p>
                    Pengisian Similarity dan AI dapat dilakukan selama agenda <b>Seleksi Administrasi</b> berlangsung. yaitu pada tanggal
                        <b>{{ Carbon\Carbon::parse($timelineSeleksiAdministrasi->waktu_mulai)->translatedFormat('d F Y') }}</b> sampai
                        <b>{{ Carbon\Carbon::parse($timelineSeleksiAdministrasi->waktu_selesai)->translatedFormat('d F Y') }}</b>
                </p>
            </div>
        </div>
        <br>
    @endif
@endif

<div class="alert alert_helper">
    <div class="alert__content">
        <p>
            Silakan unduh dokumen proposal dalam format PDF terlebih dahulu untuk melakukan penilaian similarity dan penilaian AI. Batas maksimal kelulusan untuk penilaian similarity adalah <b>{{ number_format($dataSumberPendanaan['maksimal_toleransi_similarity']) }}%</b>, dan untuk penilaian AI adalah <b>{{ number_format($dataSumberPendanaan['maksimal_toleransi_ai']) }}%</b>. Hanya proposal yang memenuhi kedua kriteria ini yang dapat melanjutkan ke tahap selanjutnya
        </p>
    </div>
</div>
<br>

<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Berikan Penilaian</h3>
            <p style="color: #697586; margin-top: 5px;">Silakan berikan penilaian hasil Similarity Proposal dan Artificial Inteligence yang telah Anda periksa pada platform lain</p>
        </div>

        @php
            $simpleFile = Modules\DMS\Models\Dokumen::where('id', $currentData['id_dokumen_proposal'])->first();
            $ext = $simpleFile->extension_versi_terbaru;
            $ext = ($ext == 'docx') ? 'doc' : $ext;
            $assetUrl = asset("images/$ext-solid.svg");
            $tempUrl = $simpleFile->lastVersionTemporaryUrl();
        @endphp

        <div>`
            <a href="{{ $tempUrl }}" rel="noopener" target="_blank" class="btn btn_primary btn_xs" style="">
                <span class="icon icon-arrow-down-tray"></span>
                <span class="btn__text">Unduh Proposal</span></a>
        </div>
    </div>

    <hr>

    @php
        $keys = [
            [
                'label' => 'Similarity Proposal',
                'key' => 'similarity_proposal',
                'key_document' => 'id_dokumen_penilaian_similarity',
                'value' => $currentData['penilaian_index_similarity'],
                'max' => $dataSumberPendanaan['maksimal_toleransi_similarity'],
            ],
            [
                'label' => 'Artificial Inteligence',
                'key' => 'artificial_inteligence',
                'key_document' => 'id_dokumen_penilaian_ai',
                'value' => $currentData['penilaian_index_ai'],
                'max' => $dataSumberPendanaan['maksimal_toleransi_ai'],
            ],
        ];

        $isEditSimilarity = $timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI && !$isBypassDisabled;
    @endphp

    <div class="box-table__content" id="table-docs" style="border: 0px;">
        <div class="table-max">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Komponen Penilaian</th>
                        <th style="width: 15%;">Nilai</th>
                        <th style="width: 30%;">Hasil Pengecekan</th>
                        @if ($isLolosAdministrasi && $isEditSimilarity)
                            <th class="cell-action cell-center" style="width: 5%;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($keys as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['label'] }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    {{ !empty($item['value']) ? ($item['value'] <= $item['max'] ? checkListSVG() : crossListSVG()) : '' }}{{ !empty($item['value']) ? $item['value'] : 'Belum dinilai' }}{{ $item['value'] ? '%' : '' }}
                                </div>
                            </td>
                            <td>
                                @if (!empty($currentData[$item['key_document']]))
                                    @php
                                        $simpleFile = Modules\DMS\Models\Dokumen::where('id', $currentData[$item['key_document']])->first();
                                        $ext = $simpleFile->extension_versi_terbaru;
                                        $ext = ($ext == 'docx') ? 'doc' : $ext;
                                        $assetUrl = asset("images/$ext-solid.svg");
                                        $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                                    @endphp
                                    <a href="{{ $tempUrl }}" rel="noopener" target="_blank" class="util_d-flex util_flex-center-vertical" style="gap: 10px;">
                                        <img height="20px;" src="{{ $assetUrl }}" alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                                        {{ $simpleFile['nama_dokumen'] }}
                                    </a>
                                @else
                                    <span>Belum diunggah</span>
                                @endif
                            </td>
                            @if ($isLolosAdministrasi && $isEditSimilarity)
                                <td class="cell-action cell-center">
                                    <button type="button" class="btn btn_outline btn_xs"
                                        wire:click="similarity_editForm('{{ $item['key'] }}')">
                                        <span class="icon icon-pencil-solid"></span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                        <tr style="background: #F8FAFC;">
                            <td colspan="2">
                                <b>Kesimpulan Penilaian</b>
                            </td>
                            <td colspan="3"><b style="display: flex; align-items: center; gap: 5px;">
                                @if ($currentData['status_similarity'])
                                    @if ($currentData['status_similarity'] == 'lolos')
                                        {{ checkListSVG() }} Lolos Penilaian
                                    @elseif ($currentData['status_similarity'] == 'tidak_lolos')
                                        {{ crossListSVG() }} Tidak Lolos Penilaian
                                    @endif
                                @else
                                    -
                                @endif
                            </b></td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<x-core::modal title="Berikan Penilaian: {{ $titleSimilarity }}" variant="primary" id="modal-similarity" width="600px"
    wire:ignore.self>
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

                    <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit" wire:click="similarity_submitForm">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>
