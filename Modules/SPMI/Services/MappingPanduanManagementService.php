<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\MappingPanduan;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\MappingLK;

class MappingPanduanManagementService extends Service
{
    /**
     * @var MappingPanduan
     */
    protected $model = MappingPanduan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new MappingPanduan;
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
        $sql = "SELECT
                    pp.id,
                    pp.nama_penilaian_panduan,
                    pp.kode_penilaian_panduan,
                    case when mp.id is not null then 1 else 0 end as is_mapping
                FROM spmi.penilaian_panduan pp
                LEFT JOIN spmi.mapping_panduan mp ON pp.id = mp.id_penilaian_panduan AND mp.id_pengisian_panduan = ?";

        $defaultFilter = "pp.apakah_aktif is true and pp.waktu_dihapus is null";

        // get filter pengisian panduan
        $idpengisianpanduan = $filter[0]['value'] ?? null;
        $bindings = [
            'id_pengisian_panduan' => $idpengisianpanduan,
        ];

        // unset filter id pengisian panduan
        unset($filter[0]);

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            bindings: $bindings,
            defaultFilter: $defaultFilter,
        );

        $temp = [];
        foreach ($bindings as $key => $value) {
            $temp[] = (int) $value;
        }
        $bindings = $temp;

        $temp = DB::select($sql, $bindings);
        $temp = array_map("unserialize", array_unique(array_map("serialize", $temp)));

        $data = [];
        foreach ($temp as $d) {
            $data[] = $d;
        }

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     */
    public function store(array $data)
    {
        $mappings = $data['mapping'] ?? [];

        DB::beginTransaction();

        try {
            $idPengisian = $data['id_pengisian_panduan'];

            $oldIds = MappingPanduan::where('id_pengisian_panduan', $idPengisian)
                ->pluck('id_penilaian_panduan')
                ->toArray();

            $newIds = collect($mappings)
                ->pluck('id')
                ->unique()
                ->values()
                ->toArray();

            $toDelete = array_values(array_diff($oldIds, $newIds)); // checked -> unchecked
            $toAdd = array_values(array_diff($newIds, $oldIds)); // unchecked -> checked (penambahan)

            if (!empty($toDelete)) {
                $usedExists = JadwalAuditUnit::join('spmi.jadwal_audit as ja', 'ja.id', '=', 'spmi.jadwal_audit_unit.id_jadwal_audit')
                    ->where('ja.waktu_dihapus', null)
                    ->where('spmi.jadwal_audit_unit.id_pengisian_panduan', $idPengisian)
                    ->whereIn('spmi.jadwal_audit_unit.id_penilaian_panduan', $toDelete)
                    ->exists();

                if ($usedExists) {
                    DB::rollBack();
                    return 'Tidak dapat mengubah data karena terdapat panduan penilaian yang sudah digunakan pada jadwal audit.';
                }
            }

            if (!empty($toDelete)) {
                MappingPanduan::where('id_pengisian_panduan', $idPengisian)
                    ->whereIn('id_penilaian_panduan', $toDelete)
                    ->delete();
            }

            if (!empty($toAdd)) {
                $rows = array_map(fn($idPenilaian) => [
                    'id_pengisian_panduan' => $idPengisian,
                    'id_penilaian_panduan' => $idPenilaian,
                ], $toAdd);

                MappingPanduan::insert($rows);
            }

            DB::commit();
            return 1;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            return 'Terjadi kesalahan pada server';
        }

        return 1;
    }
}
