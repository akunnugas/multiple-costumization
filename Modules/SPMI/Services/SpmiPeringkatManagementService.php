<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\SpmiPeringkat;

class SpmiPeringkatManagementService
{
    /**
     * @var SpmiPeringkat
     */
    protected $model = SpmiPeringkat::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SpmiPeringkat;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter, fieldMap: [
            'id_audit_periode' => 'sp.id_audit_periode::TEXT',
        ], alias: 'sp');
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return SpmiPeringkat
     */
    public function show(int $id): SpmiPeringkat
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SpmiPeringkat|Error
     */
    public function store(array $data): SpmiPeringkat|Error
    {
        try {
            $min = (float) $data['skor_minimal'];
            $max = (float) $data['skor_maksimal'];

            if ($min >= $max) {
                return new Error('Skor minimal harus lebih kecil dari skor maksimal.');
            }

            return $this->model->create($data);
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SpmiPeringkat|Error
     */
    public function update(array $data, int $id): SpmiPeringkat|Error
    {
        $model = $this->model->findOrFail($id);

        try {
            $min = (float) $data['skor_minimal'];
            $max = (float) $data['skor_maksimal'];

            if ($min >= $max) {
                return new Error('Skor minimal harus lebih kecil dari skor maksimal.');
            }

            $model->update($data);

            return $model;
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }
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

        return ManagementService::create($this->model)->destroySome($ids);
    }

    protected function checkReference(int $id): bool
    {
        $isReferenceHasilAkhirAudit = HasilAkhirAudit::where('id_spmi_peringkat', $id)->exists();

        return $isReferenceHasilAkhirAudit;
    }

    protected function checkReferenceMultiple(array $ids): bool
    {
        $isReferenceHasilAkhirAudit = HasilAkhirAudit::whereIn('id_spmi_peringkat', $ids)->exists();

        return $isReferenceHasilAkhirAudit;
    }
}
