<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\AkreditasiStatus;
use Modules\Core\Services\Service;

class AkreditasiStatusManagementService extends Service
{
    /**
     * @var AkreditasiStatus
     */
    protected $model = AkreditasiStatus::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AkreditasiStatus;
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
        $PenilaianPanduanId = $this->parentResourceId;

        array_unshift($filter, ['field' => 'id_penilaian_panduan', 'value' => $PenilaianPanduanId, 'operator' => '=']);
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return AkreditasiStatus
     */
    public function show(int $id): AkreditasiStatus
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return AkreditasiStatus
     */
    public function store(array $data): AkreditasiStatus|Error
    {
        $checkUnique = AkreditasiStatus::where('kode_Status', $data['kode_Status'])->where('id_penilaian_panduan', $data['id_penilaian_panduan'])->exists();

        if ($checkUnique) {
            return new Error('Kode Status sudah digunakan.');
        }

        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return AkreditasiStatus
     */
    public function update(array $data, int $id): AkreditasiStatus
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }
}
