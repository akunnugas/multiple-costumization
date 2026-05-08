<?php

namespace Modules\SPMI\Services;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class SuratTugasAuditorManagementService
{
    /**
     * @var SuratTugasAuditor
     */
    protected $model = SuratTugasAuditor::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SuratTugasAuditor;
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
                    st.id,
                    ap.tahun_audit AS periode_audit,
                    st.nomor_surat_tugas,
                    st.tanggal_surat_tugas,
                    st.tanggal_mulai,
                    st.tanggal_selesai,
                    d.nama_dokumen as nama_dokumen_surat_tugas,
                    d.extension_versi_terbaru as ekstensi_dokumen_surat_tugas
                FROM $table st
                JOIN spmi.audit_periode ap ON ap.id = st.id_audit_periode
                    AND ap.waktu_dihapus is null
                LEFT JOIN dms.dokumen d ON d.id = st.id_dokumen
                    AND d.waktu_dihapus is null";

        $fieldMap = [
            'periode_audit' => 'ap.tahun_audit::text',
            'nama_dokumen_surat_tugas' => "CONCAT(d.nama_dokumen, '.', d.extension_versi_terbaru)",
            'tanggal_surat_tugas_range' => "CONCAT(st.tanggal_mulai::text, ' - ', st.tanggal_selesai::text)",
        ];

        $defaultFilter = "st.waktu_dihapus is null";

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
     * @return SuratTugasAuditor
     */
    public function show(int $id): SuratTugasAuditor
    {
        $data = $this->model->findOrFail($id);

        // get auditor
        $data->auditor = implode('::::', SuratTugasAuditorPegawai::where('id_surat_tugas_auditor', $id)
        ->whereHas('unit', function($query) {
            $query->whereNull('waktu_dihapus');
        })->with(['biodata', 'unit'])->get()->map(function ($item) {
            $biodata = $item->biodata;
            $unit = $item->unit;
            $nipPegawai = Pegawai::where('id_biodata', $item->id_personil)->value('nip');
            $output = '';
            if ($unit->id_jenjang_pendidikan) {
                $jenjang = JenjangPendidikan::find($unit->id_jenjang_pendidikan);
                $output .= ($jenjang ? ($jenjang->kode_jenjang . ' ') : '');
            }
            $output .= $unit->nama_unit . ' - ' . ($biodata->gelar_depan ? $biodata->gelar_depan . '. ' : '') . $biodata->nama . ($biodata->gelar_belakang ? ', ' . $biodata->gelar_belakang : '');
            $output .= !empty($nipPegawai) ? ' - ' . $nipPegawai : '';
            $output .= ' || (' . SuratTugasAuditorPegawai::POSITIONS[$item->posisi] . ')';
            return $output;
        })->toArray());

       // sort pegawai
        $temp = explode('::::', $data->auditor);

        // Custom sort function
        usort($temp, function ($a, $b) {
            // extract
            $partsA = explode(' - ', $a);
            $partsB = explode(' - ', $b);

            // sort unit and education level
            $unitA = $partsA[0];
            $unitB = $partsB[0];
            if ($unitA != $unitB) {
                return strcmp($unitA, $unitB);
            }

            $positionA = explode(' || (', $a)[1];
            $positionA = str_replace(')', '', $positionA);
            $positionB = explode(' || (', $b)[1];
            $positionB = str_replace(')', '', $positionB);

            // define order
            $positionOrder = ['Ketua Auditee' => 0, 'Anggota Auditee' => 1, 'Ketua Auditor' => 2, 'Anggota Auditor' => 3];
            if ($positionOrder[$positionA] != $positionOrder[$positionB]) {
                return $positionOrder[$positionA] - $positionOrder[$positionB];
            }

            // compare
            $nameA = $partsA[1];
            $nameB = $partsB[1];
            return strcmp($nameA, $nameB);
        });

        foreach ($temp as $key => $value) {
            $temp[$key] = str_replace(' || (', ' (', $value);
        }

        $data->auditor = implode('::::', $temp);

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SuratTugasAuditor|Error
     */
    public function store(array $data): SuratTugasAuditor|Error
    {
        $isExist = SuratTugasAuditor::where('id_audit_periode', $data['id_audit_periode'])
            ->where('waktu_dihapus', null)
            ->exists();

        if ($isExist) {
            $selectedPeriod = AuditPeriode::find($data['id_audit_periode']);
            return new Error('Surat Tugas Audit AMI untuk periode audit ' . $selectedPeriod->tahun_audit . ' sudah ada.', 400);
        }

        DB::beginTransaction();
        $file = $data['id_dokumen'] ?? null;
        $fileName = explode('.', $file?->getClientOriginalName())[0];

        // Cek range tanggal apakah sudah ada di database
        $isDateRangeExist = $this->model->where('tanggal_mulai', $data['tanggal_mulai'])->exists();

        // Jika ada range tanggal yang sama, maka return error
        if ($isDateRangeExist) {
            return new Error('Tanggal yang dipilih sudah digunakan.', 400);
        }

        $upload = new UploadDokumen();
        $upload = $upload->upload(
            file: $file,
            name: $fileName,
            folderCode: UploadDokumen::SPMI_SURAT_TUGAS,
            moduleCode: Modul::CODE_SPMI,
            note: null,
            withTransaction: false
        );

        if (Error::isError($upload->getError())) {
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()?->id;

        try {
            $model = $this->model->create($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Execute upload
        $upload->executeUpload();

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $savePersons = $this->savePersons($data['surat_tugas_auditor_pegawai'] ?? [], $model->id);
        if (Error::isError($savePersons)) {
            DB::rollBack();
            return $savePersons;
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
     * @return SuratTugasAuditor|Error
     */
    public function update(array $data, int $id): SuratTugasAuditor|Error
    {
        $isExist = SuratTugasAuditor::where('id_audit_periode', $data['id_audit_periode'])
            ->where('waktu_dihapus', null)
            ->whereNot('id', $id)
            ->exists();

        if ($isExist) {
            $selectedPeriod = AuditPeriode::find($data['id_audit_periode']);
            return new Error('Surat Tugas Audit AMI untuk periode audit ' . $selectedPeriod->tahun_audit . ' sudah ada.', 400);
        }

        DB::beginTransaction();

        $model = $this->model->findOrFail($id);

        $file = $data['id_dokumen'] ?? null;
        $fileName = explode('.', $file?->getClientOriginalName())[0];

        // Cek range tanggal apakah sudah ada di database
        $isDateRangeExist = $this->model
            ->where('tanggal_mulai', $data['tanggal_mulai'])
            ->whereNot('id', $id)
            ->exists();

        // Jika ada range tanggal yang sama, maka return error
        if ($isDateRangeExist) {
            return new Error('Tanggal yang dipilih sudah digunakan.', 400);
        }

        $upload = new UploadDokumen();
        if (isset($model->id_dokumen)) {
            $upload = $upload->update(
                data: [
                    'file' => $file,
                    'name' => $fileName,
                    'note' => null,
                ],
                id: $model->id_dokumen,
                withTransaction: false,
                isReplace: true
            );
        } else {
            $upload = $upload->upload(
                file: $file,
                name: $fileName,
                folderCode: UploadDokumen::SPMI_SURAT_TUGAS,
                moduleCode: Modul::CODE_SPMI,
                note: null,
                withTransaction: false,
            );
        }

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()?->id ?? $model->id_dokumen;

        try {
            $model->update($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Execute upload
        $upload->executeUpload();

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $savePersons = $this->savePersons($data['surat_tugas_auditor_pegawai'] ?? [], $model->id);
        if (Error::isError($savePersons)) {
            DB::rollBack();
            return $savePersons;
        }

        DB::commit();

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

        $isPenilaian = PenilaianAudit::where('id_audit_periode', $model->id_audit_periode)->exists();

        if ($isPenilaian) {
            return new Error('Penghapusan data Surat Tugas gagal, data masih dijadikan referensi');
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
        foreach ($ids as $id) {
            $model = $this->model->findOrFail($id);

            $isPenilaian = PenilaianAudit::where('id_audit_periode', $model->id_audit_periode)->exists();

            if ($isPenilaian) {
                return new Error('Penghapusan data Surat Tugas gagal, data masih dijadikan referensi');
            }
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    public function showAvailablePersonBySK(int|null $periodId = null)
    {
        $sql = "SELECT
                    p.id,
                    e.nip,
                    p.nama,
                    p.gelar_depan,
                    p.gelar_belakang,
                    o.id as leader_study_program_id
                FROM core.biodata p
                JOIN core.pegawai e ON e.ref_key_siakad = p.ref_key_pegawai
                JOIN spmi.sk_auditor_pegawai skp ON skp.id_personil = p.id
                JOIN spmi.sk_auditor ska ON ska.id = skp.id_sk_auditor
                LEFT JOIN core.unit_kerja o ON o.id_pimpinan = p.id
                WHERE ska.id_audit_periode = ?";

        $data = DB::select($sql, [$periodId]);

        return (array) $data;
    }

    public function showAvailablePerson()
    {
        $sql = "SELECT
                    p.id,
                    e.nip,
                    p.nama,
                    p.gelar_depan,
                    p.gelar_belakang,
                    o.id as leader_study_program_id
                FROM core.biodata p
                JOIN core.pegawai e ON e.ref_key_siakad = p.ref_key_pegawai
                JOIN hr.employee_statuses s ON s.id = e.id_status_pegawai
                    AND s.waktu_dihapus IS NULL
                LEFT JOIN core.unit_kerja o ON o.id_pimpinan = p.id
                WHERE p.waktu_dihapus IS NULL AND e.waktu_dihapus IS NULL
                AND s.is_active = true";

        $data = DB::select($sql);

        return (array) $data;
    }

    public function showPersons(int $id)
    {
        $sql = "SELECT
                    p.id,
                    stp.id_personil,
                    stp.id_unit,
                    stp.posisi,
                    e.nip,
                    p.nama person_name,
                    p.gelar_depan,
                    p.gelar_belakang,
                    CONCAT(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil
                    AND p.waktu_dihapus IS NULL
                JOIN core.pegawai e ON e.ref_key_siakad = p.ref_key_pegawai
                    AND e.waktu_dihapus IS NULL
                JOIN hr.employee_statuses s ON s.id = e.id_status_pegawai
                    AND s.waktu_dihapus IS NULL
                JOIN core.unit_kerja o ON o.id = stp.id_unit
                    AND o.waktu_dihapus IS NULL
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                    AND d.waktu_dihapus IS NULL
                WHERE stp.id_surat_tugas_auditor = ?
                    AND s.is_active = true
                    AND stp.waktu_dihapus IS NULL";

        $data = DB::select($sql, [$id]);

        return array_map(function ($item) {
            return (array) $item;
        }, $data ?? []);
    }

    public function checkRegisteredStudyProgram($studyProgramId, $periodId, $stAuditId = null)
    {
        if (empty($studyProgramId)) {
            return false;
        }

        $isExist = SuratTugasAuditorPegawai::whereHas('stAuditor', function ($query) use ($periodId) {
            $query->where('id_audit_periode', $periodId);
        })->where('id_unit', $studyProgramId)
            ->where('id_surat_tugas_auditor', '!=', $stAuditId)
            ->exists();

        return $isExist;
    }

    public function getPosisiAuditor($idPerson, $idPeriodeAudit, $idUnit){
        $suratTugasAuditorTable = (new SuratTugasAuditor)->getTable();
        $result = SuratTugasAuditorPegawai::select('surat_tugas_auditor_pegawai.id', 'surat_tugas_auditor_pegawai.posisi')
            ->where('id_personil', $idPerson)
            ->where('id_unit', $idUnit)
            ->join($suratTugasAuditorTable, function (JoinClause $join) use ($suratTugasAuditorTable, $idPeriodeAudit) {
                $join->on("$suratTugasAuditorTable.id", '=', 'surat_tugas_auditor_pegawai.id_surat_tugas_auditor')
                        ->where("$suratTugasAuditorTable.id_audit_periode", '=', $idPeriodeAudit);
            })
            ->value('surat_tugas_auditor_pegawai.posisi');
        return $result;
    }

    private function savePersons($data, $id): int|Error
    {
        // Validasi harus ada minimal 1 personil
        if (empty($data)) {
            return new Error('Harap tambahkan minimal 1 orang personil auditor.', 400);
        }

        $data = array_map(function ($item) {
            return [
                'id_personil' => $item['person_id'],
                'id_unit' => $item['id_unit'],
                'posisi' => $item['position'],
            ];
        }, $data);

        $totalSaved = 0;
        $studyProgramIds = [];

        $totalStudyProgramLeader = 0;
        $totalStudyProgramAuditeeLeader = 0;
        foreach ($data as $item) {
            if ($item['posisi'] == SuratTugasAuditorPegawai::POSITION_LEAD) {
                $totalStudyProgramLeader++;
            }

            if ($item['posisi'] == SuratTugasAuditorPegawai::POSITION_AUDITEE) {
                $totalStudyProgramAuditeeLeader++;
            }

            $studyProgramIds[] = $item['id_unit'] ?? null;
        }

        $totalAllStudyPrograms = count(array_unique($studyProgramIds));

        // Cek apakah jumlah ketua auditor di setiap program studi, jika kurang dari jumlah program studi, maka return error
        if ($totalStudyProgramLeader < $totalAllStudyPrograms) {
            return new Error('Pastikan setiap program studi memiliki Ketua Auditor.', 400);
        }

        // Cek apakah jumlah ketua auditor di setiap program studi, jika lebih dari jumlah program studi, maka return error
        if ($totalStudyProgramLeader > $totalAllStudyPrograms) {
            return new Error('Pastikan setiap program studi hanya memiliki 1 Ketua Auditor.', 400);
        }

        // Cek apakah jumlah ketua auditee di setiap program studi, jika kurang dari jumlah program studi, maka return error
        if ($totalStudyProgramAuditeeLeader < $totalAllStudyPrograms) {
            return new Error('Pastikan setiap program studi memiliki Ketua Auditee.', 400);
        }

        // Cek apakah jumlah ketua auditee di setiap program studi, jika lebih dari jumlah program studi, maka return error
        if ($totalStudyProgramAuditeeLeader > $totalAllStudyPrograms) {
            return new Error('Pastikan setiap program studi hanya memiliki 1 Ketua Auditee.', 400);
        }

        // Ambil semua data personil yang sudah ada
        $allCurrentData = SuratTugasAuditorPegawai::where('id_surat_tugas_auditor', $id)
            ->get(['id_personil', 'id_unit', 'posisi'])
            ->toArray();

        $toStoreData = [];
        $toUpdateData = [];

        // Memisahkan data yang akan diupdate dan disimpan
        foreach ($data as $person) {
            $personId = (int)$person['id_personil'];
            $studyProgramId = (int)$person['id_unit'];

            $isUpdatePersonData = false;

            $isUpdatePersonData = collect($allCurrentData)
                ->where('id_unit', $studyProgramId)
                ->where('id_personil', $personId)
                ->isNotEmpty();

            if ($isUpdatePersonData) {
                $toUpdateData[] = $person;
            } else {
                $toStoreData[] = $person;
            }
        }

        // Memisahkan data yang akan dihapus
        $toDeleteData = collect($allCurrentData)
            ->reject(function ($currentPerson) use ($toUpdateData, $toStoreData) {
                return collect($toUpdateData)
                    ->merge($toStoreData)
                    ->contains(function ($item) use ($currentPerson) {
                        return $item['id_personil'] == $currentPerson['id_personil'] &&
                            $item['id_unit'] == $currentPerson['id_unit'];
                    });
            })
            ->toArray();
        $toDeleteData = array_values($toDeleteData);

        // Eksekusi query update data
        try {
            foreach ($toUpdateData as $person) {
                SuratTugasAuditorPegawai::where('id_surat_tugas_auditor', $id)
                    ->where('id_personil', (int)$person['id_personil'])
                    ->where('id_unit', (int)$person['id_unit'])
                    ->update([
                        'posisi' => $person['posisi']
                    ]);

                $totalSaved++;
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        // Eksekusi query tambah data
        try {
            foreach ($toStoreData as $person) {
                SuratTugasAuditorPegawai::create([
                    'id_surat_tugas_auditor' => $id,
                    ...$person
                ]);

                $totalSaved++;
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        // Eksekusi query hapus data
        try {
            foreach ($toDeleteData as $person) {
                SuratTugasAuditorPegawai::where('id_surat_tugas_auditor', $id)
                    ->where('id_personil', (int) $person['id_personil'])
                    ->where('id_unit', (int) $person['id_unit'])
                    ->delete();

                $totalSaved++;
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        $createRoleSiakadV1 = $this->createRolePersonSiakadV1($data);

        if (Error::isError($createRoleSiakadV1)) {
            return $createRoleSiakadV1;
        }

        $deleteRoleSiakadV1 = $this->deleteRolePersonSiakadV1($toDeleteData);

        if (Error::isError($deleteRoleSiakadV1)) {
            return $deleteRoleSiakadV1;
        }

        return $totalSaved;
    }

    protected function createRolePersonSiakadV1($data)
    {
        $connectionV1 = 'siakadv1';
        $userV2 = Biodata::whereIn('id', array_column($data, 'id_personil'))->get(['id', 'ref_key_pegawai'])->toArray();
        $siakadPegawaiRefs = array_filter(array_column($userV2, 'ref_key_pegawai', 'id'));

        if (empty($siakadPegawaiRefs)) {
            return;
        }

        $refIdPegawai = implode("','", $siakadPegawaiRefs);

        // Ambil user v1 berdasarkan id ref pegawai
        $listUserV1 = DB::connection($connectionV1)->select(
            "SELECT
                ur.userid,
                ur.idpegawai,
                p.idunit
            FROM gate.sc_userrole ur
            JOIN gate.sc_role r ON r.idrole = ur.idrole
            JOIN hr.ms_pegawai p ON p.idpegawai = ur.idpegawai
            WHERE ur.idpegawai IN('{$refIdPegawai}')
            GROUP BY ur.userid, ur.idpegawai, p.idunit",
        );

        $listUserV1Pegawai = array_column($listUserV1, 'userid', 'idpegawai');
        $listUserV1Unit = array_column($listUserV1, 'idunit', 'idpegawai');
        $siakadPegawaiRefsFlip = array_flip($siakadPegawaiRefs);

        DB::connection($connectionV1)->beginTransaction();
        foreach ($siakadPegawaiRefsFlip as $idPegawai => $id) {
            $idUserV1 = $listUserV1Pegawai[$idPegawai] ?? null;
            $idUnitV1 = $listUserV1Unit[$idPegawai] ?? null;

            if (empty($idUserV1) || empty($idUnitV1)) {
                continue;
            }

            $roleV1 = DB::connection($connectionV1)->select("
                SELECT
                    idrole
                FROM gate.sc_userrole ur
                WHERE ur.userid = (
                    SELECT userid FROM gate.sc_user WHERE userid = :id_user
                )
                    AND idrole IN('ATR', 'ADT')
            ", [
                'id_user' => $idUserV1,
            ]);

            $roleV1 = array_column($roleV1, 'idrole');

            $forInsert = array_filter($data, function ($item) use ($id) {
                return $item['id_personil'] == $id;
            });

            if (empty($forInsert)) {
                continue;
            }

            $uniquePosisi = array_unique(array_column($forInsert, 'posisi'));

            if (empty($uniquePosisi)) {
                continue;
            }

            $isHasPositionAuditee = (in_array(SuratTugasAuditorPegawai::POSITION_AUDITEE, $uniquePosisi) || in_array(SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE, $uniquePosisi));
            $isHasPositionAuditor = array_intersect($uniquePosisi, [SuratTugasAuditorPegawai::POSITION_LEAD, SuratTugasAuditorPegawai::POSITION_MEMBER]);

            $isHasRoleAuditor = in_array('ATR', $roleV1);
            if ($isHasPositionAuditor && !$isHasRoleAuditor) {
                $item = array_filter($forInsert, function ($item) {
                    return ($item['posisi'] !== SuratTugasAuditorPegawai::POSITION_AUDITEE && $item['posisi'] !== SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE);
                });

                if (empty($item)) {
                    continue;
                }

                try {
                    DB::connection($connectionV1)->table('gate.sc_userrole')->insert([
                        'userid' => $idUserV1,
                        'idrole' => 'ATR',
                        'idpegawai' => $idPegawai,
                        'idsatker' => $idUnitV1,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return new Error("Gagal menambahkan role auditor pada user v1", 500);
                }
            }

            $isHasRoleAuditee = in_array('ADT', $roleV1);
            if ($isHasPositionAuditee && !$isHasRoleAuditee) {
                $item = array_filter($forInsert, function ($item) {
                    return ($item['posisi'] === SuratTugasAuditorPegawai::POSITION_AUDITEE || $item['posisi'] === SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE);
                });

                if (empty($item)) {
                    continue;
                }

                try {
                    DB::connection($connectionV1)->table('gate.sc_userrole')->insert([
                        'userid' => $idUserV1,
                        'idrole' => 'ADT',
                        'idpegawai' => $idPegawai,
                        'idsatker' => $idUnitV1,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return new Error("Gagal menambahkan role auditor pada user v1", 500);
                }
            }
        }

        DB::connection($connectionV1)->commit();
    }

    protected function deleteRolePersonSiakadV1($data)
    {
        $connectionV1 = 'siakadv1';
        $userV2 = Biodata::whereIn('id', array_column($data, 'id_personil'))->get(['id', 'ref_key_pegawai'])->toArray();
        $siakadPegawaiRefs = array_filter(array_column($userV2, 'ref_key_pegawai', 'id'));

        if (empty($siakadPegawaiRefs)) {
            return;
        }

        $refIdPegawai = implode("','", $siakadPegawaiRefs);

        // Ambil user v1 berdasarkan id ref pegawai
        $listUserV1 = DB::connection($connectionV1)->select(
            "SELECT
                ur.userid,
                ur.idpegawai,
                p.idunit
            FROM gate.sc_userrole ur
            JOIN gate.sc_role r ON r.idrole = ur.idrole
            JOIN hr.ms_pegawai p ON p.idpegawai = ur.idpegawai
            WHERE ur.idpegawai IN('{$refIdPegawai}')
            GROUP BY ur.userid, ur.idpegawai, p.idunit",
        );

        $listUserV1Pegawai = array_column($listUserV1, 'userid', 'idpegawai');
        $listUserV1Unit = array_column($listUserV1, 'idunit', 'idpegawai');
        $siakadPegawaiRefsFlip = array_flip($siakadPegawaiRefs);

        DB::connection($connectionV1)->beginTransaction();
        foreach ($siakadPegawaiRefsFlip as $idPegawai => $id) {
            $idUserV1 = $listUserV1Pegawai[$idPegawai] ?? null;
            $idUnitV1 = $listUserV1Unit[$idPegawai] ?? null;

            if (empty($idUserV1)) {
                continue;
            }

            $roleV1 = DB::connection($connectionV1)->select("
                SELECT
                    idrole
                FROM gate.sc_userrole ur
                WHERE ur.userid = (
                    SELECT userid FROM gate.sc_user WHERE userid = :id_user
                )
                    AND idrole IN('KA', 'ATR', 'ADT')
            ", [
                'id_user' => $idUserV1,
            ]);

            $roleV1 = array_column($roleV1, 'idrole');

            $forInsert = array_filter($data, function ($item) use ($id) {
                return $item['id_personil'] == $id;
            });

            if (empty($forInsert)) {
                continue;
            }

            $uniquePosisi = array_unique(array_column($forInsert, 'posisi'));

            if (empty($uniquePosisi)) {
                continue;
            }

            $isHasPositionAuditee = (in_array(SuratTugasAuditorPegawai::POSITION_AUDITEE, $uniquePosisi) || in_array(SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE, $uniquePosisi));
            $isHasPositionAuditor = array_intersect($uniquePosisi, [SuratTugasAuditorPegawai::POSITION_LEAD, SuratTugasAuditorPegawai::POSITION_MEMBER]);

            $isHasRoleAuditor = in_array('ATR', $roleV1);
            if ($isHasPositionAuditor && $isHasRoleAuditor) {
                $item = array_filter($forInsert, function ($item) {
                    return ($item['posisi'] !== SuratTugasAuditorPegawai::POSITION_AUDITEE && $item['posisi'] !== SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE);
                });

                if (empty($item)) {
                    continue;
                }

                $isExistAuditeeDifferentProdi = SuratTugasAuditorPegawai::where('id_personil', $id)
                    ->whereNot('posisi', SuratTugasAuditorPegawai::POSITION_AUDITEE)
                    ->whereNot('posisi', SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE)
                    ->limit(1)
                    ->exists();

                // Jika telah berada di penjadwalan dengan prodi yang berbeda, maka tidak perlu dihapus
                if ($isExistAuditeeDifferentProdi) {
                    continue;
                }

                try {
                    DB::connection($connectionV1)->table('gate.sc_userrole')
                        ->where('userid', $idUserV1)
                        ->where('idsatker', $idUnitV1)
                        ->where('idrole', 'ATR')
                        ->where('idpegawai', $idPegawai)
                        ->delete();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return new Error("Gagal menambahkan role auditor pada user v1", 500);
                }
            }

            $isHasRoleAuditee = in_array('ADT', $roleV1);
            if ($isHasPositionAuditee && $isHasRoleAuditee) {
                $item = array_filter($forInsert, function ($item) {
                    return ($item['posisi'] === SuratTugasAuditorPegawai::POSITION_AUDITEE || $item['posisi'] === SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE);
                });

                if (empty($item)) {
                    continue;
                }

                $isExistAuditeeDifferentProdi = SuratTugasAuditorPegawai::where('id_personil', $id)
                    ->whereIn('posisi', [
                        SuratTugasAuditorPegawai::POSITION_AUDITEE,
                        SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE
                    ])
                    ->limit(1)
                    ->exists();

                // Jika telah berada di penjadwalan dengan prodi yang berbeda, maka tidak perlu dihapus
                if ($isExistAuditeeDifferentProdi) {
                    continue;
                }

                try {
                    DB::connection($connectionV1)->table('gate.sc_userrole')
                        ->where('userid', $idUserV1)
                        ->where('idsatker', $idUnitV1)
                        ->where('idrole', 'ADT')
                        ->where('idpegawai', $idPegawai)
                        ->delete();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return new Error("Gagal menambahkan role auditor pada user v1", 500);
                }
            }
        }

        DB::connection($connectionV1)->commit();
    }
}
