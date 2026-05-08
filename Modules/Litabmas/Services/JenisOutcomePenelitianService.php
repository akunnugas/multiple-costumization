<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\JenisPublikasi;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;

class JenisOutcomePenelitianService
{
    /**
     * @var JenisOutcomePenelitian
     */
    protected $model = JenisOutcomePenelitian::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JenisOutcomePenelitian;
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
        $table = $this->model->getTable();

        $sql = "select ot.id, ot.nama_outcome, ot.kategori_outcome, ot.batas_pengumpulan_outcome, ot.id_jenis_publikasi,
                so.nama_jenis_publikasi
            from " . $table . " ot
            left join litabmas.jenis_publikasi so on so.id = ot.id_jenis_publikasi AND so.waktu_dihapus is null
            ";

        $defaultFilter = "ot.waktu_dihapus is null";

        $fieldMap = [
            'nama_outcome' => 'ot.nama_outcome',
            'kategori_outcome' => 'ot.kategori_outcome',
            'batas_pengumpulan_outcome' => 'ot.batas_pengumpulan_outcome',
            'id_jenis_publikasi' => 'ot.id_jenis_publikasi',
            'nama_jenis_publikasi' => 'so.nama_jenis_publikasi',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Get list data output dari database (cache).
     *
     * @return Collection
     */
    public function getListCache()
    {
        return $this->model->getListCache();
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return JenisOutcomePenelitian|Error
     */
    public function show(int $id): JenisOutcomePenelitian|Error
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return JenisOutcomePenelitian|Error
     */
    public function store(array $data): JenisOutcomePenelitian|Error
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return JenisOutcomePenelitian|Error
     */
    public function update(array $data, int $id): JenisOutcomePenelitian|Error
    {
        try {
            $model = $this->model->findOrFail($id);
            $fieldsToCheck = [
                'batas_pengumpulan_outcome',
            ];

            $hasChanged = Cstr::isArrayDifferent($model->toArray(), $data, $fieldsToCheck);
            
            if ($hasChanged) {
                //check relation
                $relationsForCheck = ['klasterPendanaan'];
                // Check if data has relation
                $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
                // If data has relation, return error
                if (!empty($hasRelations['status'])) {
                    $relation = __('litabmas::jenis_outcome_penelitian.' . $hasRelations['relation']);
                    return new Error("Outcome tidak bisa diubah karena sudah memiliki Data $relation.", Error::SQLSTATE_VIOLATION_FK);
                }

            }
            
            $model->update($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        try {
            $model = $this->model->findOrFail($id);
            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Sinkronisasi data jenis publikasi dari modul Kepegawaian aplikasi SIAKAD V1
     */
    public function jenisPublikasiFromHRSiakadV1()
    {
        $siakadV1Connection = DB::connection('siakadv1');
        $sql = "select idjenispublikasi, jenispublikasi, softdelete from hr.ms_jenispublikasi";

        try {
            $result = $siakadV1Connection->select($sql);
            $jenisPublikasi = json_decode(json_encode($result), true);
        } catch (Exception $e) {
            return [true, $e->getMessage()];
        }

        // mapping column
        $pkJenisPublikasi = ['idjenispublikasi'];
        $mapping = [
            'jenispublikasi' => ['column' => 'nama_jenis_publikasi'],
        ];

        list($err, $msg) = SyncSiakad::sync($mapping, $jenisPublikasi, new JenisPublikasi, $pkJenisPublikasi);

        return [$err, $msg];
    }
}
