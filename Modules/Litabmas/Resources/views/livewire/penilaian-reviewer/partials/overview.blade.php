@php
    $apakahLaporanProposal = $currentReviewer->apakah_review_proposal;
@endphp

@if ($apakahLaporanProposal)
    @php
        $msgType = 'helper';
        $msgInfo = '';
    @endphp

    @if (!$isCanFeedback)
        @php
            $msgInfo =
                'Anda dapat memberikan feedback pada tanggal awal <b>' .
                Carbon\Carbon::parse($timelineFeedback->waktu_mulai)->translatedFormat('d F Y') .
                '</b> sampai <b>' .
                Carbon\Carbon::parse($timelineFeedback->waktu_selesai)->translatedFormat('d F Y') .
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

    <div class="card card_details-custom">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <h3>Feedback Reviewer</h3>
                <p style="color: #697586; margin-top: 5px;">Silakan berikan feedback berupa komentar untuk proposal
                    dibawah ini</p>
            </div>
            @if ($isCanFeedback)
                @if (!$isEdit)
                    <div>
                        <a href="javascript::void(0)" wire:click="overview_editFeedback" class="btn btn_primary btn_xs"
                            style="">
                            <span class="icon icon-pencil"></span>
                            <span class="btn__text">Berikan Feedback</span></a>
                    </div>
                @else
                    <div style="display: flex; gap: 8px;">
                        <a href="javascript::void(0)" wire:click="overview_cancelFeedback"
                            class="btn btn_outline btn_xs" style="">
                            <span class="btn__text">Batal</span></a>
                        <a href="javascript::void(0)" wire:click="overview_saveFeedback" class="btn btn_primary btn_xs"
                            style="">
                            <span class="btn__text">Simpan</span></a>
                    </div>
                @endif
            @endif
        </div>
        <hr>

        @foreach ($dataIsianPenilaianProposal as $item)
            <br>
            <div class="card card_details-custom color-auto" style="box-shadow: rgba(149, 157, 165, 0.2) 0px 2px 2px;">
                <h3 style="color: #222222">{{ $loop->iteration . '. ' . $item->nama }}</h3>
                <p style="margin-top: 5px;">{!! $item->deskripsi !!}</p>
                <hr>

                @if (!$isEdit)
                    @if (!isset($dataFeedbackPenilaianProposal[$item->id]))
                        <span>Belum ada feedback dari reviewer</span>
                    @else
                        @foreach ($dataFeedbackPenilaianProposal[$item->id] as $feedback)
                            @php
                                $biodata = $feedback->pengajuan_pendanaan_reviewer->biodata;
                                $textFeedback = $feedback->feedback_isian_proposal;
                                $textFeedback = str_replace("\n", "<br>", $textFeedback);
                            @endphp
                            <div class="color-auto">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="avatar__circle"
                                        style="min-width: 35px; width: 35px; min-height: 35px; height: 35px;">
                                        <p class="avatar__acronym">
                                            {{ substr($biodata->nama, 0, 2) }}
                                        </p>
                                    </div>
                                    <h4 style="color: #222222">{{ $biodata->nama }} (Anda)</h4>
                                    &bull; {{ $feedback->waktu_diubah->translatedFormat('d F Y') }}
                                </div>
                                <p style="margin-top: 10px; max-width: 450px;">{!! $textFeedback !!}</p>
                            </div>
                            <br>
                        @endforeach
                    @endif
                @else
                    @php
                        $feedback = $dataFeedbackPenilaianProposal[$item->id][0] ?? null;
                    @endphp
                    <div class="form-control__group {{ isset($fieldErrors[$item->id]) ? 'error' : '' }}">
                        <textarea wire:change="overview_setFeedback('{{ $item->id }}', $event.target.value)"
                            class="form-control__input textarea" style="margin-top: 10px; height: 100px;">{{ $feedback?->feedback_isian_proposal }}</textarea>
                    </div>
                    @if (isset($fieldErrors[$item->id]))
                        <div class="form-control__message" style="margin-top: 10px; color:red;">
                            {{ $fieldErrors[$item->id] }}</div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    <br>
@else
    <div class="card card_details-custom">
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">
                            Tidak bertugas sebagai Reviewer Proposal
                        </h1>
                        <p style="text-align: start; max-width: 550px;">
                            Anda tidak ditunjuk sebagai <b>Reviewer Proposal</b> pada proposal ini, Anda bisa mengabaikan halaman ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
