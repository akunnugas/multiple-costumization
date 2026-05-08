<?php

namespace Modules\HR\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\SyncSiakad;
use Modules\HR\Models\WorkRelation;

class WorkRelationManagementService
{
    /**
     * @var WorkRelation
     */
    protected $model = WorkRelation::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new WorkRelation;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return WorkRelation
     */
    public function show(int $id): WorkRelation
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return WorkRelation
     */
    public function store(array $data): WorkRelation
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return WorkRelation
     */
    public function update(array $data, int $id): WorkRelation
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

    /**
     * Get data from SIAKAD V1 [ref.lv_statuspegawai]
     * 
     * @return array
     */
    public function getFromSiakadV1()
    {
        $connection = 'siakadv1';
        $query = "select 
            idhubkerja,
            hubkerja,
            ispns
            from hr.ms_hubkerja";

        $result = DB::connection($connection)->select($query);
        $result = json_decode(json_encode($result), true);

        return $result;
    }

    /**
     * Sync data from SIAKAD V1 to SIAKAD V2
     * 
     * return array
     */
    public function syncFromSiakadv1() 
    {
        $dataSiakad = $this->getFromSiakadV1();

        $mapping = [];
        $mapping['idhubkerja'] = ['column' => 'code'];
        $mapping['hubkerja'] = ['column' => 'name'];
        $mapping['ispns'] = ['column' => 'is_pns'];
        $mapping['iskatif'] = ['column' => 'is_active', 'default' => true];

        $pk = ['idhubkerja'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, $this->model, $pk);

        return [$err, $msg];
    }
}
