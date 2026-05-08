<?php

namespace Modules\Litabmas\Services;

use Modules\Core\Helpers\Cstr;
use Modules\DMS\Models\Dokumen;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;
use Modules\Litabmas\Models\PeriodePendanaan;

class PengajuanPendanaanReviewerKegiatanService
{
    /**
     * @var PeriodePendanaan
     */
    protected $model = PengajuanPendanaanReviewerKegiatan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanReviewerKegiatan;
    }

    /**
     * Cek apakah user merupakan reviewer dari pengajuan pendanaan tsb.
     *
     * @param int $idPengajuanPendanaan
     * @param int|null $idBiodata
     * @return bool
     */
    public function isReviewer(int $idPengajuanPendanaan, int $idBiodata = null): bool
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->where('id_biodata', $idBiodata)
            ->exists();
    }

    /**
     * Get id pengajuan pendanaan reviewer by id pengajuan pendanaan and id biodata.
     *
     * @param int $idPengajuanPendanaan
     * @param int|null $idBiodata
     * @param array|null $tipeReviewer
     * @return mixed
     */
    public function getIdPengajuanPendanaanReviewer(int $idPengajuanPendanaan, int $idBiodata = null, array $tipeReviewer = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->when($tipeReviewer, function ($query, $tipeReviewer) {
                return $query->whereIn('tipe_reviewer', $tipeReviewer);
            })
            ->where('id_biodata', $idBiodata)
            ->select('id')
            ->first()?->id;
    }

    /**
     * Get detail reviewer dari suatu pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @param int|null $idBiodata
     * @param array|null $tipeReviewer
     * @return mixed
     */
    public function getReviewer(int $idPengajuanPendanaan, int $idBiodata = null, array $tipeReviewer = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->when($tipeReviewer, function ($query, $tipeReviewer) {
                return $query->whereIn('tipe_reviewer', $tipeReviewer);
            })
            ->where('id_biodata', $idBiodata)
            ->first();
    }

    /**
     * Mendapattkan jumlah reviewer pada pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @param array|null $tipeReviewer
     * @return int
     */
    public function jumlahReviewer(int $idPengajuanPendanaan, array $tipeReviewer = null): int
    {
        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->when($tipeReviewer, function ($query, $tipeReviewer) {
                return $query->whereIn('tipe_reviewer', $tipeReviewer);
            })
            ->count();
    }

    /**
     * Cek apakah pengajuan pendanaan memiliki reviewer kegiatan.
     *
     * @param int $idPengajuanPendanaan
     * @return bool
     */
    public function apakahMemilikiReviewer(int $idPengajuanPendanaan): bool
    {
        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->exists();
    }

    /**
     * Cek apakah merupakan reviewer kegiatan berdasarkan sumber pendanaan.
     *
     * @param int $idSumberPendanaan
     * @param int|null $idBiodata
     * @return bool
     */
    public function isReviewerBySumberPendanaan(int $idSumberPendanaan, int $idBiodata = null): bool
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::join('litabmas.pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan_reviewer_kegiatan.id_pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id_sumber_pendanaan', $idSumberPendanaan)
            ->where('pengajuan_pendanaan_reviewer_kegiatan.id_biodata', $idBiodata)
            ->exists();
    }

    /**
     * Get daftar id biodata dari reviewer suatu pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return array
     */
    public function getDaftarIdBiodataReviewer(int $idPengajuanPendanaan): array
    {
        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->pluck('id_biodata')
            ->toArray();
    }

    /**
     * Get daftar id biodata reviewer berdasarkan sumber pendanaan.
     *
     * @param int $idSumberPendanaan
     * @return array
     */
    public function getDaftarIdBiodataReviewerBySumberPendanaan(int $idSumberPendanaan): array
    {
        return $this->model::join('litabmas.pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan_reviewer_kegiatan.id_pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id_sumber_pendanaan', $idSumberPendanaan)
            ->pluck('litabmas.pengajuan_pendanaan_reviewer_kegiatan.id_biodata')
            ->toArray();
    }
}
