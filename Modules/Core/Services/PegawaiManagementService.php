<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;

class PegawaiManagementService
{
    /**
     * @var Pegawai
     */
    protected $model = Pegawai::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Pegawai;
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

        $sql = "SELECT DISTINCT
            e.id,
            e.nip,
            coalesce(p.gelar_depan || ' ', '') || trim(p.nama) || coalesce(', ' || p.gelar_belakang, '') nama,
            o.nama_unit,
            od.nama_unit AS homebase,
            e.akun_sidik_jari
        FROM $table e
        LEFT JOIN core.biodata p ON p.id = e.id_biodata
        LEFT JOIN core.unit_kerja o ON o.id = e.id_unit_kerja
        LEFT JOIN core.unit_kerja od ON od.id = e.id_homebase_dosen";

        $fieldMap = [
            'nama_unit' => 'o.nama_unit',
            'homebase' => 'od.nama_unit',
        ];

        $defaultFilter = "e.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
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
    public function show(int $id): Collection|Error
    {
        $sql = "SELECT p.*, b.*
                FROM core.pegawai p
                LEFT JOIN core.biodata b ON b.id = p.id_biodata
                WHERE p.id = :id";

        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            return new Error('Data tidak ditemukan.', 404);
        }

        $data = Collection::make($select[0]);

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Pegawai
     */
    public function store(array $data): Pegawai
    {
        DB::beginTransaction();

        $person = Biodata::create($data);
        $model = $this->model->create($data + ['id_biodata' => $person->id]);

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Pegawai
     */
    public function update(array $data, int $id): Pegawai
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        // FIXME: save biodata
        $biodata = Biodata::findOrFail($model->id_biodata);
        //...

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

    public function setUserIdPegawai($idPegawai, $user = null)
    {
        $pegawai = Biodata::where('ref_key_pegawai', $idPegawai)->first();
        if (!empty($pegawai)) {
            $pegawai->id_user = $user->id;
            $pegawai->save();
        }
    }
}
