<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Illuminate\Support\Facades\DB;

use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\Evaluasi;
use Modules\Kerjasama\Models\JawabanPeserta;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Peserta;
use Str;

class PesertaManagementService
{
    /**
     * @var Kegiatan
     */
    protected $model = Peserta::class;
    protected $kerjasama = Kerjasama::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Peserta;
    }

    protected function defineIndexQuery(array $filter = [], array $order = []): array
    {
        $sql = "
        SELECT 
            p.id AS pertanyaan_id,
            p.pertanyaan,
            oj.id AS opsi_jawaban_id,
            oj.jawaban AS opsi_jawaban,
            COUNT(jp.id) AS total_jawaban,
            AVG(CASE WHEN jp.jawaban ~ '^[0-9.]+$' THEN jp.jawaban::float ELSE NULL END) AS rata_rata_jawaban
        FROM kerjasama.pertanyaan p
        LEFT JOIN kerjasama.opsi_jawaban oj ON oj.pertanyaan_id = p.id
        LEFT JOIN kerjasama.jawaban_peserta jp ON jp.pertanyaan_id = p.id AND jp.opsi_jawaban_id = oj.id
        WHERE p.waktu_dihapus IS NULL
        GROUP BY p.id, p.pertanyaan, oj.id, oj.jawaban
    ";

        $fieldMap = [
            'pertanyaan_id' => 'p.id',
            'pertanyaan' => 'p.pertanyaan',
            'opsi_jawaban_id' => 'oj.id',
            'opsi_jawaban' => 'oj.jawaban',
            'total_jawaban' => 'total_jawaban',
            'rata_rata_jawaban' => 'rata_rata_jawaban',
        ];

        $defaultFilter = "1=1";
        $defaultOrder = "p.id ASC";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            defaultOrder: $defaultOrder,
            fieldMap: $fieldMap,
        );

        return [$sql, $bindings];
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
        [$sql, $bindings] = $this->defineIndexQuery($filter, $order);

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Peserta
     */
    public function show(int $id): Peserta
    {
       return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Kegiatan
     */
    public function store(array $data): Peserta|Error
    {
        DB::beginTransaction();


        // Filter out fields not in fillable
        $data = collect($data)->only((new Peserta)->getFillable())->toArray();

        try {
            $model = $this->model->create($data);
        } catch (\Exception $e) {
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
     * @param int $id
     *
     * @return Kegiatan
     */
    public function update(array $data, int $id): Peserta|Error
    {
        DB::beginTransaction();
        $model = $this->model->findOrFail($id);

        $data = collect($data)->only($model->getFillable())->toArray();


        $model->update($data);

        DB::commit();
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
        try {
            DB::transaction(function () use ($ids) {
                $this->model->destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Export
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function export(array $order = [], array $filter = []): mixed
    {
        [$sql, $bindings] = $this->defineIndexQuery($filter, $order);

        return array_map(function ($value) {
            return (array) $value;
        }, DB::select($sql, $bindings) ?? []);
    }

}
