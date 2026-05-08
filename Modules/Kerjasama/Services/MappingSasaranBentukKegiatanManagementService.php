<?php

namespace Modules\Kerjasama\Services;

use DB;
use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\MappingSasaranBentukKegiatan;

class MappingSasaranBentukKegiatanManagementService
{
    /**
     * @var MappingSasaranBentukKegiatan
     */
    protected $model = MappingSasaranBentukKegiatan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new MappingSasaranBentukKegiatan;
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
     * @return MappingSasaranBentukKegiatan|Error
     */
    public function show(int $id): MappingSasaranBentukKegiatan|Error
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
     * @return MappingSasaranBentukKegiatan|Error
     */
    public function store(array $data): MappingSasaranBentukKegiatan|Error
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
     * @return MappingSasaranBentukKegiatan|Error
     */
    public function update(array $data, int $id): MappingSasaranBentukKegiatan|Error
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
     * Hapus beberapa data berdasarkan id_bentuk_kegiatan dan id_sasaran_kinerja.
     *
     * @param $ids
     * @return null|Error
     */
    public function destroySomeByBentukKegiatan($idBentukKegiatan, array $ids): null|Error
    {
        try {
            DB::transaction(function () use ($idBentukKegiatan, $ids) {
                $this->model
                    ->where('id_bentuk_kegiatan', $idBentukKegiatan)
                    ->whereIn('id_sasaran_kinerja', $ids)
                    ->delete();
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    public function getSasaranKinerjaByBentukKegiatan(int $idBentukKegiatan)
    {
        $sasaranOPtions = DB::table($this->model->getTable().' AS mp')
            ->where('mp.id_bentuk_kegiatan', $idBentukKegiatan)
            ->whereNull('mp.waktu_dihapus')
            ->leftJoin('kerjasama.sasaran_kinerja AS sk', 'sk.id', '=', 'mp.id_sasaran_kinerja')
            ->select(['sk.id', 'sk.sasaran']);

        return $sasaranOPtions->pluck('sasaran', 'id')->toArray();
    }
}
