<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\BroadcastPenerima;

class BroadcastPenerimaManagementService
{
    /**
     * @var BroadcastPenerima
     */
    protected $model = BroadcastPenerima::class;

    protected $broadcastId;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new BroadcastPenerima;
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
        // TODO: coalesce saat ini baru peserta saja silahkan diupdate sesuai kebutuhan (misal keluargamhs, mhs, dosen, dll)
        $sql = "select br.id,
                coalesce(r.code, '') as recipient_code,
                coalesce(p.nama, '') as recipient_name,
                case
                    when (r.id is not null) then '" . BroadcastPenerima::TYPE_PENDAFTAR . "'
                    else '" . BroadcastPenerima::TYPE_KELUARGA . "'
                end as recipient_type,
                case
                    when (br.id_registration_period is not null and br.id_registrant is null and z.all = z.is_email_sent and z.all <> 0)
                    then '1'
                    else br.apakah_email_terkirim
                end as is_email_sent,
                case
                    when (br.id_registration_period is not null and br.id_registrant is null and z.all = z.is_whatsapp_sent and z.all <> 0)
                    then '1'
                    else br.apakah_whatsapp_terkirim
                end as is_whatsapp_sent,
                case
                    when (br.id_registration_period is not null and br.id_registrant is null and z.all = z.is_sms_sent and z.all <> 0)
                    then '1'
                    else br.apakah_sms_terkirim
                end as is_sms_sent
            from " . $this->model->getTable() . " br
            join core.broadcast b on b.id = br.id_broadcast
            left join pmb.registrants r on r.id = br.registrant_id
            left join pmb.registration_periods rp on rp.id = br.registration_period_id
            left join core.biodata p on p.id = r.person_id
            left join (
                select id_registration_period,
                    count(1) as all,
                    count(1) filter (where apakah_email_terkirim = '1') as is_email_sent,
                    count(1) filter (where apakah_whatsapp_terkirim = '1') as is_whatsapp_sent,
                    count(1) filter (where apakah_sms_terkirim = '1') as is_sms_sent
                from core.broadcast_penerima
                where registration_period_id is not null and id_registrant is not null
                group by id_registration_period
            ) z on z.registration_period_id = br.id_registration_period and br.id_registrant is null";
        $defaultFilter = "b.id = " . $this->broadcastId;

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
     * @return BroadcastPenerima
     */
    public function show(int $id): BroadcastPenerima
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return BroadcastPenerima
     */
    public function store(array $data): BroadcastPenerima
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return BroadcastPenerima
     */
    public function update(array $data, int $id): BroadcastPenerima
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

    public function setBroadcastId($broadcastId)
    {
        $this->broadcastId = $broadcastId;
    }
}
