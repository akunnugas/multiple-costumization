@php use Illuminate\Support\Carbon; @endphp
@props([
    'data',
    'isPeneliti' => false,
])

@pushonce('head')
    <style>
        .form-control .form-control__label {
            color: #364152 !important;
        }
        .form-control__text {
            color: #697586;
        }
        hr.slash {
            border-top: 1px #E3E8EF;
        }
    </style>
@endpushonce

@foreach($data as $isianProposal)
    <div class="card" style="margin: 0 1rem 0 1rem; padding: 1rem; width: auto;">
        <div class="card__body">
            <div class="form-control">
                <label class="form-control__label">
                    <b>{{ $isianProposal->nama_isian_proposal }}:</b>
                </label>
                <span class="ql-snow">
                    <span class="ql-editor" style="display: flex; flex-direction: column; padding: 0;">
                        {!! $isianProposal->isian_proposal !!}
                    </span>
                </span>
            </div>
            {{-- @if(!$isianProposal->feedback_isian_proposal->isEmpty())
                <hr class="slash util_mt-8" />
                @foreach($isianProposal->feedback_isian_proposal as $feedbackIsianProposal)
                    @php
                        $namaReviewer = $feedbackIsianProposal->nama_reviewer;
                        $reviewerKe = $feedbackIsianProposal->reviewer_ke;
                        $isReviewer = $feedbackIsianProposal->is_reviewer;
                        if ($isPeneliti) {
                            $labelNamaReviewer = 'Reviewer ' . $reviewerKe;
                        } else {
                            $labelNamaReviewer = 'Reviewer ' . $reviewerKe . ' (' . ($isReviewer ? 'Anda' : $namaReviewer) . ')';
                        }

                        $waktuMereview = Carbon::parse($feedbackIsianProposal->waktu_diubah)->isoFormat('D MMM YYYY');
                        $feedback = nl2br(e($feedbackIsianProposal->feedback_isian_proposal));
                    @endphp
                    <div class="util_pl-8 util_pr-8">
                        <div class="util_d-flex util_flex-center-vertical util_gap-8px util_pt-12 util_pb-8">
                            <span class="avatar avatar_xs" data-avatar-name="{{ $namaReviewer }}"></span>
                            <b>{{ $labelNamaReviewer }}</b>
                            <img src="{{ asset('images/ellipse-dot.png') }}" width="4" alt="separator">
                            <span class="form-control__text">
                                {{ $waktuMereview }}
                            </span>
                        </div>
                        <span class="grid cols-1 cols-sm-1 cols-md-2">
                            {!! $feedback !!}
                        </span>
                    </div>
                @endforeach
            @endif --}}
        </div>
    </div>
@endforeach
