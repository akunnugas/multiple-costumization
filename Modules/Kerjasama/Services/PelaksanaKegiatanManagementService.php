<?php

namespace Modules\Kerjasama\Services;

use DB;
use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\PelaksanaKegiatan;

class PelaksanaKegiatanManagementService
{
    /**
     * @var PelaksanaKegiatan
     */
    protected $model = null;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PelaksanaKegiatan;
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
     * @return PelaksanaKegiatan
     */
    public function show(int $id): PelaksanaKegiatan
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PelaksanaKegiatan
     */
    public function store(array $data): PelaksanaKegiatan
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PelaksanaKegiatan
     */
    public function update(array $data, int $id): PelaksanaKegiatan | Error
    {
        try {
            $model = $this->model->findOrFail($id);
            $model->update($data);
        } catch (\Throwable $th) {
            return new Error('Gagal update pelaksana kegiatan');
        }

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
        return ManagementService::create($this->model)->destroySome($ids);
    }

    public function getByKegiatan($idKegiatan)
    {
        $data = DB::table($this->model->getTable() . ' AS kg')
            ->leftJoin('core.mahasiswa AS mhs', 'mhs.id', '=', 'kg.id_mahasiswa')
            ->where('id_kegiatan', $idKegiatan)
            ->whereNull('kg.waktu_dihapus')
            ->select([
                'kg.*',
                'mhs.id AS id_mahasiswa',
                'mhs.nim',
                'mhs.nama_mahasiswa'
            ])
            ->get()
            ->toArray();

        return array_map(function($item) {
            return (array) $item;
        }, $data);
    }

    public function destroyByKegiatan(string $idKegiatan, array $exceptIds = []): null|Error
    {
        try {
            $this->model->query()
                ->where('id_kegiatan', $idKegiatan)
                ->when(!empty($exceptIds), function ($query) use ($exceptIds) {
                    $query->whereNotIn('id', $exceptIds);
                })
                ->delete();
            return null;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
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