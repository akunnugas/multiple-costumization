<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Illuminate\Support\Collection;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\PihakPenanggungJawab;

class PihakPenanggungJawabManagementService
{
    /**
     * @var PihakPenanggungJawab
     */
    protected $model = PihakPenanggungJawab::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PihakPenanggungJawab;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return PihakPenanggungJawab|Error
     */
    public function show(int $id): PihakPenanggungJawab|Error
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return PihakPenanggungJawab|Error
     */
    public function store(array $data): PihakPenanggungJawab|Error
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
     * @return PihakPenanggungJawab|Error
     */
    public function update(array $data, int $id): PihakPenanggungJawab|Error
    {
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
        try {
            return ManagementService::create($this->model)->destroySome($ids);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Mendapatkan semua pihak penanggung jawab by kerjasama
     * 
     * @param string $idKerjasama
     * @return Collection<PihakPenanggungJawab>
     */
    public function getAllPihakPenanggungJawab(string $modelType, string $idModel): Collection|Error
    {
        try {
            return $this->model
                ->with('penanggung_jawab')
                ->where('model_id', $idModel)
                ->where('model', $modelType)
                ->get();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function updateOrCreate(array $data): PihakPenanggungJawab|Error
    {
        try {
            return $this->model->updateOrCreate(
                [
                    'model' => $data['model'],
                    'model_id' => $data['model_id'],
                    'id_pihak' => $data['id_pihak'],
                    'pihak_ke' => $data['pihak_ke'],
                ],
                $data
            );
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function firstOrCreate(array $conditions, array $data): PihakPenanggungJawab|Error
{
    try {
        return $this->model->firstOrCreate($conditions, $data);
    } catch (Exception $e) {
        return new Error(exception: $e);
    }
}
}
