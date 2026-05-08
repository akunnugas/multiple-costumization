<?php

namespace Modules\Litabmas\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanIsianProposal;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerAdministrasi;
use Modules\Litabmas\Models\PenilaianReviewerIsianProposal;

class PenilaianIsianProposalService
{
    /**
     * @var PengajuanPendanaanReviewerAdministrasi
     */
    protected $model = PengajuanPendanaanReviewerAdministrasi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanReviewerAdministrasi;
    }

    /**
     * Get aspek penilaian komposisi proposal.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed|Error
     */
    public function getAspekBobotPenilaian(int $idPengajuanPendanaan): mixed
    {
        $pengajuanPendanaan = PengajuanPendanaan::where('litabmas.pengajuan_pendanaan.id', $idPengajuanPendanaan)
            ->join('litabmas.sumber_pendanaan as sp', 'sp.id', '=', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
            ->select('sp.id_periode_pendanaan', 'litabmas.pengajuan_pendanaan.kode_jenis_pendanaan')
            ->first();

        try {
            return (new AspekPenilaianKomposisiProposalService())->getByJenisPendanaanDanPeriodePendanaan(
                $pengajuanPendanaan->kode_jenis_pendanaan,
                $pengajuanPendanaan->id_periode_pendanaan
            );
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function getDataIsianPenilaianProposal($id)
    {
        return PengajuanPendanaanIsianProposal::select('litabmas.pengajuan_pendanaan_isian_proposal.id', 'a.nama_isian_proposal as nama', 'litabmas.pengajuan_pendanaan_isian_proposal.isian_proposal as deskripsi')
            ->where('id_pengajuan_pendanaan', $id)
            ->join('litabmas.aspek_penilaian_isian_proposal as a', 'a.id', '=', 'litabmas.pengajuan_pendanaan_isian_proposal.id_aspek_penilaian_isian_proposal')
            ->orderBy('a.urutan_isian_proposal', 'ASC')
            ->get();
    }

    public function getDataPenilaianReviwerIsianProposal($id, $dataIsianPenilaianProposal, $idReviewer = null)
    {
        $dataFeedbackPenilaianProposal = PenilaianReviewerIsianProposal::select('litabmas.penilaian_reviewer_isian_proposal.*')
            ->join('litabmas.pengajuan_pendanaan_reviewers as b', 'b.id', '=', 'litabmas.penilaian_reviewer_isian_proposal.id_pengajuan_pendanaan_reviewer')
            ->join('litabmas.pengajuan_pendanaan_isian_proposal as a', 'a.id', '=', 'litabmas.penilaian_reviewer_isian_proposal.id_pengajuan_pendanaan_isian_proposal')
            ->where('a.id_pengajuan_pendanaan', $id)
            ->when($idReviewer, function ($query) use ($idReviewer) {
                return $query->where('litabmas.penilaian_reviewer_isian_proposal.id_pengajuan_pendanaan_reviewer', $idReviewer);
            })
            ->orderBy('b.reviewer_ke')
            ->get();

        $temp = [];
        foreach ($dataIsianPenilaianProposal as $key => $value) {
            foreach ($dataFeedbackPenilaianProposal as $item) {
                $item = (object) $item; // remove object
                if ($item->id_pengajuan_pendanaan_isian_proposal == $value->id) {
                    $temp[$value->id][] = $item;
                }
            }
        }
        $dataFeedbackPenilaianProposal = $temp;

        return $dataFeedbackPenilaianProposal;
    }
}
