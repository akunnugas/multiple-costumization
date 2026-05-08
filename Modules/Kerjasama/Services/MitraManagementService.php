<?php

namespace Modules\Kerjasama\Services;

use DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Wilayah;
use Modules\Kerjasama\Enums\JenisMitra;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Mitra;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class MitraManagementService
{
    /**
     * @var Mitra
     */
    protected $model = Mitra::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Mitra;
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
        $table = $this->model->getTable();
        $sql = "SELECT 
                    m.*,
                    km.klasifikasi_mitra as id_kriteria_mitra
                FROM 
                    $table m
                JOIN kerjasama.kriteria_mitra km ON m.id_kriteria_mitra = km.id AND km.waktu_dihapus IS NULL";

        $fieldMap = [
            'id_kriteria_mitra' => 'km.id',
        ];

        $defaultFilter = "m.waktu_dihapus IS NULL";
        $defaultOrder = "m.waktu_dibuat desc";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            defaultOrder: $defaultOrder,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     * biar ga error di route model binding kalau id nya string
     * @param int $id
     *
     * @return Mitra|null 
     */
    public function show($id)
    {
        return $this->model->with('kontak')->findOrFail($id);
    }

    public function getTingkatMitraById(int|string $id): string
    {
        return $this->model
            ->query()
            ->where('id', $id)
            ->get('tingkat_mitra')
            ->first()
            ?->tingkat_mitra ?? '';
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Mitra
     */
    public function store(array $data): Mitra
    {

        if (!empty($data['id_provinsi'])) {
            $idNegara = Wilayah::getIdParentWilayahById(Wilayah::LEVEL_NEGARA, $data['id_provinsi']);
            $data['id_negara'] = $idNegara;
        }

        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Mitra
     */
    public function update(array $data, int $id): Mitra
    {
        $model = $this->model->findOrFail($id);

        if(!empty($data['id_provinsi'])) {
            $idNegara = Wilayah::getIdParentWilayahById(Wilayah::LEVEL_NEGARA, $data['id_provinsi']);
            $data['id_negara'] = $idNegara;
        }

        $model->update($data);

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        if ($this->checkReference($id)) {
            return new Error([
                'Oops.. Terjadi Kesalahan',
                'Data tidak bisa dihapus karena sudah digunakan sebagai referensi kerjasama.', 
                'double'
            ]);
        }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
        }

        return true;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        if ($this->checkReference($ids)) {
            return new Error([
                'Oops.. Terjadi Kesalahan', 
                'Data tidak bisa dihapus karena sudah digunakan sebagai referensi kerjasama.', 
                'double'
            ]);
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    /**
     * @param array $order
     * @param array $filter
     * 
     * @return BinaryFileResponse
     */
    public function export(array $order = [], array $filter = []): array
    {
        $table = $this->model->getTable();
        $sql = "SELECT 
                    m.*,
                    km.klasifikasi_mitra AS id_kriteria_mitra,
                    w_negara.nama_wilayah AS id_negara,
                    w_provinsi.nama_wilayah AS id_provinsi,
                    w_kota.nama_wilayah AS id_kota,
                    w_kecamatan.nama_wilayah AS id_kecamatan
                FROM 
                    $table m
                JOIN kerjasama.kriteria_mitra km 
                    ON m.id_kriteria_mitra = km.id 
                    AND km.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah w_negara 
                    ON m.id_negara = w_negara.id 
                    AND w_negara.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah w_provinsi 
                    ON m.id_provinsi = w_provinsi.id 
                    AND w_provinsi.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah w_kota 
                    ON m.id_kota = w_kota.id 
                    AND w_kota.waktu_dihapus IS NULL   
                LEFT JOIN core.wilayah w_kecamatan 
                    ON m.id_kecamatan = w_kecamatan.id 
                    AND w_kecamatan.waktu_dihapus IS NULL      
                ";

        $fieldMap = [
            'id_kriteria_mitra' => 'km.klasifikasi_mitra',
        ];

        $defaultFilter = "m.waktu_dihapus IS NULL";
        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            defaultFilter: $defaultFilter,
            filter: $filter,
            fieldMap: $fieldMap,
        );

        $data = array_map(function ($value) {
            return (array) $value;
        }, DB::select($sql, $bindings) ?? []);

        foreach ($data as $key => $item) {
            foreach ($item as $field => $value) {
                if ($field == 'tingkat_mitra') {
                    $data[$key][$field] = Mitra::LEVELS[$value];
                }

                if ($field == 'jenis_mitra') {
                    $data[$key][$field] = JenisMitra::getOptions()[$value];
                }
            }
        }

        return $data;
    }

    public function isExists($id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    protected function checkReference(mixed $ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        return Kerjasama::whereIn('id_mitra', $ids)->exists();
    }

    public function getUnitKerjaOptions()
    {
        $unitKerja = DB::table('core.unit_kerja as uk')
            ->leftJoin('core.jenjang_pendidikan as jp', 'jp.id', '=', 'uk.id_jenjang_pendidikan')
            ->select('uk.id', 'jp.kode_jenjang', 'uk.nama_unit', 'uk.info_left')
            ->orderBy('uk.info_left', 'asc')
            ->get();

        $unitKerjaOptions = [];
        foreach ($unitKerja as $item) {
            $unitKerjaOptions[$item->id] = !empty($item->kode_jenjang) ? $item->kode_jenjang . ' - ' . $item->nama_unit : $item->nama_unit;
        }

        return $unitKerjaOptions;
    }
}
