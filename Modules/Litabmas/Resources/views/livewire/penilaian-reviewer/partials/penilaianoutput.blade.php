@php
    $apakahLaporanLuaran = $currentReviewer->apakah_review_luaran;
@endphp

@if ($apakahLaporanLuaran)
    @php
        $msgType = 'helper';
        $msgInfo = '';
    @endphp

    @if (!$isCanLuaran)
        @php
            $msgInfo =
                'Anda dapat memberikan penilaian pada tanggal awal <b>' .
                Carbon\Carbon::parse($timelineLuaran->waktu_mulai)->translatedFormat('d F Y') .
                '</b> sampai <b>' .
                Carbon\Carbon::parse($timelineLuaran->waktu_selesai)->translatedFormat('d F Y') .
                '</b>.';
        @endphp
    @endif

    @if (!empty($msgInfo) && !isset($alert))
        <div class="alert alert_{{ $msgType }}">
            <div class="alert__content">
                <p>{!! $msgInfo !!}</p>
            </div>
        </div>
        <br>
    @endif

    @include('litabmas::livewire.penilaian-reviewer.partials.section_jadwal')

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Berikan Penilaian Laporan Luaran</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan berikan penilaian berdasarkan dokumen & hasil presentasi </p>
            </div>
            @if ($isCanLuaran)
                @if (!$isEdit)
                    <div>
                        <a href="javascript::void(0)" wire:click="penilaianoutput_editLuaran"
                            class="btn btn_primary btn_xs" style="">
                            <span class="icon icon-pencil"></span>
                            <span class="btn__text">Beri Penilaian</span></a>
                    </div>
                @else
                    <div style="display: flex; gap: 8px;">
                        <a href="javascript::void(0)" wire:click="penilaianoutput_cancelLuaran"
                            class="btn btn_outline btn_xs" style="">
                            <span class="btn__text">Batal</span></a>
                        <a href="javascript::void(0)" wire:click="penilaianoutput_saveLuaran"
                            class="btn btn_primary btn_xs" style="">
                            <span class="btn__text">Simpan</span></a>
                    </div>
                @endif
            @endif
        </div>
        <hr>

        <div class="box-table__content" id="table-docs" style="border: 0px;">
            <div class="table-max">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Laporan</th>
                            <th>File Laporan</th>
                            <th style="width: 30%;">Feedback Reviewer</th>
                            <th style="width: 25%;">Status Luaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $optionStatus = Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_OPTIONS;
                            unset($optionStatus[Modules\Litabmas\Models\PenilaianReviewerOutput::STATUS_BELUM_DINILAI]);
                        @endphp
                        @foreach ($dataOutputLuaran as $item)
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
                                <td>
                                    <div class="form-control__group {{ isset($fieldErrors['feedback_'.$item->id]) ? 'error' : '' }}">
                                        @if (!$isEdit)
                                            {{ $dataReviewerOutputLuaran[$item->id][0]->feedback_output ?? 'Tidak ada Feedback' }}
                                        @else
                                            <textarea wire:change="penilaianoutput_setFeedback('{{ $item->id }}', $event.target.value)"
                                            class="form-control__input textarea">{{ $records['feedback'][$item->id] ?? null }}</textarea>
                                    @endif
                                </div>
                                </td>
                                <td>
                                    @if (!$isEdit)
                                        @php
                                            $status = $dataReviewerOutputLuaran[$item->id][0]->status_penilaian_output ?? 'Belum Dinilai';
                                            $colorVariant = $status === Modules\Litabmas\Models\PengajuanPendanaanLaporanOutput::STATUS_DISETUJUI ? 'success' : 'warning';
                                        @endphp
                                        <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
                                            {{ ucwords($status) }}
                                        </x-core::badge>
                                    @else
                                        <div class="form-control__group {{ isset($fieldErrors['status_'.$item->id]) ? 'error' : '' }}">
                                            <x-core::controls.select label="Status" purpose="form"
                                                :selected="$records['status'][$item->id] ?? null"
                                                wire:change="penilaianoutput_setStatusPenilaian('{{ $item->id }}', $event.target.value)"
                                                :options="$optionStatus" />
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <br>
        <h4>Komentar umum</h4>
        <p style="margin-top: 10px; @if(!$records['komentar_umum']) color: #697586; @endif">
            @if (!$isEdit)
                {{ $records['komentar_umum'] ?? 'Anda belum memberikan komentar umum' }}
            @else
                <div class="form-control__group">
                    <textarea wire:change="penilaianoutput_setKomentarUmum($event.target.value)"
                        class="form-control__input textarea">{{ $records['komentar_umum'] ?? null }}</textarea>
                </div>
            @endif
        </p>
    </div>

    <br>

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <h3>Berikan Penilaian Luaran (Bersama)</h3>
            @if ($isCanLuaran)
                <div>
                    <a href="javascript::void(0)" wire:click="penilaianoutput_editLuaranBersama"
                        class="btn btn_primary btn_xs" style="">
                        <span class="icon icon-pencil"></span>
                        <span class="btn__text">Beri Penilaian</span></a>
                </div>
            @endif
        </div>
        <hr>

        @if ($isHasOutputBersama && $isCanLuaran)
            <div class="alert alert_helper">
                <div class="alert__content">
                    <p>Penilaian output bersama sudah dinilai sebelumnya, Anda dapat meninjau kembali</p>
                </div>
            </div>
            <br>
        @endif

        <div style="display: flex; gap: 10px; align-items: center;">
            <h3 style="color: #636366;">Daftar Nama Reviewer:</h3>

            @foreach ($dataReviewerLuaranOnly as $item)
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar__circle" style="min-width: 35px; width: 35px; min-height: 35px; height: 35px;">
                        <p class="avatar__acronym">
                            {{ substr($item->nama_reviewer, 0, 2) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <x-core::modal title="Penilaian Output Bersama" variant="primary" id="modal-output-bersama"
        width="600px" wire:ignore.self>
        <x-core::form method="POST">
            <x-core::modal.body>
                @foreach ($fields as $item)
                    @php
                        $item['name'] ??= $item['field'];
                        unset($item['field']);

                        $item['label'] = $loop->iteration . '. ' . $item['label'];

                        $attributes = Page::buildAttributes($item);
                    @endphp
                    <x-core::controls.form {{ $attributes }} />
                @endforeach

                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batalkan
                        </x-core::button>

                        <x-core::button variant="primary" class="util_ml-8" wire:click="penilaianoutput_saveLuaranBersama">
                            Simpan Penilaian
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
                            Tidak bertugas sebagai Reviewer Luaran
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Anda tidak ditunjuk sebagai <b>Reviewer Luaran</b> pada proposal ini, Anda bisa mengabaikan halaman ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
