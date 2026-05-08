<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\PenanggungJawab;

class PenanggungJawabManagementService
{
    /**
     * @var PenanggungJawab
     */
    protected $model = PenanggungJawab::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenanggungJawab;
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
     * @return PenanggungJawab|Error
     */
    public function show(int $id): PenanggungJawab|Error
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
     * @return PenanggungJawab|Error
     */
    public function store(array $data): PenanggungJawab|Error
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
     * @return PenanggungJawab|Error
     */
    public function update(array $data, int $id): PenanggungJawab|Error
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

    public function destroyByPihakPenanggungJawab(array|string|int $idsPihakPenanggungjawab, array $exceptIds)
    {
        // convert menjadi array jika berupa string atau integer
        if (!is_array($idsPihakPenanggungjawab)) {
            $idsPihakPenanggungjawab = [ $idsPihakPenanggungjawab ];
        }

        try {
            $this->model->query()
                ->whereIn('id_pihak_penanggung_jawab', $idsPihakPenanggungjawab)
                ->when(!empty($exceptIds), function ($query) use ($exceptIds) {
                    $query->whereNotIn('id', $exceptIds);
                })
                ->delete();
            return null;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function updateOrCreate(array $conditions, array $data): PenanggungJawab|Error
    {
        try {
            return $this->model->updateOrCreate($conditions, $data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function firstOrCreate(array $conditions, array $data): PenanggungJawab|Error
{
    try {
        return $this->model->firstOrCreate($conditions, $data);
    } catch (Exception $e) {
        return new Error(exception: $e);
    }
}
}
