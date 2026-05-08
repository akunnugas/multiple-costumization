<?php

namespace Modules\PMB\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\Konten;

class KontenManagementService
{
    /**
     * @var Konten
     */
    protected $model = Konten::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Konten;
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
    public function index(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Konten
     */
    public function show(int $id): Konten
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Konten
     */
    public function store(array $data): Konten
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Konten
     */
    public function update(array $data, int $id): Konten
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
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }

    public function getAlumniContents()
    {
        $table = $this->model->getTable();
        $sql = "SELECT ac.*, d.slug FROM $table ac
                LEFT JOIN dms.dokumen d ON d.id = ac.id_file_gambar and d.waktu_dihapus is null
                WHERE ac.jenis_konten = 'alumni'";
        $result = DB::select($sql);
        return $result;
    }
    public function getFacilityContents()
    {
        $table = $this->model->getTable();
        $sql = "SELECT ac.*, d.slug FROM $table ac
                LEFT JOIN dms.dokumen d ON d.id = ac.id_file_gambar and d.waktu_dihapus is null
                WHERE ac.jenis_konten = 'fasilitas'";
        $result = DB::select($sql);
        return $result;
    }
}
