<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Date;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;
use Modules\Litabmas\Models\PenilaianReviewerLaporanProgres;

class PengajuanPendanaanLaporanProgresService
{
    /**
     * @var PengajuanPendanaanLaporanProgres
     */
    protected $model = PengajuanPendanaanLaporanProgres::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanLaporanProgres;
    }

    public function storeDokumen(array $data, int $idPengajuanPendanaan)
    {
        if (!$data['id_dokumen_report']) {
            return new Error('Dokumen laporan progres wajib diunggah.');
        }

        $file = $data['id_dokumen_report'];

        $fileName = explode('.', $file?->getClientOriginalName())[0];

        $upload = new UploadDokumen();
        $upload = $upload->upload(
            file: $file,
            name: $fileName,
            folderCode: UploadDokumen::LITABMAS_PENGAJUAN_PENDANAAN_LAPORAN_PROGRESS,
            moduleCode: Modul::CODE_LITABMAS,
            note: null,
            withTransaction: false
        );

        if (Error::isError($upload->getError())) {
            return new Error($upload->getError());
        }

        // Execute upload
        $upload->executeUpload();

        $idDokumen = $upload->get()?->id;

        PengajuanPendanaanLaporanProgres::updateOrCreate(
            [
                'id_pengajuan_pendanaan' => $idPengajuanPendanaan,
                'jenis_laporan_progres' => $data['jenis_laporan_progres'],
            ],
            [
                'id_dokumen_laporan_progres' => $idDokumen,
                'status_laporan_progres' => PengajuanPendanaanLaporanProgres::STATUS_LAP_BELUM_DINILAI,
            ]
        );
    }

    public function getDataLaporanProgress($id, $idReviewer)
    {
        return PengajuanPendanaanLaporanProgres::select('litabmas.pengajuan_pendanaan_laporan_progres.jenis_laporan_progres', 'litabmas.pengajuan_pendanaan_laporan_progres.id_dokumen_laporan_progres', 'b.status_laporan_progres_reviewer as status', 'b.feedback_laporan_progres as feedback')
            ->leftJoin('litabmas.penilaian_reviewer_laporan_progres as b', function ($join) use ($idReviewer) {
                $join->on('b.id_pengajuan_pendanaan_laporan_progres', '=', 'litabmas.pengajuan_pendanaan_laporan_progres.id')
                    ->on('b.id_pengajuan_pendanaan_reviewer', '=', DB::raw($idReviewer));
            })
            ->where('litabmas.pengajuan_pendanaan_laporan_progres.jenis_laporan_progres', PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES)
            ->where('litabmas.pengajuan_pendanaan_laporan_progres.id_pengajuan_pendanaan', $id)
            ->first();
    }

    public function getDataLapKeuangan($id, $idReviewer)
    {
        return PengajuanPendanaanLaporanProgres::select('litabmas.pengajuan_pendanaan_laporan_progres.jenis_laporan_progres', 'litabmas.pengajuan_pendanaan_laporan_progres.id_dokumen_laporan_progres', 'b.status_laporan_progres_reviewer as status', 'b.feedback_laporan_progres as feedback')
            ->leftJoin('litabmas.penilaian_reviewer_laporan_progres as b', function($join) use ($idReviewer) {
                $join->on('b.id_pengajuan_pendanaan_laporan_progres', '=', 'litabmas.pengajuan_pendanaan_laporan_progres.id')
                    ->on('b.id_pengajuan_pendanaan_reviewer', '=', DB::raw($idReviewer));
            })
            ->where('litabmas.pengajuan_pendanaan_laporan_progres.jenis_laporan_progres', PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA)
            ->where('litabmas.pengajuan_pendanaan_laporan_progres.id_pengajuan_pendanaan', $id)
            ->first();
    }
}
