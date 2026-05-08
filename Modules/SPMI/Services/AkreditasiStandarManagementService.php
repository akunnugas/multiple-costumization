<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\PenilaianMatriks;

class AkreditasiStandarManagementService
{
    /**
     * @var AkreditasiStandar
     */
    protected $model = AkreditasiStandar::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AkreditasiStandar;
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
     * @return AkreditasiStandar
     */
    public function show(int $id): AkreditasiStandar
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return AkreditasiStandar
     */
    public function store(array $data): AkreditasiStandar
    {
        $data['apakah_data_default'] = false;

        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return AkreditasiStandar
     */
    public function update(array $data, int $id): AkreditasiStandar
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
     * @return bool|Error
     */
    public function destroy(int $id): bool|Error
    {
        if ($this->checkReference($id)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        $model = $this->model->findOrFail($id);
        $model->destroy($model->id);

        return true;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        if ($this->checkReferenceMultiple($ids)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        DB::beginTransaction();

        foreach ($ids as $id) {
            $model = $this->model->findOrFail($id);

            if ($model->apakah_data_default) {
                DB::rollBack();
                return new Error('Data default tidak bisa dihapus.');
            }

            try {
                $model->delete();
            } catch (\Throwable $e) {
                DB::rollBack();
                return new Error('Gagal menghapus data');
            }
        }

        DB::commit();

        return true;
    }

    protected function checkReference(int $id): bool
    {
        $isReferencePenilaianMatriks = PenilaianMatriks::where('id_akreditasi_standar', $id)->exists();

        return $isReferencePenilaianMatriks;
    }

    protected function checkReferenceMultiple(array $ids): bool
    {
        $isReferencePenilaianMatriks = PenilaianMatriks::whereIn('id_akreditasi_standar', $ids)->exists();

        return $isReferencePenilaianMatriks;
    }
}
