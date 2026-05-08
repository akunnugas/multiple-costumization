@php
    $agendaPembimbing = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENINJAUAN_LOGBOOK;
    });

    $agendaPembimbing = array_values($agendaPembimbing)[0];

    $isAct = $isLolosPendanaan && $agendaPembimbing->active;
@endphp

@if (!$isAct)
    <div class="alert alert_warning">
        <div class="alert__content">
            <p>
                Pengisian Pembimbing dapat dilakukan selama agenda <b>Pelaksanaan Penelitian/Pengabdian</b> berlangsung. yaitu pada tanggal
                    <b>{{ Carbon\Carbon::parse($agendaPembimbing->waktu_mulai)->translatedFormat('d F Y') }}</b> sampai
                    <b>{{ Carbon\Carbon::parse($agendaPembimbing->waktu_selesai)->translatedFormat('d F Y') }}</b>
            </p>
        </div>
    </div>
    <br>
@endif

<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Aktivitas Peneliti</h3>
            <p style="color: #697586; margin-top: 5px;">Silahkan berikan masukkan dari hasil aktivitas penelitian yang telah dilakukan oleh peneliti.</p>
        </div>
    </div>
    <hr>

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
                        <th>Feedback Pembimbing</th>
                        <th>Dokumen Feedback</th>
                        @if ($isAct)
                            <th class="cell-action cell-center" style="width: 5%;">Aksi</th>
                        @endif
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
                            <td>
                                {{ $item->lokasi_aktivitas_penelitian }}
                            </td>
                            <td>
                                @if ($item->id_dokumen_logbook)
                                    @php
                                        $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item->id_dokumen_logbook)->first();
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
                            <td>
                                {{ $item->feedback_logbook ?? '-' }}
                            </td>
                            <td>
                                @if ($item->id_dokumen_feedback_logbook)
                                    @php
                                        $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item->id_dokumen_feedback_logbook)->first();
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
                            @if ($isAct)
                                <td class="cell-action cell-center">
                                    <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                        <button type="button" class="btn btn_outline btn_xs"
                                            wire:click="bimbingan_addForm({{ $item->id }})">
                                            <span class="icon icon-pencil-solid"></span>
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
</div>

<x-core::modal title="Bimbingan" variant="primary" id="modal-bimbingan" width="600px"
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

                    <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit" wire:click="bimbingan_submitForm">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>
