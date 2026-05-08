<?php

namespace Modules\HR\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\SyncSiakad;
use Modules\HR\Models\EmployeeStatus;

class EmployeeStatusManagementService
{
    /**
     * @var EmployeeStatus
     */
    protected $model = EmployeeStatus::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new EmployeeStatus;
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
     * @return EmployeeStatus
     */
    public function show(int $id): EmployeeStatus
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return EmployeeStatus
     */
    public function store(array $data): EmployeeStatus
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return EmployeeStatus
     */
    public function update(array $data, int $id): EmployeeStatus
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
                idstatusaktif,
                namastatusaktif,
                kodeemis,
                iskeluar
            from ref.lv_statuspegawai";

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
        
        // Mapping Data
        $dataSiakad = array_map(function ($item) {
            $isActive = true;

            if (!empty($item['iskeluar']) && $item['iskeluar'] === '1') {
                $isActive = false;
            }

            $item['is_active'] = $isActive;
            return $item;
        }, $dataSiakad);

        // mapping data
        foreach ($dataSiakad as $key => $data) {
            // jika iskeluar == '1' maka set custom_is_active = false
            $dataSiakad[$key]['custom_is_active'] = ($data['iskeluar'] == '1' ? false : true);
            unset($dataSiakad[$key]['iskeluar']);
        }

        $mapping = [];
        $mapping['idstatusaktif'] = ['column' => 'code'];
        $mapping['namastatusaktif'] = ['column' => 'name'];
        $mapping['kodeemis'] = ['column' => 'emis_code'];
        $mapping['custom_is_active'] = ['column' => 'is_active', 'default' => true];

        $pk = ['idstatusaktif'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, $this->model, $pk);

        return [$err, $msg];
    }
}
