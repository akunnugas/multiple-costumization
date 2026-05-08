<?php

namespace Modules\Litabmas\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\PengajuanPendanaanPembimbing;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;
use Modules\Litabmas\Models\PenilaianPembimbingAktivitasPenelitian;
use Modules\Litabmas\Models\PeriodePendanaan;

class PengajuanPendanaanPembimbingService
{
    /**
     * @var PeriodePendanaan
     */
    protected $model = PengajuanPendanaanPembimbing::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanPembimbing;
    }

    /**
     * Get daftar pembimbing dari suatu pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @param bool $withDetailDocumentSk
     * @return mixed
     */
    public function getDaftarPembimbing(int $idPengajuanPendanaan, bool $withDetailDocumentSk = false)
    {
        $pengajuanPendanaanPembimbings = DB::table('litabmas.pengajuan_pendanaan_pembimbing')
            ->where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->join('core.biodata as b', 'b.id', '=', 'litabmas.pengajuan_pendanaan_pembimbing.id_biodata')
            ->leftJoin('core.pegawai as p', 'p.id_biodata', '=', 'b.id')
            ->leftJoin('litabmas.dosen_eksternal as de', 'de.id_biodata', '=', 'b.id')
            ->selectRaw('pengajuan_pendanaan_pembimbing.id, pengajuan_pendanaan_pembimbing.id_biodata,
                pengajuan_pendanaan_pembimbing.id_dokumen_sk, b.nama, COALESCE(p.nip, de.nip) as nip,
                COALESCE(p.nip, de.nip) || \' - \' || b.nama as nip_nama')
            ->get()->unique('id_biodata');

        if (!$withDetailDocumentSk) {
            return $pengajuanPendanaanPembimbings;
        }

        // get detail document sk
        $documentSkIds = $pengajuanPendanaanPembimbings->pluck('id_dokumen_sk')->toArray();
        $documents = Dokumen::whereIn('id', $documentSkIds)
            ->select('id', 'nama_dokumen', 'extension_versi_terbaru', 'alamat_versi_terbaru')
            ->get();

        return $pengajuanPendanaanPembimbings->map(function ($item) use ($documents) {
            $document = $documents->where('id', $item->id_dokumen_sk)->first();
            if (empty($document)) {
                return $item;
            }

            $ext = $document->extension_versi_terbaru;
            $assetUrl = asset("images/$ext-solid.svg");

            $item->nama_dokumen = $document->nama_dokumen;
            $item->extension_versi_terbaru = $ext;
            $item->last_version_size = $document->last_version_size;
            $item->asset_url = $assetUrl;
            $item->temp_url = $document->lastVersionTemporaryUrl();
            return $item;
        });
    }

    public function store(array $data): PengajuanPendanaanPembimbing|Error
    {
        if ($this->model->where(['id_pengajuan_pendanaan' => $data['id_pengajuan_pendanaan'], 'id_biodata' => $data['id_biodata']])->exists()) {
            return new Error('id_biodata:Pembimbing sudah terdaftar');
        }

        if ($this->model->where('id_pengajuan_pendanaan', $data['id_pengajuan_pendanaan'])->count() >= PengajuanPendanaanPembimbing::MAX_PEMBIMBING) {
            return new Error('id_biodata:Anda hanya dapat menambahkan ' . PengajuanPendanaanPembimbing::MAX_PEMBIMBING . ' pembimbing');
        }

        if (PengajuanPendanaanReviewer::where(['id_pengajuan_pendanaan' => $data['id_pengajuan_pendanaan'], 'id_biodata' => $data['id_biodata']])->exists()) {
            return new Error('id_biodata:Sudah terdaftar sebagai reviewer pada proposal ini');
        }

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
                    folderCode: UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_PEMBIMBING_SK,
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

            $this->model->create($data);
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        return $this->model;
    }

    public function update(array $data, int $id): PengajuanPendanaanPembimbing|Error
    {
        $model = $this->model->find($id);

        if (empty($model)) {
            return new Error('id:Pembimbing tidak ditemukan');
        }

        if ($this->model->where(['id_pengajuan_pendanaan' => $data['id_pengajuan_pendanaan'], 'id_biodata' => $data['id_biodata']])
            ->where('id', '<>', $id)
            ->exists()) {
            return new Error('id_biodata:Pembimbing sudah terdaftar');
        }

        if (PengajuanPendanaanReviewer::where(['id_pengajuan_pendanaan' => $data['id_pengajuan_pendanaan'], 'id_biodata' => $data['id_biodata']])->exists()) {
            return new Error('id_biodata:Sudah terdaftar sebagai reviewer pada proposal ini');
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
                    folderCode: UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_PEMBIMBING_SK,
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
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
    }

    public function destroy(int $id)
    {
        $model = $this->model->find($id);

        if (empty($model)) {
            return new Error('Pembimbing tidak ditemukan');
        }

        $check = PenilaianPembimbingAktivitasPenelitian::where('id_pengajuan_pendanaan_pembimbing', $id)->exists();

        if ($check) {
            return new Error('Pembimbing tidak dapat dihapus karena sudah memiliki riwayat bimbingan');
        }

        $model->delete();

        return $model;
    }
}
