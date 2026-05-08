<?php

namespace Modules\Litabmas\Services;

use Modules\DMS\Models\Dokumen;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerAdministrasi;
use Modules\Litabmas\Models\PeriodePendanaan;

class PengajuanPendanaanReviewerAdministrasiService
{
    /**
     * @var PeriodePendanaan
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
     * Cek apakah merupakan reviewer administrasi berdasarkan sumber pendanaan.
     *
     * @param int $idSumberPendanaan
     * @param int|null $idBiodata
     * @return bool
     */
    public function isReviewerBySumberPendanaan(int $idSumberPendanaan, int $idBiodata = null): bool
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::join('litabmas.pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan_reviewer_administrasi.id_pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id_sumber_pendanaan', $idSumberPendanaan)
            ->where('pengajuan_pendanaan_reviewer_administrasi.id_biodata', $idBiodata)
            ->exists();
    }

    /**
     * Get id pengajuan pendanaan reviewer by id pengajuan pendanaan and id biodata.
     *
     * @param int $idPengajuanPendanaan
     * @param int|null $idBiodata
     * @return mixed
     */
    public function getIdPengajuanPendanaanReviewer(int $idPengajuanPendanaan, int $idBiodata = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->where('id_biodata', $idBiodata)
            ->select('id')
            ->first()?->id;
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
        return $this->model::join('litabmas.pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan_reviewer_administrasi.id_pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id_sumber_pendanaan', $idSumberPendanaan)
            ->pluck('litabmas.pengajuan_pendanaan_reviewer_administrasi.id_biodata')
            ->toArray();
    }

    /**
     * Get detail reviewer dari suatu pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @param int|null $idBiodata
     * @return mixed
     */
    public function getReviewer(int $idPengajuanPendanaan, int $idBiodata = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->where('id_biodata', $idBiodata)
            ->first();
    }

    /**
     * Mendapattkan jumlah reviewer pada pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return int
     */
    public function jumlahReviewer(int $idPengajuanPendanaan): int
    {
        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->count();
    }

    /**
     * Get daftar nilai komposisi proposal dari semua reviewer berdasarkan pendanaan tertentu.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getNilaiKomposisiAllReviewer(int $idPengajuanPendanaan)
    {
        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->join('core.biodata as b', 'b.id', '=', 'litabmas.pengajuan_pendanaan_reviewer_administrasi.id_biodata')
            ->select('reviewer_ke', 'pengajuan_pendanaan_reviewer_administrasi.id_biodata', 'b.nama', 'total_nilai_komposisi_reviewer')
            ->orderBy('reviewer_ke')
            ->get()
            ->unique('id_biodata');
    }

    /**
     * Get daftar rekomendasi anggaran dari semua reviewer berdasarkan pendanaan tertentu.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getRekomendasiAnggaranAllReviewer(int $idPengajuanPendanaan)
    {
        return $this->model::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->join('core.biodata as b', 'b.id', '=', 'litabmas.pengajuan_pendanaan_reviewer_administrasi.id_biodata')
            ->select('reviewer_ke', 'pengajuan_pendanaan_reviewer_administrasi.id_biodata', 'b.nama', 'rekomendasi_anggaran', 'mata_uang')
            ->orderBy('reviewer_ke')
            ->get()
            ->unique('id_biodata');
    }
}
