<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaService;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\PaginationRequest;

use Modules\Core\Models\Wilayah;
use Modules\Core\Helpers\Neofeeder;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ServiceReturn;
use Modules\Core\Helpers\SyncSiakadV2;

class WilayahManagementService extends SevimaService
{
    /**
     * @var Wilayah
     */
    protected $model = Wilayah::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Wilayah;
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
     * @return Wilayah
     */
    public function show(int $id): Wilayah
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Wilayah
     */
    public function store(array $data): Wilayah
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Wilayah
     */
    public function update(array $data, int $id): Wilayah
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
     * Menampilkan list data Negara
     *
     * @param PaginationRequest $request
     * @return mixed
     */
    public function indexNegara(PaginationRequest $request)
    {
        $table = $this->model->getTable();
        $sql = "select * from $table";

        // binding default filter
        $defaultFilter = 'waktu_dihapus is null and level_wilayah = :level_wilayah';
        $bindings = ['level_wilayah' => $this->model::LEVEL_NEGARA];

        return $this->createPagination(
            $sql,
            $request,
            defaultOrder: 'nama_wilayah',
            defaultFilter: $defaultFilter,
            defaultFilterBindings: $bindings
        );
    }

    /**
     * Menampilkan list data Provinsi
     *
     * @param PaginationRequest $request
     * @return mixed
     */
    public function indexProvinsi(PaginationRequest $request)
    {
        $table = $this->model->getTable();
        $sql = "select * from $table";

        // binding default filter
        $defaultFilter = 'waktu_dihapus is null and level_wilayah = :level_wilayah';
        $bindings = ['level_wilayah' => $this->model::LEVEL_PROVINSI];

        return $this->createPagination(
            $sql,
            $request,
            defaultOrder: 'nama_wilayah',
            defaultFilter: $defaultFilter,
            defaultFilterBindings: $bindings
        );
    }

    /**
     * Menampilkan list data Kab/Kota
     *
     * @param PaginationRequest $request
     * @return mixed
     */
    public function indexKota(PaginationRequest $request)
    {
        $table = $this->model->getTable();
        $sql = "select * from $table";

        // binding default filter
        $defaultFilter = 'waktu_dihapus is null and level_wilayah = :level_wilayah';
        $bindings = ['level_wilayah' => $this->model::LEVEL_KOTA_KABUPATEN];

        return $this->createPagination(
            $sql,
            $request,
            defaultOrder: 'nama_wilayah',
            defaultFilter: $defaultFilter,
            defaultFilterBindings: $bindings
        );
    }

    /**
     * Menampilkan list data Kecamatan
     *
     * @param PaginationRequest $request
     * @return mixed
     */
    public function indexKecamatan(PaginationRequest $request)
    {
        $table = $this->model->getTable();
        $sql = "select * from $table";

        // binding default filter
        $defaultFilter = 'waktu_dihapus is null and level_wilayah = :level_wilayah';
        $bindings = ['level_wilayah' => $this->model::LEVEL_KECAMATAN];

        return $this->createPagination(
            $sql,
            $request,
            defaultOrder: 'nama_wilayah',
            defaultFilter: $defaultFilter,
            defaultFilterBindings: $bindings
        );
    }

    /**
     * Menampilkan opsi untuk kebutuhan filter.
     * @return array
     */
    public function options(int $level)
    {
        return $this->model->where('level_wilayah', $level)
            ->orderBy('nama_wilayah')->get(['id', 'nama_wilayah'])->pluck('nama_wilayah', 'id')->toArray();
    }

    public function getWilayahIndonesia()
    {
        return ServiceReturn::value(Wilayah::where('kode_wilayah', 'IDN')->first());
    }

    /**
     * @param array $levelWilayahV1
     * @param bool $isInitSync
     * @return array
     */
    private function getDataWilayahNonNegaraFromV1(array $levelWilayahV1, bool $isInitSync = false)
    {
        $lastSyncAt = Wilayah::where('level_wilayah', '<>', Wilayah::LEVEL_NEGARA)->max('waktu_terakhir_sync');

        // pengkondisian khusus level_wilayah, karena definisinya berbeda antara v1 dan v2
        $query = "select idkota, namakota, iddikti, parentkota, sum(levelkota::int + 1) as levelkota
            from ref.lv_kota
            where softdelete = '0'";
        $bindings = [];
        if (!empty($lastSyncAt) && empty($isInitSync)) {
            $query .= " and t_updatetime > ?";
            $bindings[] = date('Y-m-d H:i:s', strtotime($lastSyncAt));
        }

        if (!empty($levelWilayahV1)) {
            $query .= " and levelkota in ('" . implode("', '", $levelWilayahV1) . "')";
        }

        $query .= " group by idkota order by levelkota, idkota";

        return DB::connection('siakadv1')->select($query, $bindings);
    }

    /**
     * @param array|null $levelWilayah
     * @param bool $isInitSync
     * @param bool $isDeleteUnUsedDataUsingQueue
     * @return mixed[]
     */
    public function syncAllFromSiakadV1(
        array $levelWilayah = null,
        bool $isInitSync = false,
        bool $isDeleteUnUsedDataUsingQueue = false
    ) {
        if (empty($levelWilayah)) {
            $levelWilayah = [Wilayah::LEVEL_PROVINSI, Wilayah::LEVEL_KOTA_KABUPATEN, Wilayah::LEVEL_KECAMATAN];
        }

        return static::tryCall(function () use($levelWilayah, $isInitSync, $isDeleteUnUsedDataUsingQueue) {
            // get negara indonesia
            $idNegaraIdn = Wilayah::toBase()
                ->where('level_wilayah', Wilayah::LEVEL_NEGARA)
                ->where('kode_wilayah', 'IDN')->value('id');
            if (empty($idNegaraIdn)) {
                return [false, 'Negara Indonesia belum terdaftar, silakan sinkronisasi negara terlebih dahulu'];
            }

            // pengkondisi khusus level_wilayah, dikurangi 1
            $levelWilayahV1 = array_map(fn($level) => ($level - 1), $levelWilayah);

            // get data from v1
            $dataSiakad = $this->getDataWilayahNonNegaraFromV1($levelWilayahV1, $isInitSync);

            // sync provinsi, kota/kab, kecamatan from siakad v1 [ref.lv_kota] to siakad v2 [core.wilayah]
            $mappingV1ToV2 = [
                'idkota' => ['column' => 'kode_wilayah'],
                'namakota' => ['column' => 'nama_wilayah'],
                'levelkota' => ['column' => 'level_wilayah'],
                'iddikti' => ['column' => 'kode_dikti'], // yg parentkota null itu provinsi (default indonesia)
                't_updatetime' => ['column' => 'waktu_terakhir_sync', 'default' => now()], // Simpan waktu update
                'parentkota' => [
                    'column' => 'id_parent',
                    'default' => function($record) use ($idNegaraIdn) {
                        if (!empty($record->levelkota) && $record->levelkota == Wilayah::LEVEL_PROVINSI) {
                            return $idNegaraIdn;
                        }

                        return null;
                    },
                    'pkv1' => 'kode_wilayah',
                    'pkv2' => 'id'
                ],
            ];

            $pkV1 = ['idkota'];
            $refPkV2 = 'ref_key_siakad';
            [$isError, $errorMessage] = SyncSiakadV2::sync(
                modelV2: Wilayah::class,
                mappingColumns: $mappingV1ToV2,
                dataSiakadV1: $dataSiakad,
                primaryKeyV1: $pkV1,
                refKeyV2: $refPkV2,
                methodGetDataForDeleteAfterSync: 'Modules\Core\Services\WilayahManagementService::getDataNonNegaraForDeleteAfterSync',
                paramsGetDataForDeleteAfterSync: [$levelWilayah, $levelWilayahV1],
                isDeleteUsingQueue: $isDeleteUnUsedDataUsingQueue,
            );

            return [$isError, $errorMessage];
        });
    }

    public function syncNegaraFromSiakadV1(bool $isInitSync = false)
    {
        return static::tryCall(function () use ($isInitSync) {
            // get data dari siakad v1
            $lastSyncAt = Wilayah::where('level_wilayah', Wilayah::LEVEL_NEGARA)->max('waktu_terakhir_sync');
            $query = "select idnegara, namanegara, t_updatetime
                from ref.ms_negara
                where softdelete = '0'";

            $bindings = [];
            if (!empty($lastSyncAt) && empty($isInitSync)) {
                $lastSyncAt = date('Y-m-d H:i:s', strtotime($lastSyncAt));
                $query .= " and t_updatetime > ?";
                $bindings[] = $lastSyncAt;
            }

            $dataSiakad = DB::connection('siakadv1')->select($query, $bindings);

            // sync data from siakad v1 [ref.ms_negara] to siakad v2 [core.wilayah]
            $mappingV1ToV2 = [
                'idnegara' => ['column' => 'kode_wilayah'],
                'namanegara' => ['column' => 'nama_wilayah'],
                'level' => ['column' => 'level_wilayah', 'default' => Wilayah::LEVEL_NEGARA],
                't_updatetime' => ['column' => 'waktu_terakhir_sync', 'default' => now()], // Simpan waktu update
            ];

            [$isError, $errorMessage] = SyncSiakadV2::sync(
                modelV2: Wilayah::class,
                mappingColumns: $mappingV1ToV2,
                dataSiakadV1: $dataSiakad,
                primaryKeyV1: ['idnegara'],
                refKeyV2: 'ref_key_siakad',
                methodGetDataForDeleteAfterSync: 'Modules\Core\Services\WilayahManagementService::getDataNegaraForDeleteAfterSync',
            );
            

            return [$isError, $errorMessage];
        });
    }

    /**
     * @return array
     */
    public static function getDataNegaraForDeleteAfterSync()
    {
        return [
            'v2' => Wilayah::toBase()
                ->where('level_wilayah', Wilayah::LEVEL_NEGARA)
                ->select('ref_key_siakad')
                ->get(),
            'v1' => DB::connection('siakadv1')
                ->table('ref.ms_negara')
                ->select('idnegara')
                ->get(),
        ];
    }

    /**
     * @param array $levelWilayah
     * @param array $levelWilayahV1
     * @return array
     */
    public static function getDataNonNegaraForDeleteAfterSync(array $levelWilayah, array $levelWilayahV1)
    {
        return [
            'v2' => Wilayah::toBase()
                ->where('level_wilayah', '<>', Wilayah::LEVEL_NEGARA)
                ->whereIn('level_wilayah', $levelWilayah)
                ->select('ref_key_siakad')
                ->get(),
            'v1' => DB::connection('siakadv1')
                ->table('ref.lv_kota')
                ->whereIn('levelkota', $levelWilayahV1)
                ->select('idkota')
                ->get(),
        ];
    }

    public function syncRegion(array $levelCode = [])
    {
        $syncCount = array_keys($this->model::LEVELS);

        //if levelCode is empty, get all level
        if (empty($levelCode)) {
            $levelCode = $syncCount;
        }

        //init sync count
        $syncCount = array_fill_keys($syncCount, 0);
        $syncCount['total'] = 0;

        //init neofeeder
        $neofeeder = new Neofeeder();
        $neofeeder->setLimit(300);
        $neofeeder->setOrder('id_level_wilayah asc, id_wilayah asc');
        $neofeeder->setFilter("id_level_wilayah in ('" . implode("','", $levelCode) . "')");
        $response = $neofeeder->exec('GetWilayah');

        if ($response instanceof Error) {
            return $response;
        }

        //mapping data
        $regionArray = array_map(function ($region) {
            return [
                'kode_wilayah' => trim($region['id_wilayah']),
                'nama_wilayah' => $region['nama_wilayah'],
                'kode_dikti' => trim($region['id_wilayah']),
                'level_wilayah' => $region['id_level_wilayah'],
            ];
        }, $response['data']);

        //insert data
        try {
            foreach ($regionArray as $region) {
                $this->updateParentId($region);

                $this->model->updateOrCreate(
                    ['kode_wilayah' => $region['kode_wilayah']],
                    $region
                );
                $syncCount[$region['level_wilayah']]++;
                $syncCount['total']++;
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        return $syncCount;
    }

    private function updateParentId(&$region)
    {
        if ($region['level_wilayah'] == $this->model::LEVEL_PROVINSI) {
            $region['id_parent'] = substr($region['kode_wilayah'], 0, -6) . '000000';
        } elseif ($region['level_wilayah'] == $this->model::LEVEL_KOTA_KABUPATEN) {
            $region['id_parent'] = substr($region['kode_wilayah'], 0, -4) . '0000';
        } elseif ($region['level_wilayah'] == $this->model::LEVEL_KECAMATAN) {
            $region['id_parent'] = substr($region['kode_wilayah'], 0, -2) . '00';
        } else {
            $region['id_parent'] = null;
        }

        // Get id of id_parent from the database
        $parent = $this->model->where('kode_wilayah', $region['id_parent'])->first();

        if ($parent) {
            $region['id_parent'] = $parent->id;
        } else {
            $region['id_parent'] = null;
        }
    }
}
