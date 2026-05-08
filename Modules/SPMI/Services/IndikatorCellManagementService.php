<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\IndikatorCell;

class IndikatorCellManagementService
{
    /**
     * @var IndikatorCell
     */
    protected $model = IndikatorCell::class;

    protected $indicatorId;
    protected $cellCategory;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new IndikatorCell;
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
        $filter[] = ['field' => 'id_indikator_laporan_kinerja', 'value' => $this->indicatorId];
        $filter[] = ['field' => 'kategori_cell', 'value' => $this->cellCategory];

        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return IndikatorCell
     */
    public function show(int $id): IndikatorCell
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return IndikatorCell
     */
    public function store(array $data): IndikatorCell
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return IndikatorCell
     */
    public function update(array $data, int $id): IndikatorCell
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
     * Setter for indicatorId
     *
     * @param int $indicatorId
     * @return void
     */
    public function setIndicatorId(int $indicatorId): void
    {
        if (!filter_var($indicatorId, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $this->indicatorId = $indicatorId;
    }

    /**
     * Setter for cellCategory
     *
     * @param $cellCategory
     * @return void
     */
    public function setCellCategory($cellCategory): void
    {
        if (!key_exists($cellCategory, $this->model::CELL_CATEGORY)) {
            abort(404);
        }

        $this->cellCategory = $cellCategory;
    }
}
