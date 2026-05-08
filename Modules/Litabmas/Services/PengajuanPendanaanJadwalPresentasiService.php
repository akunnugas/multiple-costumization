<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;

class PengajuanPendanaanJadwalPresentasiService
{
    /**
     * @var PengajuanPendanaanJadwalPresentasi
     */
    protected $model = PengajuanPendanaanJadwalPresentasi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanJadwalPresentasi;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @param int $idPengajuanPendanaan
     * @return PengajuanPendanaanJadwalPresentasi|Error
     */
    public function store(array $data, int $idPengajuanPendanaan): PengajuanPendanaanJadwalPresentasi|Error
    {
        // custom validate
        $data = $this->customValidateStoreUpdate($data, $idPengajuanPendanaan);
        if (Error::isError($data)) {
            return $data;
        }

        DB::beginTransaction();

        try {
            $model = $this->model->create($data);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $idPengajuanPendanaan
     * @param int $idPengajuanPendanaanJadwalPresentasi
     * @return PengajuanPendanaanJadwalPresentasi|Error
     */
    public function update(array $data, int $idPengajuanPendanaan, int $idPengajuanPendanaanJadwalPresentasi): PengajuanPendanaanJadwalPresentasi|Error
    {
        // validation
        $data = $this->customValidateStoreUpdate($data, $idPengajuanPendanaan);
        if (Error::isError($data)) {
            return $data;
        }

        // cek jadwal presentasi
        $model = $this->model->find($idPengajuanPendanaanJadwalPresentasi);
        if (empty($model)) {
            return new Error('Data jadwal presentasi tidak ditemukan.', 404);
        }

        DB::beginTransaction();

        try {
            $model->update($data);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $idPengajuanPendanaan
     * @param int $idPengajuanPendanaanJadwalPresentasi
     * @return Error|null
     */
    public function destroy(int $idPengajuanPendanaanJadwalPresentasi): Error|null
    {
        $model = $this->model->find($idPengajuanPendanaanJadwalPresentasi);

        if (empty($model)) {
            return new Error('Data jadwal presentasi tidak ditemukan.', 404);
        }

        DB::beginTransaction();

        try {
            $model->delete();
        } catch (Exception $e) {
            DB::rollBack();

            return new Error(exception: $e);
        }

        DB::commit();

        return null;
    }

    /**
     * Validasi custom untuk store dan update.
     *
     * @param array $data
     * @param int $idPengajuanPendanaan
     * @return array|Error
     */
    private function customValidateStoreUpdate(array $data, int $idPengajuanPendanaan)
    {
        //

        return $data;
    }
}
