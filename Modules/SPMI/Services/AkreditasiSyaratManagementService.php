<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\AkreditasiSyarat;

class AkreditasiSyaratManagementService
{
    /**
     * @var AkreditasiSyarat
     */
    protected $model = AkreditasiSyarat::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AkreditasiSyarat;
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
        $table = $this->model->getTable();
        $sql = "SELECT
                    ar.*,
                    m.pertanyaan_penilaian AS nama_matriks
                FROM $table ar
                JOIN spmi.penilaian_panduan ag ON ag.id = ar.id_penilaian_panduan
                    AND ag.waktu_dihapus IS NULL
                JOIN spmi.penilaian_matriks m ON m.id = ar.id_penilaian_matriks
                    AND m.waktu_dihapus IS NULL
                JOIN spmi.akreditasi_peringkat r ON r.id = ar.id_akreditasi_peringkat
                    AND r.waktu_dihapus IS NULL";

        $fieldMap = [
            'filter_penilaian_panduan' => 'ag.id',
            'id_penilaian_panduan' => 'ag.nama_singkat',
            'id_penilaian_matriks' => 'm.pertanyaan_penilaian',
            'id_akreditasi_peringkat' => 'r.nama_peringkat_akreditasi',
        ];

        $defaultFilter = "ar.waktu_dihapus IS NULL";

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
     * @return AkreditasiSyarat
     */
    public function show(int $id): AkreditasiSyarat
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return AkreditasiSyarat
     */
    public function store(array $data): AkreditasiSyarat
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
     * @return AkreditasiSyarat
     */
    public function update(array $data, int $id): AkreditasiSyarat
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
     * @return void
     */
    public function destroy(int $id): void
    {
        $model = $this->model->findOrFail($id);

        $model->destroy($model->id);
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }
}
