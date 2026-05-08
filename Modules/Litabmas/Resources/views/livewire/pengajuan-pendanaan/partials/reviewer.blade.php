@php
    $agendaReviewer = array_filter($timelines, function ($item) {
        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
    });

    // Cek apakah similarity lolos - bisa dari field status_similarity atau dari status agenda kegiatan
    // Jika status_similarity NULL tapi sudah lolos administrasi atau lebih tinggi, berarti similarity sudah lolos
    $isSimilarityLolos = $currentData['status_similarity'] == Modules\Litabmas\Models\PengajuanPendanaan::LOLOS_SIMILARITY_AI ||
        ($currentData['status_similarity'] === null && in_array($currentData['status_agenda_kegiatan'], [
            Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI,
            Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL7_PENINJAUAN_PROPOSAL,
            Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI,
            Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN,
            Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL,
        ]));

    $canAddReviewer = $isLolosAdministrasi && $isSimilarityLolos;
@endphp
@if (!empty($agendaReviewer))
    <div class="alert alert_helper">
        <div class="alert__content">
            <p>
                Anda dapat menambahkan reviewer setelah proposal ditanyakan <b>Lolos Administrasi</b>.
            </p>
        </div>
    </div>
    <br>

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Tambahkan Reviewer</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan tentukan hingga 3 reviewer per tugas untuk memberikan
                    penilaian proposal</p>
            </div>
            @if ($canAddReviewer)
                <div>
                    <a href="javascript::void(0)" wire:click="reviewer_addForm" class="btn btn_primary btn_xs" style="">
                        <span class="icon icon-plus"></span>
                        <span class="btn__text">Tambahkan Reviewer</span></a>
                </div>
            @endif
        </div>
        <hr>

        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>Reviewer ke</th>
                            <th>Nama Reviewer</th>
                            <th>Bertugas sebagai</th>
                            <th style="width: 30%;">Dokumen SK Reviewer</th>
                            @if ($canAddReviewer)
                                <th class="cell-action cell-center" style="width: 10%;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataReviewer as $item)
                            @php
                                $bertugasSebagai = [
                                    Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_PROPOSAL =>
                                        $item->apakah_review_proposal,
                                    Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_LUARAN =>
                                        $item->apakah_review_luaran,
                                    Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_ANTARA =>
                                        $item->apakah_review_antara,
                                ];
                                $bertugasSebagai = array_filter($bertugasSebagai);
                                foreach ($bertugasSebagai as $key => $value) {
                                    $bertugasSebagai[$key] =
                                        Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_OPTIONS[
                                            $key
                                        ];
                                }
                                $bertugasSebagai = array_values($bertugasSebagai);

                                $simpleFile = null;
                                if (!empty($item->id_dokumen_sk)) {
                                    $simpleFile = Modules\DMS\Models\Dokumen::where('id', $item->id_dokumen_sk)->first();
                                }
                            @endphp
                            <tr>
                                <td>{{ $item->reviewer_ke }}</td>
                                <td>{{ $item->nama_reviewer }}</td>
                                <td>{{ implode(', ', $bertugasSebagai) }}</td>
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
                                @if ($canAddReviewer)
                                    <td class="cell-action cell-center">
                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                            <button type="button" class="btn btn_outline btn_xs"
                                                wire:click="reviewer_editForm({{ $item->id }})">
                                                <span class="icon icon-pencil-solid"></span>
                                            </button>
                                            <button type="button" class="btn btn_outline btn_xs"
                                                wire:click="removeForm({{ $item->id }}, '{{ $item->nama_reviewer }}', 'reviewer')">
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

            @if (count($dataReviewer) == 0)
                <div class="empty-list">
                    <div class="empty-list__wrapper">
                        <div class="empty-list__content" style="align-items:center;">
                            <div class="empty-list__inner">
                                <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                                <h1 style="text-align: start;">Belum ada Reviewer yang ditugaskan</h1>
                                <p style="text-align: start; max-width: 550px;">Proposal ini telah disetujui pendanaannya,
                                    sehingga Anda dapat menugaskan reviewer
                                    untuk memberikan penilaian dalam kegiatan yang akan dilakukan oleh peneliti.</p>
                                @if ($canAddReviewer)
                                    <a href="javascript::void(0)" wire:click="reviewer_addForm" class="btn btn_outline btn_xs"
                                        style="width: fit-content; margin-top: 20px;">
                                        <span class="icon icon-plus"></span>
                                        <span class="btn__text">Tambahkan Reviewer</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} Reviewer" variant="primary" id="modal-reviewer" width="600px"
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

                        <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit"
                            wire:click="reviewer_submitForm">
                            {{ $isEdit ? 'Ubah' : 'Tambah' }} Reviewer
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
                            Tidak ada Tahapan Kegiatan Peninjauan Proposal
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Klaster pada proposal ini tidak menggunakan agenda Peninjauan Proposal, Anda bisa mengabaikan
                            halaman ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif