<?php

namespace Modules\PMB\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\PMB\Models\SyaratPendaftaran;

class SyaratPendaftaranManagementService
{
    /**
     * @var SyaratPendaftaran
     */
    protected $model = SyaratPendaftaran::class;
    protected int $registrationPeriodId;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SyaratPendaftaran;
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
        $sql = "select rr.id, rr.id_periode_pendaftaran, rr.id_seleksi_syarat, rr.apakah_wajib, rr.apakah_upload,
                    rr.jumlah_dokumen
                from " . $this->model->getTable() . " rr
                join pmb.periode_pendaftaran rp on rp.id = rr.id_periode_pendaftaran and rp.waktu_dihapus is null
                join pmb.seleksi_syarat ar on ar.id = rr.id_seleksi_syarat and ar.waktu_dihapus is null";

        $defaultFilter = "rr.waktu_dihapus is null
            and rp.id = " . $this->registrationPeriodId;

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return SyaratPendaftaran
     */
    public function show(int $id): SyaratPendaftaran
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Menampilkan syarat pendaftaran berdasarkan jenis syarat.
     *
     * @param int $requirementTypeId
     * @param int|null $registrationPeriodId
     * @return array
     */
    public function getByRequirementType(int $requirementTypeId, int $registrationPeriodId = null)
    {
        $sql = "select rr.id, rr.id_periode_pendaftaran, rr.id_seleksi_syarat, rr.apakah_wajib, rr.apakah_upload,
                    rr.jumlah_dokumen, ar.nama_jenis_syarat
                from " . $this->model->getTable() . " rr
                join pmb.seleksi_syarat ar on ar.id = rr.id_seleksi_syarat and ar.waktu_dihapus is null
                where rr.id_jenis_syarat = :id_jenis_syarat
                    and rr.waktu_dihapus is null";

        $bindings = [
            'id_jenis_syarat' => $requirementTypeId,
        ];

        if ($registrationPeriodId) {
            $sql .= " and rr.id_periode_pendaftaran = :id_periode_pendaftaran";
            $bindings['id_periode_pendaftaran'] = $registrationPeriodId;
        }

        $resultQuery = DB::select($sql, $bindings);
        return json_decode(json_encode($resultQuery), true);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SyaratPendaftaran
     */
    public function store(array $data): SyaratPendaftaran
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SyaratPendaftaran
     */
    public function update(array $data, int $id): SyaratPendaftaran
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
     * Setter untuk registration period id (periode pendaftaran).
     *
     * @param int $registrationPeriodId
     * @return void
     */
    public function setRegistrationPeriodId(int $registrationPeriodId): void
    {
        if (!filter_var($registrationPeriodId, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $this->registrationPeriodId = $registrationPeriodId;
    }
}
