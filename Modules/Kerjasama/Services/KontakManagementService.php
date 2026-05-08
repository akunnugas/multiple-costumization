<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Illuminate\Support\Collection;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\Kontak;

class KontakManagementService
{
    /**
     * @var Kontak
     */
    protected $model = Kontak::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Kontak;
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
     * @return Kontak|Error
     */
    public function show(int $id): Kontak|Error
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
     * @return Kontak|Error
     */
    public function store(array $data): Kontak|Error
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
     * @return Kontak|Error
     */
    public function update(array $data, int $id): Kontak|Error
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

    public function destroyByMitra(string $idMitra, array $exceptIds = []): null|Error
    {
        try {
            $this->model->query()
                ->where('id_mitra', $idMitra)
                ->when(!empty($exceptIds), function ($query) use ($exceptIds) {
                    $query->whereNotIn('id', $exceptIds);
                })
                ->delete();
            return null;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function getAllKontakByMitra(string $idMitra): Collection|Error
    {
        try {
            return $this->model
                ->where('id_mitra', $idMitra)
                ->get();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }
}
