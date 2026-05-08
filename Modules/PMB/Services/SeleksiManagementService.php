<?php

namespace Modules\PMB\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Services\Service;
use Modules\PMB\Models\Seleksi;

class SeleksiManagementService extends Service
{
    /**
     * @var Seleksi
     */
    protected $model = Seleksi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Seleksi;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $registrationPeriodId = $this->parentResourceId;
        $sql = "select pa.id, pd.id_periode_pendaftaran, pa.id_sebaran_prodi, pa.urutan_seleksi,
                    pa.persentase_nilai, pa.waktu_mulai, pa.waktu_selesai
                from " . $this->model->getTable() . " pa
                join pmb.sebaran_prodi pd on pd.id = pa.id_sebaran_prodi and pd.waktu_dihapus is null
                join pmb.periode_pendaftaran rp on rp.id = pd.id_periode_pendaftaran and rp.waktu_dihapus is null";

        $defaultFilter = "pa.waktu_dihapus is null
            and rp.id = " . $registrationPeriodId;

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
     * @return Seleksi
     */
    public function show(int $id): Seleksi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get data by registration period id.
     *
     * @param $registrationPeriodId
     * @return array
     */
    public function getByRegistrationPeriodId($registrationPeriodId)
    {
        $sql = "select pa.id, pa.id_sebaran_prodi, pa.urutan_seleksi, pa.persentase_nilai,
                pa.waktu_mulai, pa.waktu_selesai, at.nama_jenis_seleksi
            from " . $this->model->getTable() . " pa
            join pmb.sebaran_prodi pd on pd.id = pa.id_sebaran_prodi and pd.waktu_dihapus is null
            join pmb.jenis_seleksi at on at.id = pa.id_jenis_seleksi and at.waktu_dihapus is null
            where pd.id_periode_pendaftaran = :id_periode_pendaftaran";

        $resultQuery = DB::select($sql, ['id_periode_pendaftaran' => $registrationPeriodId]);
        return json_decode(json_encode($resultQuery), true);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Seleksi
     */
    public function store(array $data): Seleksi
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Seleksi
     */
    public function update(array $data, int $id): Seleksi
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
}
