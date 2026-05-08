<?php

namespace Modules\Litabmas\Services;

use Modules\Core\Helpers\Error;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\PengajuanPendanaanLaporanOutput;

class PengajuanPendanaanLaporanOutputService
{
    /**
     * @var PengajuanPendanaanLaporanOutput
     */
    protected $model = PengajuanPendanaanLaporanOutput::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanLaporanOutput;
    }

    public function storeDokumen(array $data, int $idProposalPendanaan)
    {
        if (!$data['id_dokumen_report']) {
            return new Error('Dokumen laporan luaran wajib diunggah.');
        }

        $file = $data['id_dokumen_report'];

        $fileName = explode('.', $file?->getClientOriginalName())[0];

        $upload = new UploadDokumen();
        $upload = $upload->upload(
            file: $file,
            name: $fileName,
            folderCode: UploadDokumen::LITABMAS_PENGAJUAN_PENDANAAN_LAPORAN_OUTPUT,
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

        PengajuanPendanaanLaporanOutput::updateOrCreate(
            [
                'id_pengajuan_pendanaan' => $idProposalPendanaan,
                'id_pengajuan_pendanaan_output_penelitian' => $data['id_pengajuan_pendanaan_output_penelitian'],
            ],
            [
                'id_dokumen_output' => $idDokumen,
                'status_output' => PengajuanPendanaanLaporanOutput::STATUS_BELUM_DINILAI,
            ]
        );

        return true;
    }
}
