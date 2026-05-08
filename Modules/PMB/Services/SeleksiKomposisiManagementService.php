<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\PMB\Models\SeleksiKomposisi;

class SeleksiKomposisiManagementService
{
    /**
     * @var SeleksiKomposisi
     */
    protected $model = SeleksiKomposisi::class;

    protected int $registrationPeriodId;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SeleksiKomposisi;
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
        $sql = "select pc.id, pc.id_seleksi_jenis, pc.id_seleksi_komponen, pc.persentase_komposisi
                from " . $this->model->getTable() . " pc
                join pmb.registration_periods rp on rp.id = pc.id_periode_pendaftaran and rp.waktu_dihapus is null";

        $defaultFilter = "pc.waktu_dihapus is null
            and rp.id = " . $this->registrationPeriodId;

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
     * @return SeleksiKomposisi
     */
    public function show(int $id): SeleksiKomposisi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SeleksiKomposisi
     */
    public function store(array $data): SeleksiKomposisi
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SeleksiKomposisi
     */
    public function update(array $data, int $id): SeleksiKomposisi
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
    public function setRegistrationPeriodId($registrationPeriodId): void
    {
        if (!filter_var($registrationPeriodId, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $this->registrationPeriodId = $registrationPeriodId;
    }
}
