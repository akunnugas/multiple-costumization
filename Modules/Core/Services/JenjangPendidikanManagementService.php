<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Core\Models\Cache\JenjangPendidikanCache;
use Modules\Core\Models\JenjangPendidikan;

class JenjangPendidikanManagementService
{
    /**
     * @var JenjangPendidikan
     */
    protected $model = JenjangPendidikan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JenjangPendidikan;
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
     * @return JenjangPendidikan
     */
    public function show(int $id): JenjangPendidikan
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JenjangPendidikan
     */
    public function store(array $data): JenjangPendidikan
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JenjangPendidikan
     */
    public function update(array $data, int $id): JenjangPendidikan
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
     * Get data from SIAKAD V1 [ref.ms_]
     *
     * @return array
     */
    public function getFromSiakadV1()
    {
        $connection = 'siakadv1';
        $query = "select
                idjenjang,
                namajenjang,
                namajenjangen,
                isakademik,
                ispt,
                ispasca,
                kodeemisjenjang,
                kodeemisjenjangpasca,
                kodeemisdosen,
                urutan
            from ref.lv_jenjangpendidikan";

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
        $mapping['idjenjang'] = ['column' => 'kode_jenjang'];
        $mapping['namajenjang'] = ['column' => 'nama_jenjang'];
        $mapping['namajenjangen'] = ['column' => 'nama_jenjang_en'];
        $mapping['isakademik'] = ['column' => 'apakah_akademik', 'default' => false];
        $mapping['ispt'] = ['column' => 'apakah_pt', 'default' => false];
        $mapping['ispasca'] = ['column' => 'apakah_pasca', 'default' => false];
        $mapping['kodeemisjenjang'] = ['column' => 'kode_emis'];
        $mapping['kodeemisjenjangpasca'] = ['column' => 'kode_emis_pasca'];
        $mapping['kodeemisdosen'] = ['column' => 'kode_emis_dosen'];
        $mapping['urutan'] = ['column' => 'urutan'];

        // unique column
        $pk = ['idjenjang'];

        list($err, $msg) = SyncSiakad::sync(
            mappings: $mapping,
            records: $dataSiakad,
            model: $this->model,
            pk: $pk,
            otherRefKeys: ['apakah_data_default'],
        );

        JenjangPendidikanCache::destroy();

        return [$err, $msg];
    }
}
