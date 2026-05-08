<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\PeriodeAkademik;

class PeriodeAkademikManagementService
{
    /**
     * @var PeriodeAkademik
     */
    protected $model = PeriodeAkademik::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PeriodeAkademik;
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
    public function index($page = null, $perPage = null, $order = null, $filter = null)
    {
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PeriodeAkademik
     */
    public function show(int $id): PeriodeAkademik
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PeriodeAkademik
     */
    public function store(array $data): PeriodeAkademik
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PeriodeAkademik
     */
    public function update(array $data, int $id): PeriodeAkademik
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

    public function getActivePeriod()
    {
        return $this->model->where('apakah_aktif', true)->first()['id'] ?? 0;
    }
}
