<?php

namespace Modules\Kerjasama\Services;

use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Kerjasama\Models\KriteriaMitra;

class KriteriaMitraManagementService
{
    /**
     * @var KriteriaMitra
     */
    protected $model = KriteriaMitra::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new KriteriaMitra;
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
     * @return KriteriaMitra
     */
    public function show(int $id): KriteriaMitra
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return KriteriaMitra
     */
    public function store(array $data): KriteriaMitra
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return KriteriaMitra
     */
    public function update(array $data, int $id): KriteriaMitra|Error
    {
        [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id, 'mengubah');

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
        [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id);

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
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
        foreach ($ids as $id) {
            [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id);

            if ($isDataDefault) {
                return new Error($errorMessage);
            }
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    protected function isianDefaultVaidation(int $id, string $actionLabel = "menghapus"): array
    {
        $data = $this->model
            ->where('id', $id)
            ->first();

        if (!$data->isian_default) {
            return [false, ""];
        }

        return [true, "Gagal $actionLabel Data '" . $data->klasifikasi_mitra . "' karena merupakan data default"];
    }

    public function firstOrCreate(string $jenisDokumen): KriteriaMitra
    {
        $normalizedName = strtolower(trim($jenisDokumen));
        $existing = $this->model->whereRaw('LOWER(TRIM(klasifikasi_mitra)) = ?', [$normalizedName])->first();

        if ($existing) {
            return $existing;
        }

        if (empty($jenisDokumen)) {
            return $this->model->firstOrCreate(
                ['klasifikasi_mitra' => 'Umum'],
                [
                    'keterangan' => 'Kriteria default',
                    'isian_default' => true,
                    'waktu_dibuat' => now(),
                    'dibuat_oleh' => auth()->id(),
                ]
            );
        }

        return $this->model->create([
            'klasifikasi_mitra' => trim($jenisDokumen),
            'keterangan' => null,
            'isian_default' => false,
            'waktu_dibuat' => now(),
            'dibuat_oleh' => auth()->id(),
        ]);
    }
}
