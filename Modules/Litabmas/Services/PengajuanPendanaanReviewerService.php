<?php

namespace Modules\Litabmas\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;
use Modules\Litabmas\Models\PeriodePendanaan;

class PengajuanPendanaanReviewerService
{
    /**
     * @var PeriodePendanaan
     */
    protected $model = PengajuanPendanaanReviewer::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanReviewer;
    }

    public function index($id, $filter = [])
    {
        return DB::table('litabmas.pengajuan_pendanaan_reviewers')
            ->where('id_pengajuan_pendanaan', $id)
            ->when(!empty($filter), function ($query) use ($filter) {
                return $query->where($filter);
            })
            ->join('core.biodata as b', 'b.id', '=', 'litabmas.pengajuan_pendanaan_reviewers.id_biodata')
            ->select('litabmas.pengajuan_pendanaan_reviewers.*', 'b.nama as nama_reviewer')
            ->orderBy('reviewer_ke')
            ->get();
    }

    public function store(array $data): PengajuanPendanaanReviewer|Error
    {
        // Generate reviewer_ke
        if (empty($data['reviewer_ke'])) {
            $noReviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])->orderBy('reviewer_ke', 'desc')->first()?->reviewer_ke;

            if ($noReviewer) {
                $data['reviewer_ke'] = $noReviewer + 1;
            } else {
                $data['reviewer_ke'] = 1;
            }
        }

        // Check if reviewer proposal, output, or progress report already max
        if (isset($data['apakah_review_proposal']) && $data['apakah_review_proposal']) {
            $countRp = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
                ->where('apakah_review_proposal', true)
                ->count();

            if ($countRp >= PengajuanPendanaanReviewer::MAX_REVIEWER) {
                return new Error('tipe_reviewer:Maksimal ' . PengajuanPendanaanReviewer::MAX_REVIEWER . ' reviewer proposal');
            }
        }

        if (isset($data['apakah_review_luaran']) && $data['apakah_review_luaran']) {
            $countRo = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
                ->where('apakah_review_luaran', true)
                ->count();

            if ($countRo >= PengajuanPendanaanReviewer::MAX_REVIEWER) {
                return new Error('tipe_reviewer:Maksimal ' . PengajuanPendanaanReviewer::MAX_REVIEWER . ' reviewer luaran');
            }
        }

        if (isset($data['apakah_review_antara']) && $data['apakah_review_antara']) {
            $countRpr = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
                ->where('apakah_review_antara', true)
                ->count();

            if ($countRpr >= PengajuanPendanaanReviewer::MAX_REVIEWER) {
                return new Error('tipe_reviewer:Maksimal ' . PengajuanPendanaanReviewer::MAX_REVIEWER . ' reviewer antara');
            }
        }

        // check if id_biodata is already a reviewer
        $isReviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
            ->where('id_biodata', $data['id_biodata'])
            ->count();

        if ($isReviewer) {
            return new Error('id_biodata:Reviewer sudah ada');
        }

        $data['status_penilaian_progress_report'] = PengajuanPendanaanReviewer::STATUS_PENILAIAN_BELUM_DINILAI;
        $data['status_penilaian_output'] = PengajuanPendanaanReviewer::STATUS_PENILAIAN_BELUM_DINILAI;
        $data['status_penilaian_output_bersama'] = PengajuanPendanaanReviewer::STATUS_PENILAIAN_BELUM_DINILAI;

        try {
            $file = null;
            if ($data['id_dokumen_sk']) {
                $file = $data['id_dokumen_sk'] ?? null;

                unset($data['id_dokumen_sk']);
            }

            if ($file) {
                $fileName = explode('.', $file?->getClientOriginalName())[0];

                $upload = new UploadDokumen();
                $upload = $upload->upload(
                    file: $file,
                    name: $fileName,
                    folderCode: UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_REVIEWER_SK,
                    moduleCode: Modul::CODE_LITABMAS,
                    note: null,
                    withTransaction: false
                );

                if (Error::isError($upload->getError())) {
                    return new Error($upload->getError());
                }

                // Execute upload
                $upload->executeUpload();

                $data['id_dokumen_sk'] = $upload->get()?->id;
            }

            $model = $this->model->create($data);
        } catch (\Throwable $th) {
            return new Error($th->getMessage());
        }

        return $model;
    }

    public function update(array $data, $id): PengajuanPendanaanReviewer|Error
    {
        $model = $this->model->find($id);

        if (!$model) {
            return new Error('Data tidak ditemukan');
        }

        // Check if reviewer proposal, output, or progress report already max
        if (isset($data['apakah_review_proposal']) && $data['apakah_review_proposal']) {
            $countRp = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
                ->where('id', '!=', $id)
                ->where('apakah_review_proposal', true)
                ->count();

            if ($countRp >= PengajuanPendanaanReviewer::MAX_REVIEWER) {
                return new Error('tipe_reviewer:Maksimal ' . PengajuanPendanaanReviewer::MAX_REVIEWER . ' reviewer proposal');
            }
        } else {
            $data['apakah_review_proposal'] = false;
        }

        if (isset($data['apakah_review_luaran']) && $data['apakah_review_luaran']) {
            $countRo = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
                ->where('id', '!=', $id)
                ->where('apakah_review_luaran', true)
                ->count();

            if ($countRo >= PengajuanPendanaanReviewer::MAX_REVIEWER) {
                return new Error('tipe_reviewer:Maksimal ' . PengajuanPendanaanReviewer::MAX_REVIEWER . ' reviewer luaran');
            }
        } else {
            $data['apakah_review_luaran'] = false;
        }

        if (isset($data['apakah_review_antara']) && $data['apakah_review_antara']) {
            $countRpr = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])
                ->where('id', '!=', $id)
                ->where('apakah_review_antara', true)
                ->count();

            if ($countRpr >= PengajuanPendanaanReviewer::MAX_REVIEWER) {
                return new Error('tipe_reviewer:Maksimal ' . PengajuanPendanaanReviewer::MAX_REVIEWER . ' reviewer antara');
            }
        } else {
            $data['apakah_review_antara'] = false;
        }

        // check if id_biodata is different or not
        if ($model->id_biodata != $data['id_biodata']) {
            $isReviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $model->id_pengajuan_pendanaan)
                ->where('id_biodata', $data['id_biodata'])
                ->count();

            if ($isReviewer) {
                return new Error('id_biodata:Reviewer sudah ada');
            }
        }

        try {
            $file = null;
            if ($data['id_dokumen_sk'] && !is_numeric($data['id_dokumen_sk'])) {
                $file = $data['id_dokumen_sk'] ?? null;

                unset($data['id_dokumen_sk']);
            }

            if ($file) {
                $fileName = explode('.', $file?->getClientOriginalName())[0];

                $upload = new UploadDokumen();
                $upload = $upload->upload(
                    file: $file,
                    name: $fileName,
                    folderCode: UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_REVIEWER_SK,
                    moduleCode: Modul::CODE_LITABMAS,
                    note: null,
                    withTransaction: false
                );

                if (Error::isError($upload->getError())) {
                    return new Error($upload->getError());
                }

                // Execute upload
                $upload->executeUpload();

                $data['id_dokumen_sk'] = $upload->get()?->id;
            }

            $model->update($data);
        } catch (\Throwable $th) {
            return new Error($th->getMessage());
        }

        return $model;
    }

    public function destroy($id)
    {
        $reviewer = PengajuanPendanaanReviewer::find($id);
        $noReviewer = $reviewer->reviewer_ke;

        $reviewer->delete();

        // Update reviewer_ke
        $reviewers = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $reviewer->id_pengajuan_pendanaan)
            ->where('reviewer_ke', '>', $noReviewer)
            ->get();

        foreach ($reviewers as $r) {
            $r->update(['reviewer_ke' => $r->reviewer_ke - 1]);
        }

        return $reviewer;
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

        return $this->model::join('litabmas.pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan_reviewers.id_pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id_sumber_pendanaan', $idSumberPendanaan)
            ->where('pengajuan_pendanaan_reviewers.id_biodata', $idBiodata)
            ->exists();
    }

    public function getDaftarIdBiodataReviewerBySumberPendanaan(int $idSumberPendanaan): array
    {
        return $this->model::join('litabmas.pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan_reviewers.id_pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id_sumber_pendanaan', $idSumberPendanaan)
            ->pluck('litabmas.pengajuan_pendanaan_reviewers.id_biodata')
            ->toArray();
    }

    public function getPenilaianReviewerOutput($idPengajuanPendanaan)
    {
        $data = DB::table('litabmas.penilaian_reviewer_output as pro')
            ->join('litabmas.pengajuan_pendanaan_reviewers as ppr', 'ppr.id', '=', 'pro.id_pengajuan_pendanaan_reviewer')
            ->where('ppr.id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->select('pro.*')
            ->get();

        return $data;
    }

    public function getDataOutputLuaranReviewer($id)
    {
        return DB::table('litabmas.pengajuan_pendanaan_output_penelitian as a')
            ->join('litabmas.pengajuan_pendanaan as p', 'p.id', '=', 'a.id_pengajuan_pendanaan')
            ->join('litabmas.klaster_pendanaan_output_penelitian as op', function ($join) {
                $join->on('op.id_klaster_pendanaan', '=', 'p.id_klaster_pendanaan')
                    ->on('a.id_jenis_output_penelitian', '=', 'op.id_jenis_output_penelitian');
            })
            ->join('litabmas.jenis_output_penelitian as b', 'b.id', '=', 'a.id_jenis_output_penelitian')
            ->leftJoin('litabmas.pengajuan_pendanaan_laporan_output as o', 'o.id_pengajuan_pendanaan_output_penelitian', '=', 'a.id')
            ->where('a.id_pengajuan_pendanaan', $id)
            ->select('b.*', 'a.id', 'o.id_dokumen_output', 'op.apakah_wajib')
            ->get();
    }

    public function getDataReviewerOutputLuaran($idReviwer) {
        return DB::table('litabmas.penilaian_reviewer_output as a')
            ->where('a.id_pengajuan_pendanaan_reviewer', $idReviwer)
            ->select('a.*')
            ->get()
            ->groupBy('id_pengajuan_pendanaan_output_penelitian');
    }
}
