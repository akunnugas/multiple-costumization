<?php

namespace Modules\Kerjasama\Services;

use DB;
use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\IndikatorSasaran;

class IndikatorSasaranManagementService
{
    /**
     * @var IndikatorSasaran
     */
    protected $model = IndikatorSasaran::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new IndikatorSasaran;
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
     * @return IndikatorSasaran|Error
     */
    public function show(int $id): IndikatorSasaran|Error
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
     * @return IndikatorSasaran|Error
     */
    public function store(array $data): IndikatorSasaran|Error
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
     * @return IndikatorSasaran|Error
     */
    public function update(array $data, int $id): IndikatorSasaran|Error
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

    public function destroyBySasaran(string $idSasaran, array $exceptIds = []): null|Error
    {
        try {
            $this->model->query()
                ->where('id_sasaran_kinerja', $idSasaran)
                ->when(!empty($exceptIds), function ($query) use ($exceptIds) {
                    $query->whereNotIn('id', $exceptIds);
                })
                ->delete();
            return null;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
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

    public function getIndikatorSasaranBySasaranKineja($idSasaranKinerja)
    {
        $indikatorSasaranOptions = DB::table($this->model->getTable() . ' AS iks')
            ->where('iks.id_sasaran_kinerja', $idSasaranKinerja)
            ->whereNull('iks.waktu_dihapus')
            ->select(['iks.id', 'iks.indikator']);

        return $indikatorSasaranOptions
            ->pluck('indikator', 'id')
            ->toArray();
    }
}
