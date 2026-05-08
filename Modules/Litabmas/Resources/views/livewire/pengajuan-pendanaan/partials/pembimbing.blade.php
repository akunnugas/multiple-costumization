@php
    $agendaPembimbing = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENINJAUAN_LOGBOOK;
    });
@endphp

@if (!empty($agendaPembimbing))

    <div class="alert alert_helper">
        <div class="alert__content">
            <p>Setelah proposal dinyatakan lolos pendanaan, Anda dapat menambahkan pembimbing.</p>
        </div>
    </div>
    <br>

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Tambahkan Pembimbing</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan tambahkan pembimbing untuk membantu peninjauan aktivitas penelitian atau pengabdian</p>
            </div>
            @if ($isLolosPendanaan)
                <div>
                    <a href="javascript::void(0)" wire:click="pembimbing_addForm" class="btn btn_primary btn_xs"
                        style="">
                        <span class="icon icon-plus"></span>
                        <span class="btn__text">Tambahkan Pembimbing</span></a>
                </div>
            @endif
        </div>
        <hr>

        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pembimbing</th>
                            <th style="width: 30%;">Dokumen SK</th>
                            @if ($isLolosPendanaan)
                                <th class="cell-action cell-center" style="width: 10%;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataPembimbing as $item)
                            @php
                                $simpleFile = null;
                                if (!empty($item->id_dokumen_sk)) {
                                    $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item->id_dokumen_sk)->first();
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nip_nama }}</td>
                                @if ($simpleFile)
                                    @php
                                        $ext = $simpleFile->extension_versi_terbaru;
                                        $ext = $ext == 'docx' ? 'doc' : $ext;
                                        $assetUrl = asset("images/$ext-solid.svg");
                                        $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                                    @endphp
                                    <td><a href="{{ $tempUrl }}" rel="noopener" target="_blank"
                                            class="util_d-flex util_flex-center-vertical" style="gap: 10px;">
                                            <img height="20px;" src="{{ $assetUrl }}"
                                                alt="Dokumen {{ $simpleFile['nama_dokumen'] }}">
                                            {{ $simpleFile['nama_dokumen'] }}
                                        </a>
                                    </td>
                                @else
                                    <td>
                                        Belum diunggah
                                    </td>
                                @endif
                                @if ($isLolosPendanaan)
                                    <td class="cell-action cell-center">
                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                            <button type="button" class="btn btn_outline btn_xs"
                                                wire:click="pembimbing_editForm({{ $item->id }})">
                                                <span class="icon icon-pencil-solid"></span>
                                            </button>
                                            <button type="button" class="btn btn_outline btn_xs"
                                                wire:click="removeForm({{ $item->id }}, '{{ $item->nip_nama }}', 'pembimbing')">
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

        @if (count($dataPembimbing) == 0)
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">Belum ada Pembimbing yang ditugaskan</h1>
                            <p style="text-align: start; max-width: 550px;">
                                Proposal ini telah disetujui pendanaannya, sehingga Anda dapat menugaskan pembimbing untuk membantu dalam kegiatan yang akan dilakukan oleh peneliti.
                            </p>
                            @if ($isLolosPendanaan)
                                <a href="javascript::void(0)" wire:click="pembimbing_addForm"
                                    class="btn btn_outline btn_xs" style="width: fit-content; margin-top: 20px;">
                                    <span class="icon icon-plus"></span>
                                    <span class="btn__text">Tambahkan Pembimbing</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <br>

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Aktivitas Peneliti</h3>
                <p style="color: #697586; margin-top: 5px;">Catatan tertulis yang untuk mendokumentasikan setiap tahap dan aktivitas dalam proses penelitian maupun pengabdian.</p>
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
                            <th class="cell-center">Feedback Pembimbing @if(!empty($currentData['nama_pembimbing'])) <br> {{ $currentData['nama_pembimbing'] }} @endif</th>
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
                                <td>{{ Carbon\Carbon::parse($item->tanggal_aktivitas_penelitian)->translatedFormat('d F Y') }}</td>
                                <td>{{ $item->nama_aktivitas_penelitian }}</td>
                                <td>{{ $item->lokasi_aktivitas_penelitian }}</td>
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
                                <td>{{ $item->feedback_logbook ?? '-' }}</td>
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
    </div>

    <x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} Pembimbing" variant="primary" id="modal-pembimbing" width="600px"
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

                        <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit" wire:click="pembimbing_submitForm">
                            {{ $isEdit ? 'Ubah' : 'Tambah' }} Pembimbing
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
                            Tidak ada Tahapan Kegiatan Pelaksanaan Penelitian/Pengabdian
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Klaster pada proposal ini tidak menggunakan agenda Pelaksanaan Penelitian/Pengabdian, Anda bisa mengabaikan halaman ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
