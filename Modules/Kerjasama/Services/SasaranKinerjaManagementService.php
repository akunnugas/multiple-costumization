<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Kerjasama\Models\MappingSasaranBentukKegiatan;
use Modules\Kerjasama\Models\SasaranKinerja;

class SasaranKinerjaManagementService
{
    /**
     * @var SasaranKinerja
     */
    protected $model = SasaranKinerja::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SasaranKinerja;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "
            SELECT 
                sk.*,
                count(DISTINCT iks.id) as count_indikator             
            FROM $table sk
            LEFT JOIN
                kerjasama.indikator_sasaran iks
                ON iks.id_sasaran_kinerja = sk.id
            ";


        $defaultFilter = "sk.waktu_dihapus IS NULL";

        $fieldMap = [
            'keterangan' => 'sk.keterangan'
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap:$fieldMap,
            groupBy: "sk.id"
        );

        return Pagination::create($sql, $bindings, $page, $perPage);    
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return SasaranKinerja|Error
     */
    public function show(int $id): SasaranKinerja|Error
    {
        try {
            return $this->model->with(['indikator'])->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return SasaranKinerja|Error
     */
    public function store(array $data): SasaranKinerja|Error
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return SasaranKinerja|Error
     */
    public function update(array $data, int $id): SasaranKinerja|Error
    {
        [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id, 'mengubah');

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        try {
            $model = $this->model->findOrFail($id);
            $model->update($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id);

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        try {
            $model = $this->model->findOrFail($id);
            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return null|Error
     */
    public function destroySome($ids): null|Error
    {
        foreach ($ids as $id) {
            [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id);

            if ($isDataDefault) {
                return new Error($errorMessage);
            }
        }

        try {
            return ManagementService::create($this->model)->destroySome($ids);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    protected function isianDefaultVaidation(int $id, string $actionLabel = "menghapus"): array
    {
        $data = $this->model
            ->where('id', $id)
            ->first();

        if (!$data->isian_default) {
            return [false, ""];
        }

        return [true, "Gagal $actionLabel Data '".$data->sasaran."' karena merupakan data default"];
    }
}
