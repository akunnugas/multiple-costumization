<?php

namespace Modules\SPMI\Services;

use Modules\SPMI\Models\PengisianPanduan;
use Modules\Gate\Models\Modul;
use Modules\DMS\Services\DokumenManagementService;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\PengisianIndikator;

class PengisianPanduanManagementService
{
    /**
     * @var PengisianPanduan
     */
    protected $model = PengisianPanduan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengisianPanduan;
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
                pp.nama_pengisian_panduan,
                pp.nama_singkat,
                pp.tipe_edisi,
                la.nama_lembaga nama_lembaga_akreditasi,
                pp.apakah_aktif,
                pp.apakah_data_default
            FROM spmi.pengisian_panduan pp
            LEFT JOIN core.lembaga_akreditasi la ON la.id = pp.id_lembaga_akreditasi
                AND la.waktu_dihapus IS NULL";

        $defaultFilter = "pp.waktu_dihapus is null";

        $fieldMap = [
            'nama_lembaga_akreditasi' => 'la.nama_lembaga',
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
     * @return PengisianPanduan
     */
    public function show(int $id): PengisianPanduan
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PengisianPanduan
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        $file = $data['id_dokumen'] ?? null;

        $upload = new UploadDokumen();
        if (!empty($file)) {
            $upload = $upload->upload(
                file: $file,
                name: $data['nama_pengisian_panduan'],
                folderCode: UploadDokumen::SPMI_PENJAMINAN_MUTU,
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
        $data['kode_level_akses'] = $data['kode_level_akses'] ?? PengisianPanduan::LEVEL_AKSES_PS;

        if ($data['tipe_edisi'] == AkreditasiBuku::SELF_EVALUATION) {
            $data['id_akreditasi_buku'] = AkreditasiBuku::where('kode_buku', 'LED')->first()->id;
        } elseif ($data['tipe_edisi'] == AkreditasiBuku::PERFORMANCE_REPORT) {
            $data['id_akreditasi_buku'] = AkreditasiBuku::where('kode_buku', 'LKPS')->first()->id;
        }

        $idMappingPengisian = null;
        if ($data['tipe_edisi'] == AkreditasiBuku::SELF_EVALUATION) {
            $idMappingPengisian = $data['id_pengisian_panduan'];
            unset($data['id_pengisian_panduan']);
        }

        $model = $this->model->create($data);

        if ($idMappingPengisian) {
            PengisianPanduan::where('id', $idMappingPengisian)->update(['id_pengisian_panduan' => $model->id]);
        }

        if (!empty($file)) {
            // Execute upload
            $upload->executeUpload();
        }

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PengisianPanduan
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
                    'name' => $data['nama_pengisian_panduan'] ?? $model->nama_pengisian_panduan,
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
                name: $data['nama_pengisian_panduan'] ?? $model->nama_pengisian_panduan,
                folderCode: UploadDokumen::SPMI_PENJAMINAN_MUTU,
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
            $hasMappingPanduan = MappingPanduan::where('id_pengisian_panduan', $model->id)->exists();
            if ($hasMappingPanduan) {
                DB::rollBack();
                return new Error('Pengisian panduan tidak bisa dinonaktifkan karena sudah di mapping dengan penilaian panduan.');
            }
        }

        if ($data['tipe_edisi'] == AkreditasiBuku::SELF_EVALUATION) {
            $isUnsetOnly = empty($data['id_pengisian_panduan']);
            if ($isUnsetOnly) {
                PengisianPanduan::where('id_pengisian_panduan', $model->id)->update(['id_pengisian_panduan' => null]);
            } else {
                $dataBefore = PengisianPanduan::where('id_pengisian_panduan', $model->id)->first();
                if ($dataBefore && $dataBefore->id_pengisian_panduan != $data['id_pengisian_panduan']) {
                    $dataBefore->update(['id_pengisian_panduan' => null]);
                }
                PengisianPanduan::where('id', $data['id_pengisian_panduan'])->update(['id_pengisian_panduan' => $model->id]);
            }
            unset($data['id_pengisian_panduan']);
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

        $model = $this->model->findOrFail($id);

        DB::beginTransaction();

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
        $isReferencePengisianIndikator = PengisianIndikator::where('id_pengisian_panduan', $id)->exists();
        $isReferenceIndikatorLed = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $id)->exists();
        $isReferenceIndikatorLK = IndikatorLaporanKinerja::where('id_pengisian_panduan', $id)->exists();

        return $isReferencePengisianIndikator || $isReferenceIndikatorLed || $isReferenceIndikatorLK;
    }
}
