<?php

namespace Modules\Litabmas\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Helpers\ManagementService;
use Modules\Litabmas\Models\AgendaKegiatan;

class AgendaKegiatanService
{
    /**
     * @var AgendaKegiatan
     */
    protected $model = AgendaKegiatan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AgendaKegiatan;
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
    public function index(int|null $page = null, int|null $perPage = 20, array $order = [], array $filter = []): mixed
    {
        $order = [
            'field' => 'urutan',
            'desc' => false
        ];

        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Get list data agenda kegiatan dari database (cached).
     *
     * @return Collection
     */
    public function getListCache()
    {
        return $this->model->getListCache();
    }
}
