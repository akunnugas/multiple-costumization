<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Jobs\ProcessSyncIndikatorBobot;
use Modules\Core\Models\Shared\KlienConfig;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\DMS\Services\DokumenManagementService;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\TargetIndikator;

class PenilaianPanduanManagementService
{
    /**
     * @var PenilaianPanduan
     */
    protected $model = PenilaianPanduan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianPanduan;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql =
            "SELECT
                pp.id,
                pp.kode_penilaian_panduan,
                pp.nama_penilaian_panduan,
                pp.nama_singkat nama_singkat_penilaian_panduan,
                lk.nama_singkat nama_laporan_kinerja,
                ed.nama_singkat nama_panduan_evaluasi_diri,
                pp.apakah_aktif apakah_aktif_penilaian_panduan,
                pp.apakah_target_aktif apakah_target_aktif,
                pp.apakah_data_default
            FROM spmi.penilaian_panduan pp
            LEFT JOIN spmi.pengisian_panduan lk ON lk.id = pp.id_laporan_kinerja
                AND lk.waktu_dihapus IS NULL
            LEFT JOIN spmi.pengisian_panduan ed ON ed.id = lk.id_pengisian_panduan
                AND ed.waktu_dihapus IS NULL";

        $defaultFilter = "pp.waktu_dihapus is null";

        $fieldMap = [
            'nama_singkat_penilaian_panduan' => 'pp.nama_singkat',
            'nama_laporan_kinerja' => 'lk.nama_singkat',
            'nama_panduan_evaluasi_diri' => 'ed.nama_singkat',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PenilaianPanduan
     */
    public function show(int $id): PenilaianPanduan
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PenilaianPanduan
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        $file = $data['id_dokumen'] ?? null;

        $upload = new UploadDokumen();
        if (!empty($file)) {
            $upload = $upload->upload(
                file: $file,
                name: $data['nama_penilaian_panduan'],
                folderCode: UploadDokumen::SPMI_PANDUAN_PENILAIAN,
                moduleCode: Modul::CODE_SPMI,
                note: null,
                withTransaction: false
            );

            if ($upload instanceof Error) {
                return $upload;
            }

            $data['id_dokumen'] = $upload->get()->id;
        }

        $data['apakah_data_default'] = false;

        $model = $this->model->create($data);

        DB::commit();

        $kode_klien = KlienConfig::getKodeKlien();
        ProcessSyncIndikatorBobot::dispatch($kode_klien, null, null, [$model->id]);

        if (!empty($file)) {
            // Execute upload
            $upload->executeUpload();
        }

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PenilaianPanduan
     */
    public function update(array $data, int $id)
    {
        DB::beginTransaction();

        $model = $this->model->findOrFail($id);
        $file = $data['id_dokumen'] ?? null;

        $upload = new UploadDokumen();
        if (!empty($file) && !empty($model->id_dokumen)) {
            $upload = $upload->update(
                data: [
                    'file' => $file,
                    'name' => $data['nama_penilaian_panduan'],
                    'note' => null,
                ],
                id: $model->id_dokumen,
                withTransaction: false,
                isReplace: false
            );

            if ($upload instanceof Error) {
                return $upload;
            }

            $data['id_dokumen'] = $upload->get()?->id ?? $model->id_dokumen;
        }

        if (!empty($file) && empty($model->id_dokumen)) {
            $upload = $upload->upload(
                file: $file,
                name: $data['nama_penilaian_panduan'],
                folderCode: UploadDokumen::SPMI_PANDUAN_PENILAIAN,
                moduleCode: Modul::CODE_SPMI,
                note: null,
                withTransaction: false
            );

            if ($upload instanceof Error) {
                return $upload;
            }

            $data['id_dokumen'] = $upload->get()->id;
        }

        if (!$data['apakah_aktif'] && $model->apakah_aktif) {
            $hasMappingPanduan = MappingPanduan::where('id_penilaian_panduan', $model->id)->exists();
            if ($hasMappingPanduan) {
                DB::rollBack();
                return new Error('Penilaian panduan tidak bisa dinonaktifkan karena sudah di mapping dengan pengisian panduan.');
            }
        }

        $model->update($data);

        if (!empty($file)) {
            // Execute upload
            $upload->executeUpload();
        }

        DB::commit();

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        if ($this->checkReference($id)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        DB::beginTransaction();
        try {
            $model = $this->model->findOrFail($id);

            if (!empty($model->id_dokumen)) {
                // Hapus dokumen di dms juga
                (new DokumenManagementService)->destroy($model->id_dokumen);
            }

            $model->destroy($model->id);
        } catch (\Throwable) {
            DB::rollBack();
            return new Error('Gagal menghapus data');
        }
        DB::commit();

        return true;
    }

    public function destroySome($ids)
    {
        DB::beginTransaction();

        foreach ($ids as $id) {
            $model = $this->model->findOrFail($id);

            if ($model->apakah_data_default) {
                DB::rollBack();
                return new Error('Data default tidak bisa dihapus.');
            }

            if ($this->checkReference($id)) {
                DB::rollBack();
                return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
            }

            try {
                $model->delete();

                // Hapus dokumen di dms juga
                if ($model->id_dokumen) {
                    (new DokumenManagementService)->destroy($model->id_dokumen);
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                return new Error('Gagal menghapus data');
            }
        }

        DB::commit();

        return true;
    }

    protected function checkReference(int $id): bool
    {
        $isReferenceMapping = MappingPanduan::where('id_penilaian_panduan', $id)->exists();

        return $isReferenceMapping;
    }
}
