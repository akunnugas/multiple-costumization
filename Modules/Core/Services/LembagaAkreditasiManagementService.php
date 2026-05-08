<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\PengisianIndikator;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\TargetIndikator;

class LembagaAkreditasiManagementService
{
    /**
     * @var LembagaAkreditasi
     */
    protected $model = LembagaAkreditasi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new LembagaAkreditasi;
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
     * @return LembagaAkreditasi
     */
    public function show(int $id): LembagaAkreditasi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return LembagaAkreditasi
     */
    public function store(array $data): LembagaAkreditasi
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return LembagaAkreditasi
     */
    public function update(array $data, int $id): LembagaAkreditasi
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
    public function destroy(int $id): Error|bool
    {
        if ($this->checkReference($id)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
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
        if ($this->checkReferenceMultiple($ids)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    /**
     * List accreditation agencies
     * 
     * @return array
     */
    public function getListOption(): array
    {
        return $this->model->pluck('nama_lembaga', 'id')->toArray();
    }

    protected function checkReference($id)
    {
        // Pengecekan di alur audit
        $isReferenceUnitKerja = UnitKerja::where('id_lembaga_akreditasi', $id)->exists();
        $isReferenceTarget = TargetIndikator::where('id_lembaga_akreditasi', $id)->exists();
        $isReferencePengisianIndikator = PengisianIndikator::where('id_lembaga_akreditasi', $id)->exists();
        $isReferencePenilaianAudit = PenilaianAudit::where('id_lembaga_akreditasi', $id)->exists();

        return $isReferenceUnitKerja || $isReferenceTarget || $isReferencePengisianIndikator || $isReferencePenilaianAudit;
    }

    protected function checkReferenceMultiple($ids)
    {
        // Pengecekan di alur audit
        $isReferenceUnitKerja = UnitKerja::whereIn('id_lembaga_akreditasi', $ids)->exists();
        $isReferenceTarget = TargetIndikator::whereIn('id_lembaga_akreditasi', $ids)->exists();
        $isReferencePengisianIndikator = PengisianIndikator::whereIn('id_lembaga_akreditasi', $ids)->exists();
        $isReferencePenilaianAudit = PenilaianAudit::whereIn('id_lembaga_akreditasi', $ids)->exists();

        return $isReferenceUnitKerja || $isReferenceTarget || $isReferencePengisianIndikator || $isReferencePenilaianAudit;
    }
}
