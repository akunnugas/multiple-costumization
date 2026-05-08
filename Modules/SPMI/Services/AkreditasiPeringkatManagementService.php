<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\PenilaianAudit;

class AkreditasiPeringkatManagementService extends Service
{
    /**
     * @var AkreditasiPeringkat
     */
    protected $model = AkreditasiPeringkat::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AkreditasiPeringkat;
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
        $PenilaianPanduanId = $this->parentResourceId;

        array_unshift($filter, ['field' => 'id_penilaian_panduan', 'value' => $PenilaianPanduanId, 'operator' => '=']);
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return AkreditasiPeringkat
     */
    public function show(int $id): AkreditasiPeringkat
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return AkreditasiPeringkat
     */
    public function store(array $data): AkreditasiPeringkat|Error
    {
        $checkUnique = AkreditasiPeringkat::where('kode_peringkat', $data['kode_peringkat'])->where('id_penilaian_panduan', $data['id_penilaian_panduan'])->exists();

        if ($checkUnique) {
            return new Error('Kode Peringkat sudah digunakan.');
        }

        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return AkreditasiPeringkat
     */
    public function update(array $data, int $id): AkreditasiPeringkat
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
    public function destroy(int $id)
    {
        $model = $this->model->findOrFail($id);

        $checkHasilAkhir = HasilAkhirAudit::where('id_akreditasi_peringkat', $model->id)->pluck('id_penilaian_audit');
        if ($checkHasilAkhir->count() > 0) {
            $cekTerunfinalisasi = PenilaianAudit::where('apakah_terfinalisasi', true)
                ->whereIn('id', $checkHasilAkhir)
                ->exists();
            if ($cekTerunfinalisasi) {
                return new Error('Data Peringkat Akreditasi tidak dapat dihapus karena sudah digunakan pada Hasil Akhir Audit.');
            }
        }

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
        $checkHasilAkhir = HasilAkhirAudit::whereIn('id_akreditasi_peringkat', $ids)->pluck('id_penilaian_audit');

        if ($checkHasilAkhir->count() > 0) {
            $cekTerfinalisasi = PenilaianAudit::where('apakah_terfinalisasi', true)
                ->whereIn('id', $checkHasilAkhir)
                ->exists();
            if ($cekTerfinalisasi) {
                return new Error('Beberapa data Peringkat Akreditasi tidak dapat dihapus karena sudah digunakan pada Hasil Akhir Audit.');
            }
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }
}
