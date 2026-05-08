<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Keluarga;
use Modules\PMB\Models\Pendaftar;

class KeluargaManagementService
{
    /**
     * @var Keluarga
     */
    protected $model = Keluarga::class;

    protected int $personId;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Keluarga;
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
    public function index(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "SELECT
            f.*,
            fs.nama_status_keluarga as family_status,
            d.nama_jenjang as degree
            FROM " . $this->model->getTable() . " f
            LEFT JOIN core.status_hubungan_keluarga fs on fs.id = f.id_status_hubungan_keluarga
            LEFT JOIN core.jenjang_pendidikan d on d.id = f.id_jenjang_pendidikan
            ";

        $defaultFilter = "
            f.waktu_dihapus IS NULL
            AND f.id_biodata = " . $this->personId;

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Collection
     */
    public function show(int $id): Collection
    {
        $sql = "SELECT
                f.*,
                fs.nama_status_keluarga as family_status,
                d.nama_jenjang as degree,
                j.nama_pekerjaan as job,
                s.nama_penghasilan as salary
                FROM " . $this->model->getTable() . " f
                LEFT JOIN core.status_hubungan_keluarga fs on fs.id = f.id_status_hubungan_keluarga AND fs.waktu_dihapus IS NULL
                LEFT JOIN core.jenjang_pendidikan d on d.id = f.id_jenjang_pendidikan AND d.waktu_dihapus IS NULL
                LEFT JOIN core.pekerjaan j ON j.id = f.id_pekerjaan AND j.waktu_dihapus IS NULL
                LEFT JOIN core.penghasilan s ON s.id = f.id_penghasilan AND s.waktu_dihapus IS NULL
                WHERE f.id = :id AND f.id_biodata = :person_id
                ";

        $select = DB::select($sql, ['id' => $id, 'id_biodata' => $this->personId]);
        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        $data = Collection::make($select[0]);

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Keluarga
     */
    public function store(array $data): Keluarga
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Keluarga
     */
    public function update(array $data, int $id): Keluarga
    {
        $model = $this->model->where(['id' => $id, 'id_biodata' => $this->personId])->firstOrFail();

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
        $model = $this->model->where(['id' => $id, 'id_biodata' => $this->personId])->firstOrFail();

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
     * Setter untuk person id (Data Personal).
     *
     * @param int $registrantId
     * @return void
     */
    public function setPersonId(int $registrantId): void
    {
        if (!filter_var($registrantId, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $personId = Pendaftar::where('id', $registrantId)->first()->id_biodata ?? 0;
        $this->personId = $personId;
    }
}
