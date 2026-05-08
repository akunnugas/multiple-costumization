<?php

namespace Modules\Core\Services;

use DateTime;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;

class UnitKerjaManagementService
{
    /**
     * @var UnitKerja
     */
    protected $model = UnitKerja::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new UnitKerja;
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
    public function index(int | null $page = null, int | null $perPage = null, array | null $order = [], array | null $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "SELECT
                    o.id,
                    o.kode_unit,
                    CASE
                        WHEN o.jenis_unit = '" . UnitKerja::UNIT_NON_PRODI . "'
                        THEN o.nama_unit
                        ELSE CONCAT(COALESCE(d.kode_jenjang || ' - ', ''), o.nama_unit)
                    END AS nama_unit,
                    po.nama_unit as parent_unit,
                    o.jenis_unit,
                    o.apakah_akademik,
                    o.apakah_satker,
                    o.apakah_aktif,
                    o.apakah_data_default
                FROM $table o
                LEFT JOIN core.jenjang_pendidikan d on d.id = o.id_jenjang_pendidikan
                LEFT JOIN $table po on po.id = o.id_parent";

        // Defaultnya order berdasarkan info left
        if (!empty($order) && $order['field'] == 'id') {
            $order = ['field' => 'o.info_left', 'direction' => 'asc', 'desc' => false];
        }

        $fieldMap = [
            'id' => 'o.id',
            'nama_unit' => 'o.nama_unit',
            'kode_unit' => 'o.kode_unit',
            'parent_unit' => 'po.nama_unit',
            'jenis_unit' => 'o.jenis_unit',
            'apakah_akademik' => 'o.apakah_akademik',
            'apakah_satker' => 'o.apakah_satker',
        ];

        $defaultFilter = "o.waktu_dihapus is null and o.jenis_unit in ('" . UnitKerja::STUDY_PROGRAM . "', '" . UnitKerja::UNIT_NON_PRODI . "')";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function optionProdi($searchTerm = null, $limitTerm = 5,)
    {
        // Check if $limitTerm is empty or not set, and default to 5
        if (empty($limitTerm)) {
            $limitTerm = 5;
        }

        $result = DB::table('core.unit_kerja as uk')
            ->join('core.jenjang_pendidikan as jp', 'jp.id', '=', 'uk.id_jenjang_pendidikan')
            ->select('uk.id', 'jp.kode_jenjang', 'uk.nama_unit')
            ->where('uk.nama_unit', 'ILIKE', '%' . $searchTerm . '%')
            ->orWhere('jp.kode_jenjang', 'ILIKE', '%' . $searchTerm . '%')
            ->orderBy('jp.kode_jenjang', 'asc')
            ->orderBy('uk.nama_unit', 'asc')
            ->limit($limitTerm)
            ->get();

        $mappingProdi = [];
        foreach ($result as $row) {
            $mappingProdi[] = [
                'value' => $row->id,
                'label' => $row->kode_jenjang . ' - ' . $row->nama_unit,
            ];
        }
        return $mappingProdi;
    }


    /**
     * Menampilkan list data universitas
     *
     * @param int $page
     * @param int $perPage
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function indexUniversities(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $filter = $filter ?? [];
        return $this->index($page, $perPage, $order, [
            ['field' => 'jenis_unit', 'value' => UnitKerja::UNIVERSITY, 'operator' => '='],
        ]);
    }

    /**
     * Menampilkan list data fakultas
     *
     * @param int $page
     * @param int $perPage
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function indexFaculties(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $filter = $filter ?? [];
        return $this->index($page, $perPage, $order, [
            ['field' => 'jenis_unit', 'value' => UnitKerja::FACULTY, 'operator' => '='],
            ...$filter,
        ]);
    }

    /**
     * Menampilkan list data program studi
     *
     * @param int $page
     * @param int $perPage
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function indexStudyPrograms(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $filter = $filter ?? [];
        return $this->index($page, $perPage, $order, [
            ['field' => 'jenis_unit', 'value' => UnitKerja::STUDY_PROGRAM, 'operator' => '='],
            ...$filter,
        ]);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return UnitKerja
     */
    public function show(int $id): UnitKerja
    {
        $model = $this->model->findOrFail($id);

        if ($model->id_pimpinan) {
            $pegawai = Pegawai::find($model->id_pimpinan);
            $biodata = Biodata::find($pegawai->id_biodata);
            $model->pimpinan = $pegawai->nip . ' - ' . (($biodata->gelar_depan ? $biodata->gelar_depan : '') . $biodata->nama . ($biodata->gelar_belakang ? ', ' . $biodata->gelar_belakang : ''));
        }

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return UnitKerja
     */
    public function store(array $data): UnitKerja
    {
        // Jika ada jenis unit maka set is_academic = true
        if (isset($data['jenis_unit'])) {
            $data['apakah_akademik'] = true;
        } else {
            $data['apakah_akademik'] = false;
        }

        // Karena satuan kerja maka set is_satker = true
        $data['apakah_satker'] = true;

        return $this->model->create($data);
    }

    public function storeUnitNonProdi(array $data): UnitKerja
    {
        $optLembagaAkreditasi = LembagaAkreditasi::whereIn('kode_lembaga', array_keys(LembagaAkreditasi::LEMBAGA_AKREDITASI))
            ->pluck('id', 'kode_lembaga')
            ->toArray();

        if (empty($data['id_parent'])) {
            $univerity = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
            if ($univerity) {
                $data['id_parent'] = $univerity->id;
            }
        }

        $data['id_jenjang_pendidikan'] = JenjangPendidikan::where('kode_jenjang', 'UNA')->first()->id;
        $data['jenis_unit'] = UnitKerja::UNIT_NON_PRODI;
        $data['apakah_akademik'] = false;
        $data['id_lembaga_akreditasi'] = $optLembagaAkreditasi[LembagaAkreditasi::BANPT];
        $data['apakah_data_default'] = false;

        // Karena satuan kerja maka set is_satker = true
        $data['apakah_satker'] = true;

        $create = $this->model->create($data);

        return $create;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return UnitKerja
     */
    public function update(array $data, int $id): UnitKerja
    {
        // Jika ada jenis unit maka set is_academic = true
        if (isset($data['jenis_unit'])) {
            $data['apakah_akademik'] = true;
        } else {
            $data['apakah_akademik'] = false;
        }
        
        if(!empty($data['tanggal_berdiri'])){
            $tanggalBerdiri = DateTime::createFromFormat('d/m/Y', $data['tanggal_berdiri']);
            $data['tanggal_berdiri'] = $tanggalBerdiri->format('Y-m-d');
        }

        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id)
    {
        if ($this->checkReference($id)) {
            return new Error("Data tidak bisa dihapus karena sudah digunakan sebagai referensi.");
        }

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
        return ManagementService::create($this->model)->destroySome($ids);
    }

    public function getStudyProgramWithDegree($isActive = true, $isActivePmb = false)
    {
        // FIXME: on progress
        $table = $this->model->getTable();
        $sql = "select * from " . $table . " o
            left join core.jenjang_pendidikan d on d.id = o.id_jenjang_pendidikan
            where o.jenis_unit = '" . UnitKerja::STUDY_PROGRAM . "'";
        if ($isActive) {
            $sql .= " and o.apakah_aktif = true";
        }
        if ($isActivePmb) {
            $sql .= " and o.apakah_aktif_pmb = true";
        }
        $sql .= " order by d.urutan";

        $result = $this->model->getConnection()->select($sql);
        return $result = [];
    }

    public function getProgramsWithImage()
    {
        $table = $this->model->getTable();
        $sql = "SELECT o.*, d.slug FROM $table o
                LEFT JOIN dms.dokumen d ON d.id = o.id_foto_unit
                    AND d.waktu_dihapus IS NULL
                WHERE o.jenis_unit = ?";
        $result = DB::select($sql, [$this->model::STUDY_PROGRAM]);
        return $result;
    }

    public function showWithImage(int $id)
    {
        $table = $this->model->getTable();
        $sql = "SELECT o.*, d.slug FROM $table o
                LEFT JOIN dms.dokumen d ON d.id = o.id_foto_unit
                WHERE o.id = ?";
        $result = DB::select($sql, [$id]);

        return $result;
    }

    public function getUnitAncestors(int $startUnitId)
    {
        $tableName = (new UnitKerja)->getTable();
        $sql = "WITH RECURSIVE descendants AS (
                SELECT *
                FROM {$tableName}
                WHERE id_parent = ?

                UNION ALL

                SELECT u.*
                FROM {$tableName} u
                INNER JOIN descendants d ON u.id_parent = d.id
            )
            SELECT * FROM descendants;";
        $results = DB::select($sql, [$startUnitId]);
        $data = UnitKerja::hydrate($results);
        if ($data->isNotEmpty()) {
            $data->load('jenjang');
        }
        $units = $data->map(function ($item) {
            $jenjang = '';
            if (!empty($item->jenjang)) {
                $jenjang = $item->jenjang->kode_jenjang . ' - ';
            }

            return [
                'id' => $item->id,
                'nama_unit' => $jenjang . $item->nama_unit
            ];
        })->toArray();
        return array_column($units, 'nama_unit', 'id');
    }

    public function checkReference($id)
    {
        return false;
    }
}
