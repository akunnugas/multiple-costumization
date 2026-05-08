<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\PengisianPanduan;

class AkreditasiBukuManagementService
{
    /**
     * @var AkreditasiBuku
     */
    protected $model = AkreditasiBuku::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AkreditasiBuku;
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
     * @return AkreditasiBuku
     */
    public function show(int $id): AkreditasiBuku
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return AkreditasiBuku
     */
    public function store(array $data): AkreditasiBuku
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
     * @return AkreditasiBuku
     */
    public function update(array $data, int $id): AkreditasiBuku
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
     * Get List Book Type
     *
     * @return array
     */
    public function getListTypeBook(): array
    {
        return $this->model::TYPE;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        DB::beginTransaction();

        foreach ($ids as $id) {
            $model = $this->model->findOrFail($id);

            if ($model->apakah_data_default) {
                DB::rollBack();
                return new Error('Data default tidak bisa dihapus.');
            }

            if (PengisianPanduan::where('id_akreditasi_buku', $id)->exists()) {
                DB::rollBack();
                return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
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
}
