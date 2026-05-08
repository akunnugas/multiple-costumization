<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;
use Modules\HR\Models\EmployeeStatus;
use Modules\HR\Models\JabatanAkademik;
use Modules\HR\Models\WorkRelation;
use Modules\HR\Services\EmployeeStatusManagementService;
use Modules\HR\Services\JabatanAkademikManagementService;
use Modules\HR\Services\WorkRelationManagementService;

class PegawaiManagementService
{
    /**
     * @var Pegawai
     */
    protected $model = Pegawai::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Pegawai;
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

        $sql = "SELECT DISTINCT
            e.id,
            e.nip,
            coalesce(p.gelar_depan || ' ', '') || trim(p.nama) || coalesce(', ' || p.gelar_belakang, '') nama,
            o.nama_unit,
            od.nama_unit AS homebase,
            e.akun_sidik_jari,
            es.name AS status_pegawai
        FROM $table e
        LEFT JOIN core.biodata p ON p.id = e.id_biodata
        LEFT JOIN hr.employee_statuses es ON es.id = e.id_status_pegawai
        LEFT JOIN core.unit_kerja o ON o.id = e.id_unit_kerja
        LEFT JOIN core.unit_kerja od ON od.id = e.id_homebase_dosen";

        $fieldMap = [
            'status_pegawai' => 'es.name',
            'nama_unit' => 'o.nama_unit',
            'homebase' => 'od.nama_unit',
        ];

        $defaultFilter = "e.waktu_dihapus is null";

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
     * @return Collection
     */
    public function show(int $id): Collection|Error
    {
        $sql = "SELECT p.*, b.*
                FROM core.pegawai p
                LEFT JOIN core.biodata b ON b.id = p.id_biodata
                WHERE p.id = :id";

        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            return new Error('Data tidak ditemukan.', 404);
        }

        $data = Collection::make($select[0]);

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Pegawai
     */
    public function store(array $data): Pegawai
    {
        DB::beginTransaction();

        $person = Biodata::create($data);
        $model = $this->model->create($data + ['id_biodata' => $person->id]);

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Pegawai
     */
    public function update(array $data, int $id): Pegawai
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        // FIXME: save biodata
        $biodata = Biodata::findOrFail($model->id_biodata);
        //...

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
     * Get data from SIAKAD V1 [hr.ms_pegawai]
     *
     * @return array
     */
    public function getFromSiakadV1()
    {
        $connection = 'siakadv1';
        $query =
            "select
                idpegawai,
                nama,
                nip,
                idunit,
                idstatusaktif,
                idhubkerja,
                idfungsional,
                nippns,
                nidn,
                emailkampus,
                idhomebase,
                gelardepan,
                gelarbelakang,
                tgllahir,
                tmplahir,
                jeniskelamin,
                alamat,
                rt,
                rw,
                kodepos,
                nik,
                nokk,
                email,
                nohp
                from hr.ms_pegawai";

        $result = DB::connection($connection)->select($query);
        $result = json_decode(json_encode($result), true);

        return $result;
    }

    /**
     * Sync data from SIAKAD V1 to SIAKAD V2
     *
     * return array
     */
    public function syncFromSiakadv1()
    {
        // set memory to 1024M
        ini_set('memory_limit', '1024M');
        gc_enable();

        // sync data from siakad v1 [ref.ms_unit] to siakad v2
        $dataSiakadOrganization = new UnitKerjaManagementService();
        list($err, $msg) = $dataSiakadOrganization->syncFromSiakadv1();
        if ($err) {
            return [$err, $msg];
        }
        unset($dataSiakadOrganization);
        gc_collect_cycles();

        // sync data from siakad v1 [ref.lv_statuspegawai] to siakad v2
        $dataSiakadStatus = new EmployeeStatusManagementService();
        list($err, $msg) = $dataSiakadStatus->syncFromSiakadv1();
        if ($err) {
            return [$err, $msg];
        }
        unset($dataSiakadStatus);
        gc_collect_cycles();

        // sync data from siakad v1 [ref.ms_hubkerja] to siakad v2
        $dataSiakadWorkRelation = new WorkRelationManagementService();
        list($err, $msg) = $dataSiakadWorkRelation->syncFromSiakadv1();
        if ($err) {
            return [$err, $msg];
        }
        unset($dataSiakadWorkRelation);
        gc_collect_cycles();

        // sync data from siakad v1 [hr.ms_fungsional] to siakad v2
        $dataJatabanFungsional = new JabatanAkademikManagementService();
        list($err, $msg) = $dataJatabanFungsional->syncJabatanAkademikFromSiakadV1();
        if ($err) {
            return [$err, $msg];
        }
        unset($dataJatabanFungsional);
        gc_collect_cycles();

        // get data pegawai from v1
        $dataSiakad = $this->getFromSiakadV1();

        // sync data from siakad v1 [gate.sc_user] to siakad v2
        $dataSiakadGate = new BiodataManagementService();
        list($err, $msg) = $dataSiakadGate->syncBiodataPegawaiFromSiakadv1($dataSiakad, true);
        if ($err) {
            return [$err, $msg];
        }
        unset($dataSiakadGate);
        gc_collect_cycles();

        $optPerson = Biodata::options();
        $optOrganization = UnitKerja::pluck('kode_unit', 'id')->toArray();
        $optEmployeeStatus = EmployeeStatus::pluck('code', 'id')->toArray();
        $optWorkRelation = WorkRelation::pluck('code', 'id')->toArray();
        $optJabatanAkademik = JabatanAkademik::pluck('ref_key_siakad', 'id')->toArray();

        $mapping = [];
        $mapping['nama'] = ['column' => 'id_biodata', 'isNew' => true, 'options' => $optPerson, 'notnull' => true];
        $mapping['nip'] = ['column' => 'nip'];
        $mapping['idunit'] = ['column' => 'id_unit_kerja', 'options' => $optOrganization, 'defaultValue' => false];
        $mapping['idstatusaktif'] = ['column' => 'id_status_pegawai', 'options' => $optEmployeeStatus];
        $mapping['idhubkerja'] = ['column' => 'id_hubungan_kerja', 'options' => $optWorkRelation];
        $mapping['idfungsional'] = ['column' => 'id_jabatan_akademik', 'options' => $optJabatanAkademik];
        $mapping['nippns'] = ['column' => 'nip_pns'];
        $mapping['nidn'] = ['column' => 'nidn'];
        $mapping['emailkampus'] = ['column' => 'email_kampus', 'lowercase' => true];
        $mapping['idhomebase'] = ['column' => 'id_homebase_dosen', 'options' => $optOrganization, 'defaultValue' => false];

        // primary key v1
        $pk = ['idpegawai'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, $this->model, $pk);

        unset($dataSiakad);
        gc_collect_cycles();

        return [$err, $msg];
    }

    public function setUserIdPegawai($idPegawai, $user = null)
    {
        $pegawai = Biodata::where('ref_key_pegawai', $idPegawai)->first();
        if (!empty($pegawai)) {
            $pegawai->id_user = $user->id;
            $pegawai->save();
        }
    }
}
