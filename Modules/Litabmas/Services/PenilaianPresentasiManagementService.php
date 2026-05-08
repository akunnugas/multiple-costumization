<?php

namespace Modules\Litabmas\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
use Modules\Litabmas\Models\PenilaianReviewerPresentasiProposal;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerAdministrasi;

class PenilaianPresentasiManagementService
{
    /**
     * @var PenilaianReviewerPresentasiProposal
     */
    protected $model = PenilaianReviewerPresentasiProposal::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanReviewerAdministrasi;
    }

    public function getAspekBobotPenilaianByIdPengajuan(int $idPengajuanPendanaan): mixed
    {
        $pengajuanPendanaan = PengajuanPendanaan::where('litabmas.pengajuan_pendanaan.id', $idPengajuanPendanaan)
            ->join('litabmas.sumber_pendanaan as sp', 'sp.id', '=', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
            ->select('sp.id_periode_pendanaan', 'litabmas.pengajuan_pendanaan.kode_jenis_pendanaan')
            ->first();

        try {
            return $this->getAspekBobotPenilaian(
                $pengajuanPendanaan->id_periode_pendanaan,
                $pengajuanPendanaan->kode_jenis_pendanaan
            );
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Ambil Aspek Penilaian.
     */
    public function getAspekBobotPenilaian($periode, $kodeJenisPendanaan): mixed
    {
        try {
            return AspekPenilaianPresentasiProposal::where('id_periode_pendanaan', $periode)
                ->where('kode_jenis_pendanaan', $kodeJenisPendanaan)
                ->orderBy('no')
                ->get();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }
}
