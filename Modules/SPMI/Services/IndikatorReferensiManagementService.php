<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\IndikatorReferensi;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Helpers\Pagination;

class IndikatorReferensiManagementService
{
    /**
     * @var IndikatorReferensi
     */
    protected $model = IndikatorReferensi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new IndikatorReferensi;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return IndikatorReferensi
     */
    public function show(int $id): IndikatorReferensi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return IndikatorReferensi|Error
     */
    public function store(array $data): IndikatorReferensi|Error
    {
        // cek unique data
        $isUnique = $this->model->where('id_indikator_laporan_kinerja', $data['id_indikator_laporan_kinerja'])
            ->where('id_indikator_evaluasi_diri', $data['id_indikator_evaluasi_diri'])
            ->where('id_pengisian_panduan', $data['id_pengisian_panduan'])
            ->first();

        if ($isUnique) {
            return new Error('Data sudah ada');
        }

        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return IndikatorReferensi|Error
     */
    public function update(array $data, int $id): IndikatorReferensi|Error
    {
        $model = $this->model->findOrFail($id);

        // cek unique data
        $isUnique = $this->model->where('id_indikator_laporan_kinerja', $data['id_indikator_laporan_kinerja'])
            ->where('id_indikator_evaluasi_diri', $model->id_indikator_evaluasi_diri)
            ->where('id_pengisian_panduan', $model->id_pengisian_panduan)
            ->where('id', '!=', $id)
            ->first();

        if ($isUnique) {
            return new Error('Data sudah ada');
        }


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
}
