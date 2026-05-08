<?php

namespace Modules\PMB\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\PMB\Models\JalurPendaftaran;

class JalurPendaftaranManagementService
{
    /**
     * @var JalurPendaftaran
     */
    protected $model = JalurPendaftaran::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JalurPendaftaran;
    }

    /**
     * Menampilkan list data
     *
     * @param int $limit
     * @param int $page
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "select rp.id, rp.nama_jalur, rp.nama_jalur as text, rp.keterangan_jalur
                from " . $this->model->getTable() . " rp";
        $defaultFilter = "rp.waktu_dihapus is null";

        $fieldMap = ['nama_jalur' => 'rp.nama_jalur', 'keterangan_jalur' => 'rp.keterangan_jalur'];
        [$sql, $bindings] = Pagination::buildQuery(
            $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return JalurPendaftaran
     */
    public function show(int $id): JalurPendaftaran
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JalurPendaftaran
     */
    public function store(array $data): JalurPendaftaran
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JalurPendaftaran
     */
    public function update(array $data, int $id): JalurPendaftaran
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
        try {
            DB::transaction(function () use ($ids) {
                JalurPendaftaran::destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }
}
