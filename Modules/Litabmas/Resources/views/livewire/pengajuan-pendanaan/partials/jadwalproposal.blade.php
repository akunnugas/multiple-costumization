@php
    $agendaReviewer = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
    });
@endphp

@if (!empty($agendaReviewer))
    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Tambahkan Jadwal Presentasi</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan membuat jadwal presentasi apabila proposal perlu divalidasi secara langsung.</p>
            </div>
            @if ($isLolosAdministrasi && $currentData['status_similarity'] == Modules\Litabmas\Models\PengajuanPendanaan::LOLOS_SIMILARITY_AI)
                <div>
                    <a href="javascript::void(0)" wire:click="jadwalProposal_addForm" class="btn btn_primary btn_xs"
                        style="">
                        <span class="icon icon-plus"></span>
                        <span class="btn__text">Tambahkan Jadwal</span></a>
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
                            <th>Nama Kegiatan</th>
                            <th>Tanggal Dilaksanakan</th>
                            <th style="width: 35%;">Lokasi / Link Kegiatan</th>
                            @if ($isLolosAdministrasi && $currentData['status_similarity'] == Modules\Litabmas\Models\PengajuanPendanaan::LOLOS_SIMILARITY_AI)
                                <th class="cell-action cell-center" style="width: 10%;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataJadwalProposal as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_kegiatan }}</td>
                                <td>{{ Carbon\Carbon::parse($item->waktu_pelaksanaan)->translatedFormat('d F Y H:i') }} WIB
                                </td>
                                <td>
                                    @if ($item->link_presentasi_kegiatan)
                                        <a href="{{ $item->link_presentasi_kegiatan }}" target="_blank"
                                            rel="noopener noreferrer">{{ $item->link_presentasi_kegiatan }}</a>
                                    @else
                                        {{ $item->tempat_pelaksanaan }}
                                    @endif
                                </td>
                                @if ($isLolosAdministrasi && $currentData['status_similarity'] == Modules\Litabmas\Models\PengajuanPendanaan::LOLOS_SIMILARITY_AI)
                                    <td class="cell-action cell-center">
                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                            <button type="button" class="btn btn_outline btn_xs"
                                                wire:click="jadwalProposal_editForm({{ $item->id }})">
                                                <span class="icon icon-pencil-solid"></span>
                                            </button>
                                            <button type="button" class="btn btn_outline btn_xs"
                                                wire:click="removeForm({{ $item->id }}, '{{ $item->nama_kegiatan }}', 'jadwalProposal')">
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

        @if (count($dataJadwalProposal) == 0)
            <div class="empty-list">
                <div class="empty-list__wrapper">
                    <div class="empty-list__content" style="align-items:center;">
                        <div class="empty-list__inner">
                            <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                            <h1 style="text-align: start;">Belum ada Jadwal Presentasi</h1>
                            <p style="text-align: start; max-width: 550px;">
                                Anda dapat menambahkan jadwal presentasi sesuai dengan kegiatan yang akan dilakukan oleh
                                peneliti.
                            </p>
                            @if ($isLolosAdministrasi && $currentData['status_similarity'] == Modules\Litabmas\Models\PengajuanPendanaan::LOLOS_SIMILARITY_AI)
                                <a href="javascript::void(0)" wire:click="jadwalProposal_addForm"
                                    class="btn btn_outline btn_xs" style="width: fit-content; margin-top: 20px;">
                                    <span class="icon icon-plus"></span>
                                    <span class="btn__text">Tambahkan Jadwal</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} Jadwal" variant="primary" id="modal-jadwal-presentasi"
        width="600px" wire:ignore.self>
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

                        <x-core::button variant="primary" class="util_ml-8" wire:click="jadwalProposal_submitForm">
                            {{ $isEdit ? 'Ubah' : 'Tambah' }} Jadwal
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
