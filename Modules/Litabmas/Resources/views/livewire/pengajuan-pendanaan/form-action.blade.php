<div style="display: flex; justify-content:space-between">
    <h2>{{ $title }}</h2>
    @php
        $msgType = 'helper';
        $msgInfo = '';

        if (!$isDosen && !is_null($timelineActive)) {
            if (!in_array($timelineActive->kode_agenda, [
                \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI,
                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_NOMINASI,
                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_PENDANAAN
            ])) {
                $nextTimelineAdmin = getNextTimeline($isDosen, $timelines, $timelineActive);
            }

            $tm = $isBypassDisabled ? $timelineActive : (isset($nextTimelineAdmin) ? $nextTimelineAdmin : null);

            if ($tm) {
                $msgInfo =
                    'Tahapan Kegiatan selanjutnya adalah <b>' .
                    $tm->nama_agenda .
                    '</b>, yang akan dimulai pada tanggal <b>' .
                    Carbon\Carbon::parse($tm->waktu_mulai)->translatedFormat('d F Y') .
                    '</b> sampai <b>' .
                    Carbon\Carbon::parse($tm->waktu_selesai)->translatedFormat('d F Y') .
                    '</b>.';
            }
        }

        if ($isDosen && !is_null($timelineActive) && $timelineActive->kode_agenda != \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN
            && $currentData['status_agenda_kegiatan'] === \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL1_DRAFT) {
                $msgType = 'danger';
                $msgInfo = 'Pendaftaran proposal sudah ditutup, Anda tidak dapat mengajukan proposal ini.';
            }

        function getNextTimeline($isDosen, $timelines, $timelineActive)
        {
            $isLock = true;
            foreach ($timelines as $key => $timeline) {
                if ($timeline->kode_agenda === $timelineActive->kode_agenda) {
                    $isLock = false;
                    continue;
                } else {
                    if ($isLock) {
                        continue;
                    }
                }

                if (in_array($timeline->kode_agenda, [
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI,
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_NOMINASI,
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_PENDANAAN
                ])) {
                    return $timeline;
                    break;
                }
            }
            return null;
        }
    @endphp
    <div style="display: flex; gap: 10px;">
        @if (
            !is_null($timelineActive) &&
            !$isDosen &&
                in_array($timelineActive->kode_agenda, [
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN,
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI, // has action
                ]))
            @php
                // harcode agenda wajib seleksi administrasi
                if ($timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN) {
                    $msgInfo =
                        'Anda dapat melakukan <b>Seleksi Administrasi</b> pada tanggal awal <b>' .
                        Carbon\Carbon::parse($timelines[1]->waktu_mulai)->translatedFormat('d F Y') .
                        '</b> sampai <b>' .
                        Carbon\Carbon::parse($timelines[1]->waktu_selesai)->translatedFormat('d F Y') .
                        '</b>';
                }
            @endphp
            @if ($isLolosAdministrasi)
                <button type="button" wire:click="overview_batalkanAdministrasi" class="btn btn_outline btn_xs"
                    style="">
                    <span class="btn__text">Batalkan Seleksi Administrasi</span></button>
                <button type="button" class="btn btn_primary btn_xs" style="" onclick="location.href='{{ url('litabmas/pengajuan-pendanaan') . '/' . $idProposalPendanaan . '/similarity' }}'">
                    <span class="btn__text">Penilaian
                        Similarity & AI</span></button>
            @else
                @if (is_null($isLolosAdministrasi))
                    <button type="button" @if ($isBypassDisabled) disabled @endif
                        @if ($timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN) disabled @endif wire:click="overview_tolakAdministrasi"
                        class="btn btn_destructive btn_xs" style="">
                        <span class="btn__text">Dokumen Tidak Lengkap</span></button>
                    <button type="button" @if ($isBypassDisabled) disabled @endif
                        @if ($timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN) disabled @endif wire:click="overview_terimaAdministrasi"
                        class="btn btn_primary btn_xs" style="">
                        <span class="btn__text">Tandai Dokumen Lengkap</span></button>
                @else
                    <button type="button" wire:click="overview_batalkanAdministrasi" class="btn btn_outline btn_xs"
                        style="">
                        <span class="btn__text">Batalkan Seleksi Administrasi</span></button>
                @endif
            @endif
        @endif

        @if (
            !is_null($timelineActive) &&
            !$isDosen &&
                in_array($timelineActive->kode_agenda, [
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_NOMINASI, // has action
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER,
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI,
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI,
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI,
                ]) && $isLolosAdministrasi === true)
            @if ($isLolosNominasi)
                @if ($timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_NOMINASI)
                    <button type="button" wire:click="overview_batalkanNominasi" class="btn btn_outline btn_xs"
                        style="">
                        <span class="btn__text">Batalkan Nominasi</span></button>
                @endif
                @php
                    $agendaReviewer = array_filter($timelines, function ($item) {
                        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
                    });
                @endphp
                @if (!empty($agendaReviewer))
                    <button type="button"
                        onclick="location.href='{{ url('litabmas/pengajuan-pendanaan') . '/' . $idProposalPendanaan . '/reviewer' }}'"
                        class="btn btn_primary btn_xs" style="">
                        <span class="btn__text">Tentukan Reviewer</span></button>
                @endif
            @else
                @if (is_null($isLolosNominasi))
                    <button type="button" @if ($isBypassDisabled) disabled @endif
                        @if (in_array($timelineActive->kode_agenda, [
                                \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER,
                                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI,
                                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI,
                            ])) disabled @endif class="btn btn_destructive btn_xs"
                        wire:click="overview_tolakNominasi" style="">
                        <span class="btn__text">Tolak Nominasi</span></button>
                    <button type="button" @if ($isBypassDisabled) disabled @endif
                        @if (in_array($timelineActive->kode_agenda, [
                                \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER,
                                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI,
                                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI,
                            ])) disabled @endif class="btn btn_primary btn_xs"
                        wire:click="overview_terimaNominasi" style="">
                        <span class="btn__text">Terima Nominasi</span></button>
                @else
                    <button type="button" wire:click="overview_batalkanNominasi" class="btn btn_outline btn_xs"
                        style="">
                        <span class="btn__text">Batalkan Nominasi</span></button>
                @endif
            @endif
        @endif

        @if (
            !is_null($timelineActive) &&
            !$isDosen &&
                in_array($timelineActive->kode_agenda, [
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_PENDANAAN, // has action
                    \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_PENDANAAN,
                ]) && $isLolosAdministrasi === true)
            @if ($isLolosPendanaan)
                @if ($timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_PENDANAAN)
                    <button type="button" wire:click="overview_batalkanPendanaan" class="btn btn_outline btn_xs"
                        style="">
                        <span class="btn__text">Batalkan Pendanaan</span></button>
                @endif
                @php
                    $msgInfo =
                        'Proposal ini dinyatakan lolos pendanaan, silahkan unggah SK sebagai bukti dinyatakan lolos pada menu Overview Proposal.';
                    $agendaPembimbing = array_filter($timelines, function ($item) {
                        return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENINJAUAN_LOGBOOK;
                    });
                @endphp
                @if (!empty($agendaPembimbing))
                    <button type="button"
                        onclick="location.href='{{ url('litabmas/pengajuan-pendanaan') . '/' . $idProposalPendanaan . '/pembimbing' }}'"
                        class="btn btn_primary btn_xs" style="">
                        <span class="btn__text">Tentukan Pembimbing</span></button>
                @endif
            @else
                @if (is_null($isLolosPendanaan))
                    <button type="button" @if ($isBypassDisabled) disabled @endif
                        class="btn btn_destructive btn_xs" wire:click="overview_tolakPendanaan" style="">
                        <span class="btn__text">Tolak Pendanaan</span></button>
                    <button type="button" @if ($isBypassDisabled) disabled @endif
                        class="btn btn_primary btn_xs" wire:click="overview_terimaPendanaan" style="">
                        <span class="btn__text">Terima Pendanaan</span></button>
                @else
                    <button type="button" wire:click="overview_batalkanPendanaan" class="btn btn_outline btn_xs"
                        style="">
                        <span class="btn__text">Batalkan Pendanaan</span></button>
                @endif
            @endif
        @endif

        @if (!is_null($timelineActive) && $isDosen && in_array($timelineActive->kode_agenda, [
                \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN,
                \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI,
            ]))
            @if ($currentAnggota['apakah_ketua'])
                @if ($timelineActive->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENDAFTARAN)
                    @if ($currentData['status_agenda_kegiatan'] === \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL1_DRAFT)
                        <x-core::button variant="outline" size="xs" onclick="location.href='{{ route('litabmas.pengajuan-pendanaan.edit', $currentData['id']) }}'" leading-icon="pencil-square-solid">
                            Ubah Data
                        </x-core::button>
                        {{-- disable for now --}}
                        {{-- <x-core::button variant="primary" size="xs" wire:click="overview_ajukanProposal" leading-icon="arrow-uturn-right">
                            Kirim Pengajuan
                        </x-core::button> --}}
                    @else
                        <x-core::button variant="outline" size="xs" wire:click="overview_batalkanProposal" leading-icon="arrow-uturn-left">
                            Batalkan Pengajuan
                        </x-core::button>
                    @endif
                @endif
            @else
                @if (is_null($currentAnggota['apakah_undangan_diterima']))
                    <x-core::button variant="destructive" size="xs" wire:click="overview_tolakUndanganAnggota">
                        Tolak Undangan
                    </x-core::button>
                    <x-core::button variant="primary" size="xs" wire:click="overview_terimaUndanganAnggota">
                        Terima Undangan
                    </x-core::button>
                @else
                    @if (in_array($currentData['status_agenda_kegiatan'], [
                        \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA,
                        \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN,
                        \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI,
                    ]) && $currentData['apakah_butuh_approve_semua_anggota'])
                        <x-core::button variant="outline" size="xs" wire:click="overview_batalkanUndanganAnggota">
                            Batalkan Konfirmasi
                        </x-core::button>
                    @endif
                @endif
            @endif
        @endif

        @if ($isDosen && $urlInfo['id'] == 'summary')
            <x-core::button variant="primary" size="xs" onclick="window.print()" leading-icon="printer">
                Cetak
            </x-core::button>
        @endif
    </div>
</div>
<br>

@if (!empty($msgInfo) && !isset($alert))
    <div class="alert alert_{{ $msgType }}">
        <div class="alert__content">
            <p>{!! $msgInfo !!}</p>
        </div>
    </div>
    <br>
@endif

@if (isset($alert) && !empty($alert))
    <div class="alert alert_{{ $alert['type'] }}" style="margin-bottom: 20px;">
        <div class="alert__content">
            <p>{!! $alert['message'] !!}</p>
        </div>
    </div>
@endif

<script>
    function scrollToSectionDocument() {
        const element = document.getElementById('section-dokumen-sk');
        element.scrollIntoView({
            behavior: 'smooth'
        });
    }
</script>

<x-core::modal title="Konfirmasi" variant="primary" id="modal-confirmation" width="600px" wire:ignore.self>
    <x-core::form method="POST">
        <x-core::modal.body>
            {{ $msgConfirmation ?? '' }}
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>

                    <x-core::button variant="primary" class="util_ml-8" wire:click="continueFunction">
                        Ya, Lanjutkan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>

@if (!is_null($timelineActive) &&
    !$isDosen &&
    in_array($timelineActive->kode_agenda, [
        \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENENTUAN_PENDANAAN, // has action
        \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_PENDANAAN,
    ]))
    @include('litabmas::livewire.pengajuan-pendanaan.form-action-pendanaan')
@endif
