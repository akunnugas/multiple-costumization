<div class="card card_details-custom">
    <h3>{{ $data['pertanyaan-penelitian']['title'] }}</h3>
    <div class="grid" style="margin-top: 18px;">
        <x-litabmas::layouts.detail.line :data="$data['pertanyaan-penelitian']['items']" />
    </div>
    <br>
    <hr>
    <h3>Komponen Proposal</h3>
    <div class="grid" style="margin-top: 18px;">
        @foreach ($dataIsianProposal as $item)
            @php
                $txtData = $item->isian_proposal;
                $txtData = str_replace("\n", "<br>", $txtData);
            @endphp
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label class="row-data__name">{{ $item->nama_isian_proposal }}</label>
            </div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                <span class="row-data__value" style="width: 100%; display: inline-block; overflow-wrap: anywhere;">
                    <span class="row-data__colon">:</span>
                    {!! $txtData !!}
                </span>
            </div>
        @endforeach
        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
            <label class="row-data__name">Dokumen Proposal</label>
        </div>
        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
            <span class="row-data__value" style="width: 100%; display: inline-block; overflow-wrap: anywhere;">
                <span class="row-data__colon">:</span>
                @php
                    $simpleFile = null;
                    if (!empty($currentData['id_dokumen_proposal'])) {
                        $simpleFile = Modules\DMS\Models\Dokumen::where('id', $currentData['id_dokumen_proposal'])->first();
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
        <div class="grid" style="margin-top: 18px;">
            <x-litabmas::layouts.detail.line :data="$item" />
        </div>
        @if (!$loop->last)
            <br>
        @endif
    @endforeach

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
    <br>
    @foreach ($fieldMahasiswa as $item)
        @php
            $rowTitle = 'Data Anggota Mahasiswa ' . $noMahasiswa++;
        @endphp
        <h4>{{ $rowTitle }}</h4>
        <div class="grid" style="margin-top: 18px;">
            <x-litabmas::layouts.detail.line :data="$item" />
        </div>
        @if (!$loop->last)
            <br>
        @endif
    @endforeach

    <hr>
    <h3>{{ $data['detail-pendanaan']['title'] }}</h3>
    @php
        // convert currency
        $convert = config('money.defaults.currency');
        $v = money(
            ((float) $data['detail-pendanaan']['items'][0]['original']),
            config('money.defaults.currency'),
            true,
        );
        $data['detail-pendanaan']['items'][0]['text'] = $v->format();
    @endphp
    <div class="grid" style="margin-top: 18px;">
        <x-litabmas::layouts.detail.line :data="$data['detail-pendanaan']['items']" />
    </div>
    <br>

    @if (!$isDosen)
        <br>
        <hr>
        <h3>Penilaian Administrasi</h3>
        <div class="grid" style="margin-top: 18px;">
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label class="row-data__name">Nilai Similarity</label>
            </div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                    <span class="row-data__colon">:</span>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        {{ !empty($currentData['penilaian_index_similarity']) ? ($currentData['penilaian_index_similarity'] <= $dataSumberPendanaan['maksimal_toleransi_similarity'] ? checkListSVG() : crossListSVG()) : '' }}{{ !empty($currentData['penilaian_index_similarity']) ? $currentData['penilaian_index_similarity'] : 'Belum dinilai' }}{{ $currentData['penilaian_index_similarity'] ? '%' : '' }}
                    </div>
                </span>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label class="row-data__name">Nilai AI</label>
            </div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                    <span class="row-data__colon">:</span>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        {{ !empty($currentData['penilaian_index_ai']) ? ($currentData['penilaian_index_ai'] <= $dataSumberPendanaan['maksimal_toleransi_ai'] ? checkListSVG() : crossListSVG()) : '' }}{{ !empty($currentData['penilaian_index_ai']) ? $currentData['penilaian_index_ai'] : 'Belum dinilai' }}{{ $currentData['penilaian_index_ai'] ? '%' : '' }}
                    </div>
                </span>
            </div>
        </div>

        <br>
        <hr>
        @php
            $txtRekomendasi = '';
            foreach ($dataReviewerProposalOnly as $item) {
                $txtRekomendasi .= 'Reviewer ' . $item->reviewer_ke . ' - ' . $item->nama_reviewer . ' ';
                $txtRekomendasi .= !empty($item->rekomendasi_anggaran)
                    ? ('('.money($item->rekomendasi_anggaran . '00')->format().')')
                    : '(Tidak Ada)';
                $txtRekomendasi .= '<br>';
            }
            $fieldScoreReviewer = [
                [
                    'field' => 'nilai_average',
                    'label' => 'Nilai Rata-rata proposal',
                    'text' => $finalScoreKomposisi
                        ? number_format($finalScoreKomposisi, 2) .
                            ' <span style="color: ' .
                            ($finalScoreKomposisi >= 300 ? 'green' : 'red') .
                            '">(' .
                            ($finalScoreKomposisi >= 300 ? 'Lolos' : 'Tidak Lolos') .
                            ')</span>'
                        : 'Belum dinilai',
                ],
                [
                    'field' => 'nilai_average_presentasi',
                    'label' => 'Nilai Rata-rata presentasi proposal',
                    'text' => $finalScorePresentasi
                        ? number_format($finalScorePresentasi, 2) .
                            ' <span style="color: ' .
                            ($finalScorePresentasi >= 300 ? 'green' : 'red') .
                            '">(' .
                            ($finalScorePresentasi >= 300 ? 'Lolos' : 'Tidak Lolos') .
                            ')</span>'
                        : 'Belum dinilai',
                ],
                [
                    'field' => 'rekomendasi_anggaran_reviewer',
                    'label' => 'Rekomendasi Anggaran Reviewer',
                    'text' => !empty($txtRekomendasi) ? $txtRekomendasi : 'Belum dinilai',
                ],
            ];
        @endphp
        <h3>Penilaian Reviewer</h3>
        <div class="grid" style="margin-top: 18px;">
            <x-litabmas::layouts.detail.line :data="$fieldScoreReviewer" />
        </div>
    @endif
</div>

@if (!$isDosen)
    <br>
    <div class="card card_details-custom" id="section-dokumen-sk">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Dokumen Surat Keterangan</h3>
                <p style="color: #697586; margin-top: 5px;">Silahkan Unggah berkas dokumen SK jika telah menyatakan
                    lolos
                    pendanaan</p>
            </div>
            <div>
                <button @if (!$isLolosPendanaan) disabled @endif wire:click="overview_addDokumenSK"
                    class="btn btn_primary btn_xs" style="">
                    <span class="icon icon-plus"></span>
                    <span class="btn__text">Unggah SK</span></button>
            </div>
        </div>
        <hr>
        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Dokumen</th>
                            <th style="width: 20%">Terakhir Diubah</th>
                            <th class="cell-action cell-center" style="width: 5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($currentData['id_dokumen_sk_peneliti']))
                            @php
                                $simpleFile = Modules\DMS\Models\Dokumen::where(
                                    'id',
                                    $currentData['id_dokumen_sk_peneliti'],
                                )->first();
                            @endphp
                            <tr>
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
                                <td>{{ Carbon\Carbon::parse($simpleFile['waktu_diubah'])->diffForHumans() }}</td>
                                <td class="cell-action cell-center">
                                    <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                        <button type="button" class="btn btn_outline btn_xs"
                                            wire:click="removeForm(null, '{{ $simpleFile['nama_dokumen'] }}', 'overview')">
                                            <span class="icon icon-trash-solid"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="3" style="text-align: center;">
                                    Tidak ada dokumen
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-core::modal title="Unggah Dokumen SK" variant="primary" id="modal-dokumen-sk" width="600px"
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

                        <x-core::button variant="primary" class="util_ml-8" :disabled="$disableSubmit" wire:click="overview_submitDokumenSK">
                            Unggah Dokumen
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
@endif
