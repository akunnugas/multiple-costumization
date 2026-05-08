<?php

namespace Modules\SPMI\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

class IndikatorBobotManagementService
{
    /**
     * @var IndikatorBobot
     */
    protected $model = IndikatorBobot::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new IndikatorBobot;
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
        $penilaianPanduanId = collect($filter)->firstWhere('field', 'id_penilaian_panduan')['value'] ?? null;

        $table = $this->model->getTable();
        $sql = "SELECT
                    ip.id,
                    ip.jenis_indikator_bobot,
                    ap.tahun_audit,
                    ip.nama_kategori_indikator,
                    ip.persentase
                FROM $table ip
                JOIN spmi.audit_periode ap ON ap.id = ip.id_audit_periode
                    AND ap.waktu_dihapus is null";

        // Defaultnya order berdasarkan id
        if (!empty($order) && $order['field'] == 'tahun_audit') {
            $order = ['field' => 'ip.id', 'direction' => 'asc', 'desc' => false];
        }

        $fieldMap = [
            'id' => 'ip.id',
            'type' => 'ip.jenis_indikator_bobot',
            'tahun_audit' => 'ap.tahun_audit',
            'persentase' => 'ip.persentase::TEXT',
        ];

        $defaultFilter = "ip.waktu_dihapus is null";
        if ($penilaianPanduanId == 'null_filter' || is_null($penilaianPanduanId)) {
            $defaultFilter .= " AND 1=0";
        }

        if ($penilaianPanduanId != 'null_filter' && !PenilaianPanduan::where('id', $penilaianPanduanId)->value('apakah_data_default')) {
            $defaultFilter .= " AND ip.jenis_indikator_bobot = '" . IndikatorBobot::TYPE_IKT . "'";
        }

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return IndikatorBobot
     */
    public function show(int $id): IndikatorBobot
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return IndikatorBobot
     */
    public function store(array $data): IndikatorBobot
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return IndikatorBobot
     */
    public function update(array $data, int $id): IndikatorBobot
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
     *  Setting persentase berdasarkan periode audit
     */
    public function setPercentages($data): Collection|Error
    {
        $ids = [];

        DB::beginTransaction();

        $percentage = 0;

        try {
            foreach ($data as $item) {
                $ids[] = $item['id'];
                $this->model->find($item['id'])
                    ->update([
                        'persentase' => $item['persentase']
                    ]);

                $percentage += $item['persentase'];
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        // Cek apakah total prosentase lebih dari 100%
        if ($percentage > 100) {
            DB::rollBack();
            return new Error('Gagal menyimpan, Total prosentase tidak boleh lebih dari 100%');
        }

        // Cek apakah total prosentase kurang dari 100%
        if ($percentage < 100) {
            DB::rollBack();
            return new Error('Gagal menyimpan, Total prosentase tidak boleh kurang dari 100%');
        }

        DB::commit();

        return $this->model->whereIn('id', $ids)->get();
    }
}
