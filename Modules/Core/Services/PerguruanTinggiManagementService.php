<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Core\Models\PerguruanTinggi;

class PerguruanTinggiManagementService
{
    /**
     * @var PerguruanTinggi
     */
    protected $model = PerguruanTinggi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PerguruanTinggi;
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
     * @return PerguruanTinggi
     */
    public function show(int $id): PerguruanTinggi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PerguruanTinggi
     */
    public function store(array $data): PerguruanTinggi
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PerguruanTinggi
     */
    public function update(array $data, int $id): PerguruanTinggi
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
     * Sync data from SIAKAD V1 to SIAKAD V2
     *
     * return array
     */
    public function syncUniversitasFromSiakadV1($limit = null)
    {
        $siakadV1Connection = DB::connection('siakadv1');
        $sql = "select iduniversitas, namauniversitas, alamat, telepon from ref.ms_universitas " . ($limit ? "limit $limit" : '');

        try {
            $result = $siakadV1Connection->select($sql);
            $universitas = json_decode(json_encode($result), true);
        } catch (Exception $e) {
            return [true, $e->getMessage()];
        }

        // mapping column
        $pk = ['iduniversitas'];
        $mapping = [
            'iduniversitas' => ['column' => 'kode_pt', 'notnull' => true],
            'namauniversitas' => ['column' => 'nama_pt', 'notnull' => true],
            'alamat' => ['column' => 'alamat_pt'],
            'telepon' => ['column' => 'telepon_pt'],
        ];

        list($err, $msg) = SyncSiakad::sync($mapping, $universitas, new PerguruanTinggi, $pk);

        return [$err, $msg];
    }
}
