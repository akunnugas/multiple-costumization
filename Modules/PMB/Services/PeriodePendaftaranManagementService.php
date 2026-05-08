<?php

namespace Modules\PMB\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\PMB\Models\Cache\PeriodePendaftaranCache;
use Modules\PMB\Models\PeriodePendaftaran;

class PeriodePendaftaranManagementService
{
    /**
     * @var PeriodePendaftaran
     */
    protected $model = PeriodePendaftaran::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PeriodePendaftaran;
    }

    public function getRelationRawQuery()
    {
        // select sesuai kebutuhan
        $selectFromRegistrationPeriod = "rp.*";
        $selectFromOther = "p.nama_periode, b.nama_gelombang, rpa.nama_jalur,
            rt.nama_jenis_pendaftaran, ls.nama_sistem";

        // table registration period
        $table = $this->model->getTable();

        return "SELECT $selectFromRegistrationPeriod, $selectFromOther
            FROM $table rp
            JOIN core.periode_akademik p ON p.id = rp.id_periode_akademik AND p.waktu_dihapus IS NULL
            JOIN pmb.jalur_pendaftaran rpa ON rpa.id = rp.id_jalur_pendaftaran AND rpa.waktu_dihapus IS NULL
            JOIN pmb.gelombang b ON b.id = rp.id_gelombang AND b.waktu_dihapus IS NULL
            JOIN core.sistem_kuliah ls ON ls.id = rp.id_sistem_kuliah AND ls.waktu_dihapus IS NULL
            JOIN core.jenis_pendaftaran rt ON rt.id = rp.id_jenis_pendaftaran AND rt.waktu_dihapus IS NULL";
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
    public function index(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();

        $sql = "SELECT rp.kode_periode,
                    rp.id,
                    rp.nama_periode AS nama_periode_pendaftaran,
                    p.nama_periode,
                    b.nama_gelombang,
                    rpa.nama_jalur,
                    ls.nama_sistem,
                    rt.nama_jenis_pendaftaran,
                    rp.status_periode,
                    rp.apakah_berbayar
                FROM " . $table . " rp
                JOIN core.periode_akademik p ON p.id = rp.id_periode_akademik AND p.waktu_dihapus IS NULL
                JOIN pmb.gelombang b ON b.id = rp.id_gelombang AND b.waktu_dihapus IS NULL
                JOIN pmb.jalur_pendaftaran rpa ON rpa.id = rp.id_jalur_pendaftaran AND rpa.waktu_dihapus IS NULL
                JOIN core.sistem_kuliah ls ON ls.id = rp.id_sistem_kuliah AND ls.waktu_dihapus IS NULL
                JOIN core.jenis_pendaftaran rt ON rt.id = rp.id_jenis_pendaftaran AND rt.waktu_dihapus IS NULL
                ";

        $defaultFilter = "rp.waktu_dihapus IS NULL";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PeriodePendaftaran
     */
    public function show(int $id): Collection
    {
        $table = $this->model->getTable();
        $sql = "select * from $table where id = :id";
        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        $data = Collection::make($select[0]);

        return $data;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @param int $periodId
     * @param int $batchId
     * @param int $registrationPathId
     * @param int $lectureSystemId
     * @return mixed|Error
     */
    public function showByRegistrationCollection(
        int $id,
        int $periodId,
        int $batchId,
        int $registrationPathId,
        int $lectureSystemId
    ) {
        $sql = $this->getRelationRawQuery() . " WHERE rp.waktu_dihapus IS NULL
            AND rp.id = :id
            AND rp.id_periode_akademik = :period_id
            AND rp.id_gelombang = :batch_id
            AND rp.id_jalur_pendaftaran = :registration_path_id
            AND rp.id_sistem_kuliah = :lecture_system_id";

        try {
            $select = DB::select($sql, [
                'id' => $id,
                'id_periode_akademik' => $periodId,
                'id_gelombang' => $batchId,
                'id_jalur_pendaftaran' => $registrationPathId,
                'id_sistem_kuliah' => $lectureSystemId,
            ]);

            if (!isset($select[0])) {
                return new Error(message: 'Data tidak ditemukan');
            }
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        // return array
        return json_decode(json_encode($select[0]), true);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PeriodePendaftaran
     */
    public function store(array $data): PeriodePendaftaran
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PeriodePendaftaran
     */
    public function update(array $data, int $id): PeriodePendaftaran
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
     * Menampilkan opsi untuk kebutuhan filter.
     * @return array
     */
    public function options()
    {
        return $this->model->orderBy('nama_periode')->get(['id', 'nama_periode'])->pluck('nama_periode', 'id')->toArray();
    }

    /**
     * Dapetin data periode pendaftaran yang aktif.
     *
     * @return mixed
     * @old: getActive() in spmb/models/m_periodedaftar.php
     */
    public function getActiveRegistrationPeriod()
    {
        $activeRegistrationPeriod = PeriodePendaftaranCache::getActiveRegistrationPeriod();

        // TODO: masih ada kondisi tambahan mengecek tarif formulir (siakad lama)

        return $activeRegistrationPeriod;
    }
}
