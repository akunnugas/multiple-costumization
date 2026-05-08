<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Broadcast;

class BroadcastManagementService
{
    /**
     * @var Broadcast
     */
    protected $model = Broadcast::class;

    /**
     * @var string
     */
    protected $moduleType;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Broadcast;
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
        $sql = "select b.*,
                case
                    when b.apakah_terkirim = false then 'Belum Dikirim'
                    when br.not_sent > 0 then 'Dalam Proses'
                    else 'Selesai'
                end as status
                from " . $this->model->getTable() . " b
                left join (
                    select br.id_broadcast, count(br.id_broadcast) as not_sent
                    from core.broadcast_penerima br
                    join core.broadcasts b on b.id = br.id_broadcast
                    where ((br.apakah_email_terkirim = false and b.apakah_email = true)
                            or (br.apakah_sms_terkirim = false and b.apakah_sms = true)
                            or (br.apakah_whatsapp_terkirim = false and b.apakah_whatsapp = true))
                        and ((br.id_registrant is not null and br.id_registration_period is null)
                            or (br.id_registrant is not null and br.id_registration_period is not null)
                            or (br.id_registrant is null and br.id_registration_period is null))
                    group by br.id_broadcast
                ) br on br.id_broadcast = b.id";

        $defaultFilter = "b.waktu_dihapus is null";
        if (!empty($this->moduleType)) {
            $defaultFilter .= " and b.jenis_modul = '" . $this->moduleType . "'";
        }

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
     * @return Broadcast
     */
    public function show(int $id): Broadcast
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Broadcast
     */
    public function store(array $data): Broadcast
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Broadcast
     */
    public function update(array $data, int $id): Broadcast
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

    public function setModuleType($moduleType)
    {
        $this->moduleType = $moduleType;
    }
}
