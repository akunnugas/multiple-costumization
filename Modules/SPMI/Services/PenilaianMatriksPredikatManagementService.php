<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\TargetSkor;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

class PenilaianMatriksPredikatManagementService extends Service
{
    /**
     * @var PenilaianMatriksPredikat
     */
    protected $model = PenilaianMatriksPredikat::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianMatriksPredikat;
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
        $PenilaianMatriksId = $this->parentResourceId;

        $sql = "SELECT
                    pmp.*, smpp.id AS id_skor_matriks_predikat_penilaian
                FROM
                    spmi.penilaian_matriks_predikat AS pmp
                JOIN spmi.skor_matriks_predikat_penilaian AS smpp
                    ON pmp.id_skor_matriks_predikat_penilaian = smpp.id";

        $defaultFilter = "pmp.id_penilaian_matriks = ? and pmp.waktu_dihapus IS NULL";

        $bindings = [$PenilaianMatriksId];

        $order = "smpp.nilai DESC";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            bindings: $bindings,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PenilaianMatriksPredikat
     */
    public function show(int $id): PenilaianMatriksPredikat
    {
        $data = $this->model->findOrFail($id);
        $skorMatriksPenilaian = SkorMatriksPredikatPenilaian::find($data->id_skor_matriks_predikat_penilaian);
        $data->id_skor_matriks_predikat_penilaian = $skorMatriksPenilaian->id;
        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PenilaianMatriksPredikat
     */
    public function store(array $data): PenilaianMatriksPredikat
    {
        $data['apakah_nonaktif'] = $data['apakah_nonaktif'] == 1 ? '0' : '1';
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PenilaianMatriksPredikat
     */
    public function update(array $data, int $id): PenilaianMatriksPredikat
    {
        $data['apakah_nonaktif'] = $data['apakah_nonaktif'] == 1 ? '0' : '1';
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

        // check if exists scores
        if (TargetSkor::where('id_predikat_matriks_penilaian', $model->id)->exists()) {
            return new Error('Butir skor sudah memiliki penilaian pada target capaian, tidak dapat dihapus.');
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
        // check if exists scores
        if (TargetSkor::whereIn('id_predikat_matriks_penilaian', $ids)->exists()) {
            return new Error('Beberapa butir skor sudah memiliki penilaian pada target capaian, tidak dapat dihapus.');
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }
}
