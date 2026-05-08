<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\PenilaianPanduan ;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\AkreditasiStandar;

class JenisStandarManagementService
{
    /**
     * @var JenisStandar
     */
    protected $model = JenisStandar::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JenisStandar;
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
        $sql = "SELECT js.*, COALESCE(aks.total_standart, 0) AS total_standart
            FROM spmi.jenis_standar js
            LEFT JOIN (
                SELECT COUNT(aks.id) AS total_standart, aks.id_jenis_standar
                FROM spmi.akreditasi_standar aks
                WHERE aks." . AkreditasiStandar::DELETED_AT . " IS NULL
                GROUP BY aks.id_jenis_standar
            ) aks ON aks.id_jenis_standar = js.id";

        $defaultFilter = " js." . JenisStandar::DELETED_AT . " IS NULL ";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return JenisStandar
     */
    public function show(int $id): JenisStandar
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JenisStandar
     */
    public function store(array $data): JenisStandar
    {
        $data['apakah_data_default'] = false;

        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JenisStandar
     */
    public function update(array $data, int $id): JenisStandar
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

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

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
        }

        return true;
    }

    /**
     * Get List of Standard Type
     *
     * @return array
     */
    public function getListOption(): array
    {
        return $this->model->pluck('nama_jenis_standar', 'id')->toArray();
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        if ($this->checkReferenceMultiple($ids)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        DB::beginTransaction();

        foreach ($ids as $id) {
            $model = $this->model->findOrFail($id);

            if ($model->apakah_data_default) {
                DB::rollBack();
                return new Error('Data default tidak bisa dihapus.');
            }

            try {
                $model->delete();
            } catch (\Throwable $e) {
                DB::rollBack();
                return new Error('Gagal menghapus data');
            }
        }

        DB::commit();

        return true;
    }

    public function storeWithStandart($jenisStandart, array $standartAkreditasi, bool $isCreate = false)
    {
        DB::beginTransaction();
        try {
            if (!isset($jenisStandart['apakah_data_default'])) {
                $jenisStandart['apakah_data_default'] = false;
            }

            if (!$isCreate) {
                $jenisStandarModel = $this->update($jenisStandart, $jenisStandart['id']);
            } else {
                $jenisStandarModel = $this->store($jenisStandart);
            }

            $kodeStandart = array_column($standartAkreditasi, 'kode_butir');
            $kodeStandarLama = AkreditasiStandar::where('id_jenis_standar', $jenisStandarModel->id)
            ->pluck('kode_standar')
            ->toArray();

            foreach ($standartAkreditasi as $standart) {
                AkreditasiStandar::updateOrCreate(
                    [
                        'id_jenis_standar' => $jenisStandarModel->id,
                        'kode_standar' => $standart['kode_butir'],
                    ],
                    [
                        'nama_standar' => $standart['nama_butir'],
                        'kode_standar' => $standart['kode_butir'],
                        'apakah_data_default' => false,
                    ]
                );
            }

            // Compare antara id lama dan baru
            $kodeStandarDiff = array_diff($kodeStandarLama, $kodeStandart);
            if (!empty($kodeStandarDiff)) {
                AkreditasiStandar::where('id_jenis_standar', $jenisStandarModel->id)
                    ->whereIn('kode_standar', $kodeStandarDiff)
                    ->delete();
            }

            DB::commit();

            return $jenisStandarModel;
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error('Gagal menyimpan data jenis standar dan butir standar.');
        }
    }

    protected function checkReference($id)
    {
        $isReferencePenilaianPanduan = PenilaianPanduan::where('id_jenis_standar', $id)->exists();

        return $isReferencePenilaianPanduan;
    }

    protected function checkReferenceMultiple($ids)
    {
        $isReferencePenilaianPanduan = PenilaianPanduan::whereIn('id_jenis_standar', $ids)->exists();

        return $isReferencePenilaianPanduan;
    }
}
