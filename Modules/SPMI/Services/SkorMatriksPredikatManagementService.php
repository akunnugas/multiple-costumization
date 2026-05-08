<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\TargetSkor;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

class SkorMatriksPredikatManagementService extends Service
{
    /**
     * @var SkorMatriksPredikatPenilaian
     */
    protected $model = SkorMatriksPredikatPenilaian::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SkorMatriksPredikatPenilaian;
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
        $penilaianPanduanId = $this->parentResourceId;

        $sql = "SELECT* FROM spmi.skor_matriks_predikat_penilaian";

        $defaultFilter = "id_penilaian_panduan = ?";

        $bindings = [$penilaianPanduanId];

        if (isset($filter[0]['or'])) {
            $defaultFilter .= " AND (";
            $index = 0;
            foreach ($filter[0]['or'] as $key => $value) {
                $defaultFilter .=  ($index++ > 0 ? " OR " : "") . $value['field'] . "::text ILIKE  '%" . $value['value'] . "%'";
            }
            $defaultFilter .= ")";
            unset($filter[0]);
        }

        $order = "nilai DESC";

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
     * @return SkorMatriksPredikatPenilaian
     */
    public function show(int $id): SkorMatriksPredikatPenilaian
    {
        $data = $this->model->findOrFail($id);
        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SkorMatriksPredikatPenilaian| Error
     */
    public function store(array $data): SkorMatriksPredikatPenilaian| Error
    {
        $isExist = $this->model
            ->where('id_penilaian_panduan', $data['id_penilaian_panduan'])
            ->where('nilai', $data['nilai'])
            ->exists();
        if ($isExist) {
            return new Error('Skor dengan nilai tersebut sudah ada pada panduan penilaian ini.');
        }
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SkorMatriksPredikatPenilaian| Error
     */
    public function update(array $data, int $id): SkorMatriksPredikatPenilaian| Error
    {
        $model = $this->model->findOrFail($id);

        $isExist = $this->model
            ->where('id_penilaian_panduan', $data['id_penilaian_panduan'])
            ->where('nilai', $data['nilai'])
            ->where('id', '!=', $id)
            ->exists();
        if ($isExist) {
            return new Error('Skor dengan nilai tersebut sudah ada pada panduan penilaian ini.');
        }

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
        if (PenilaianMatriksPredikat::where('id_skor_matriks_predikat_penilaian', $model->id)->exists()) {
            return new Error('Butir skor tidak dapat dihapus karena sudah digunakan dalam penilaian target capaian.');
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
            return new Error('Butir skor tidak dapat dihapus karena sudah digunakan dalam penilaian target capaian.');
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }
}
