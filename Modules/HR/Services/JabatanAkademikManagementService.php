<?php

namespace Modules\HR\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SyncSiakad;
use Modules\HR\Models\JabatanAkademik;

class JabatanAkademikManagementService
{
    /**
     * @var JabatanAkademik
     */
    protected $model = JabatanAkademik::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JabatanAkademik;
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

        $sql = "SELECT
                    ap.id,
                    ap.kode_jabatan_akademik,
                    ap.nama_jabatan_akademik,
                    CASE ap.jenis_jabatan_akademik
                        WHEN " . $this->model::TENAGA_KEPENDIDIKAN . " THEN '" . $this->model::TYPES[$this->model::TENAGA_KEPENDIDIKAN] . "'
                        WHEN " . $this->model::DOSEN_AKADEMIK . " THEN '" . $this->model::TYPES[$this->model::DOSEN_AKADEMIK] . "'
                        WHEN " . $this->model::DOSEN_PRAKTISI_INDUSTRI . " THEN '" . $this->model::TYPES[$this->model::DOSEN_PRAKTISI_INDUSTRI] . "'
                    END jenis_jabatan_akademik
                FROM $table ap";

        $defaultFilter = "ap.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return JabatanAkademik
     */
    public function show(int $id): JabatanAkademik
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JabatanAkademik
     */
    public function store(array $data): JabatanAkademik
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JabatanAkademik
     */
    public function update(array $data, int $id): JabatanAkademik
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

    /**
     * Sync data from SIAKAD V1 to SIAKAD V2
     *
     * return array
     */
    public function syncJabatanAkademikFromSiakadV1()
    {
        $siakadV1Connection = DB::connection('siakadv1');
        $sql = "select 
            idfungsional,
            fungsional,
            isakademik,
            kodeemis 
        from hr.ms_fungsional order by idfungsional";

        try {
            $result = $siakadV1Connection->select($sql);
            $dataSiakad = json_decode(json_encode($result), true);
        } catch (Exception $e) {
            return [true, $e->getMessage()];
        }

        $mapping = [];
        $mapping['idfungsional'] = ['column' => 'kode_jabatan_akademik', 'notnull' => true];
        $mapping['fungsional'] = ['column' => 'nama_jabatan_akademik'];
        $mapping['isakademik'] = ['column' => 'jenis_jabatan_akademik', 'default' => JabatanAkademik::TENAGA_KEPENDIDIKAN];
        $mapping['kodeemis'] = ['column' => 'kode_emis'];

        $pk = ['idfungsional'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, $this->model, $pk);

        return [$err, $msg];
    }
}
