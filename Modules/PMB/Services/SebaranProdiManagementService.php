<?php

namespace Modules\PMB\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\SistemKuliah;
use Modules\PMB\Models\Cache\SebaranProdiCache;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\SebaranAsalPendaftar;
use Modules\PMB\Models\SebaranPilihan;

class SebaranProdiManagementService
{
    /**
     * @var SebaranProdi
     */
    protected $model = SebaranProdi::class;

    protected int $registrationPeriodId;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SebaranProdi;
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
        $sql = "select pd.id, pd.id_unit_kerja, pd.daya_tampung, pd.nilai_minimal, pd.prefix_nim
                from " . $this->model->getTable() . " pd
                join pmb.periode_pendaftaran rp on rp.id = pd.id_periode_pendaftaran and rp.waktu_dihapus is null";

        $defaultFilter = "pd.waktu_dihapus is null
            and rp.id = " . $this->registrationPeriodId;

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
     * @return array
     */
    public function show(int $id): array
    {
        // get program distribution
        $programDistribution = $this->model->findOrFail($id);

        // get program option and program institution from relation
        $programOptionMappings = $programDistribution->programOptionMappings()->get();
        $programInstitutionMappings = $programDistribution->programInstitutionMappings()->get();

        // set program option and program institution mapping to program distribution
        foreach ($programOptionMappings as $programOptionMapping) {
            $programDistribution['program_option_' . $programOptionMapping->option] = true;
        }
        foreach ($programInstitutionMappings as $programInstitutionMapping) {
            $programDistribution['program_institution_' . $programInstitutionMapping->institution_type_id] = true;
        }

        return $programDistribution->toArray();
    }

    /**
     * Menampilkan list daftar periode pendaftaran berdasarkan id periode pendaftaran yang berasal dari cache.
     *
     * @param array $registrationPeriodIds
     * @return array
     * @old: getByPeriode() in spmb/models/m_sebaranprodi.php
     */
    public function getListByRegistrationPeriodIds(array $registrationPeriodIds): array
    {
        return SebaranProdiCache::getListByRegistrationPeriodIds($registrationPeriodIds);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SebaranProdi|Error
     */
    public function store(array $data): SebaranProdi|Error
    {
        DB::beginTransaction();

        // create pmb.program_distributions
        $model = $this->model->create($data);

        try {
            // save pmb.program_option_mappings
            $this->saveProgramOptionMapping($data, $model);

            // save pmb.program_institution_mappings
            $this->saveProgramInstitutionMapping($data, $model);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SebaranProdi|Error
     */
    public function update(array $data, int $id): SebaranProdi|Error
    {
        DB::beginTransaction();

        // update pmb.program_distributions
        $model = $this->model->findOrFail($id);
        $model->update($data);

        try {
            // save pmb.program_option_mappings
            $this->saveProgramOptionMapping($data, $model);

            // save pmb.program_institution_mappings
            $this->saveProgramInstitutionMapping($data, $this->model->findOrFail($id));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Proses menyimpan table pmb.program_option_mappings
     * dengan cara menghapus data yang lama lalu menyimpan data yang baru.
     *
     * @param array $data
     * @param SebaranProdi $programDistribution
     * @return void
     */
    private function saveProgramOptionMapping(array $data, SebaranProdi $programDistribution): void
    {
        $dataOptionMapping = [];
        foreach ($data as $key => $value) {
            // key harus berawalan 'program_option_' dan value true, jika tidak maka continue
            if (!str_starts_with($key, 'program_option_') || empty($value)) {
                continue;
            }

            $optionNumber = str_replace('program_option_', '', $key);
            $dataOptionMapping[] = [
                'id_sebaran_prodi' => $programDistribution->id,
                'pilihan' => $optionNumber,
            ];
        }

        if (empty($dataOptionMapping)) {
            return;
        }

        // delete old data first
        (new SebaranPilihan)->where('id_sebaran_prodi', $programDistribution->id)->delete();

        // create many new data using relationship
        $programDistribution->programOptionMappings()->createMany($dataOptionMapping);
    }

    /**
     * Proses menyimpan table pmb.program_institution_mappings
     * dengan cara menghapus data yang lama lalu menyimpan data yang baru.
     *
     * @param array $data
     * @param SebaranProdi $programDistribution
     * @return void
     */
    private function saveProgramInstitutionMapping(array $data, SebaranProdi $programDistribution): void
    {
        $dataProgramMapping = [];
        foreach ($data as $key => $value) {
            // key harus berawalan 'program_institution_' dan value true, jika tidak maka continue
            if (!str_starts_with($key, 'program_institution_') || empty($value)) {
                continue;
            }

            $institutionTypeId = str_replace('program_institution_', '', $key);
            $dataProgramMapping[] = [
                'id_sebaran_prodi' => $programDistribution->id,
                'id_jenis_institusi' => $institutionTypeId,
            ];
        }

        if (empty($dataProgramMapping)) {
            return;
        }

        // delete old data first
        (new SebaranAsalPendaftar)->where('id_sebaran_prodi', $programDistribution->id)->delete();

        // create many new data using relationship
        $programDistribution->programInstitutionMappings()->createMany($dataProgramMapping);
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
        DB::beginTransaction();

        $model = $this->model->findOrFail($id);

        // delete program option mapping
        $model->programOptionMappings()->delete();

        // delete program institution mapping
        $model->programInstitutionMappings()->delete();

        // delete program distribution
        $model->destroy($model->id);

        DB::commit();
    }

    /**
     * Setter untuk registration period id (periode pendaftaran).
     *
     * @param int $registrationPeriodId
     * @return void
     */
    public function setRegistrationPeriodId(int $registrationPeriodId): void
    {
        if (!filter_var($registrationPeriodId, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $this->registrationPeriodId = $registrationPeriodId;
    }

    /**
     * Dapetin jenjang, program studi, dan sistem kuliah
     *
     * @return array[]
     */
    public function getDegreeProgramDistributionAndLectureSystem()
    {
        $registrationPeriodService = new PeriodePendaftaranManagementService;
        // get active registration periods
        $activeRegistrationPeriods = $registrationPeriodService->getActiveRegistrationPeriod();

        // get list key and value (registration_period_id) from active registration periods
        $registrationPeriodIds = array_column($activeRegistrationPeriods, 'id_periode_pendaftaran', 'id_periode_pendaftaran');

        // get list program studi
        $programDistribution = $this->getListByRegistrationPeriodIds($registrationPeriodIds);

        // jenjang options
        $degrees = JenjangPendidikan::options();
        // sistem kuliah
        $lectureSystems = SistemKuliah::options();

        // looping utk dapetin option jenjang, program studi, dan sistem kuliah
        $degreeOpt = $programOpt = $lectureSystemOpt = [];
        foreach ($programDistribution as $val) {
            if (empty($degreeOpt[$val['id_jenjang_pendidikan']])) { // set jenjang
                $degreeOpt[$val['id_jenjang_pendidikan']] = $degrees[$val['id_jenjang_pendidikan']];
            }

            $program = [];
            $program[$val['id_unit_kerja']] = $val['kode_jenjang'] . ' - ' . $val['nama_unit'];
            // set program studi
            $programOpt[''][$val['id_unit_kerja']] = $program[$val['id_unit_kerja']];
            $programOpt[$val['kode_jenjang']][$val['id_unit_kerja']] = $program[$val['id_unit_kerja']];
        }

        // set sistem kuuliah
        foreach ($activeRegistrationPeriods as $val) {
            if (empty($lectureSystemOpt[$val['id_sistem_kuliah']])) {
                $lectureSystemOpt[$val['id_sistem_kuliah']] = $lectureSystems[$val['id_sistem_kuliah']];
            }
        }

        return [$degreeOpt, $programOpt, $lectureSystemOpt];
    }
}
