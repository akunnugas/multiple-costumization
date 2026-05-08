<?php

namespace Modules\Litabmas\Services;

use Illuminate\Support\Facades\DB;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;

class CronStatusAgendaKegiatanService
{
    /**
     * @var PengajuanPendanaan
     */
    protected $model = PengajuanPendanaan::class;

    /**
     * @var PengajuanPendanaanService
     */
    protected $service = PengajuanPendanaanService::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaan;

        $this->service = new PengajuanPendanaanService;
    }

    public function updateStatusMasukSeleksiAdministrasi()
    {
        $listProposal = $this->model
            ->whereIn('status_agenda_kegiatan', [
                PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA,
                PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN
            ])
            ->get();

        foreach ($listProposal as $proposal) {
            $agendaSeleksi = $this->service->getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan($proposal->id, AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI);
            $agendaSeleksi = array_shift($agendaSeleksi);

            // Jika agenda kegiatan seleksi administrasi aktif, maka update status proposal
            if ($agendaSeleksi->active && $proposal->status_agenda_kegiatan !== PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI) {
                $proposal->status_agenda_kegiatan = PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI;
                $proposal->save();
            }
        }
    }

    public function updateStatusProposalToNext()
    {
        $listProposal = $this->model
            ->whereNotIn('status_agenda_kegiatan', [
                PengajuanPendanaanStatus2::LEVEL1_DRAFT,
                PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA,
                PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN,
                PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL
            ])
            ->get();

        foreach ($listProposal as $proposal) {
            $listAgendaKegiatan = $this->service->getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan($proposal->id);
            $agendaAktif = array_filter($listAgendaKegiatan, function ($item) {
                return $item->active;
            });

            // Ambil agenda kegiatan yang aktif
            $agendaAktif = array_shift($agendaAktif);

            if (!empty($agendaAktif)) {
                $agendaKegiatan = AgendaKegiatan::find($agendaAktif->id);
                $listStepStatus = $agendaKegiatan->getListStepAgenda();

                // Abaikan jika tidak ada status yang perlu dirubah
                if (empty($listStepStatus)) {
                    continue;
                }

                if (!isset($listStepStatus[$proposal->status_agenda_kegiatan])) {
                    $firstStep = array_key_first($listStepStatus);

                    // Abaikan jika status proposal sudah di step pertama
                    if ($proposal->status_agenda_kegiatan === $firstStep) {
                        continue;
                    }

                    // Abaikan jika tidak lolos administrasi atau pendanaan
                    if (in_array($proposal->status_agenda_kegiatan, [PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI, PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN])) {
                        continue;
                    }

                    $proposal->status_agenda_kegiatan = $firstStep;
                    $proposal->save();

                    // Catat status proposal
                    $check = PengajuanPendanaanStatus2::where('id_pengajuan_pendanaan', $proposal->id)
                        ->where('id_agenda_kegiatan', $agendaAktif->id)
                        ->first();

                    if (empty($check)) {
                        PengajuanPendanaanStatus2::create([
                            'id_pengajuan_pendanaan' => $proposal->id,
                            'id_agenda_kegiatan' => $agendaAktif->id,
                            'status' => $firstStep
                        ]);
                    }
                }
            }
        }
    }
}
