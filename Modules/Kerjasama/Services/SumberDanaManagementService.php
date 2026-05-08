<?php

namespace Modules\Kerjasama\Services;

use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Kerjasama\Models\SumberDana;

class SumberDanaManagementService
{
    /**
     * @var SumberDana
     */
    protected $model = SumberDana::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SumberDana;
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
     * @return SumberDana
     */
    public function show(int $id): SumberDana
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SumberDana
     */
    public function store(array $data): SumberDana
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SumberDana
     */
    public function update(array $data, int $id): SumberDana|Error
    {

        [$isDataDefault, $errorMessage] = $this->isianDefaultValidation($id, 'mengubah');

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        // if ($this->checkReference($id)) {
        //     return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        // }

        [$isDataDefault, $errorMessage] = $this->isianDefaultValidation($id);

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gagal dihapus.');
        }

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
        // if ($this->checkReferenceMultiple($ids)) {
        //     return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        // }

        foreach ($ids as $id) {
            [$isDataDefault, $errorMessage] = $this->isianDefaultValidation($id);

            if ($isDataDefault) {
                return new Error($errorMessage);
            }
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    protected function isianDefaultValidation(int $id, string $actionLabel = "menghapus"): array
    {
        $data = $this->model
            ->where('id', $id)
            ->first();

        if (!$data->isian_default) {
            return [false, ""];
        }

        return [true, "Gagal $actionLabel Data '".$data->sumber_dana."' karena merupakan data default"];
    }

    // protected function checkReference($id)
    // {
    //     $isReferencePengisianPanduan = PengisianPanduan::where('id_jenis_standar', $id)->exists();
    //     $isReferencePenilaianPanduan = PenilaianPanduan::where('id_jenis_standar', $id)->exists();

    //     return $isReferencePengisianPanduan || $isReferencePenilaianPanduan;
    // }

    // protected function checkReferenceMultiple($ids)
    // {
    //     $isReferencePengisianPanduan = PengisianPanduan::whereIn('id_jenis_standar', $ids)->exists();
    //     $isReferencePenilaianPanduan = PenilaianPanduan::whereIn('id_jenis_standar', $ids)->exists();

    //     return $isReferencePengisianPanduan || $isReferencePenilaianPanduan;
    // }
}
