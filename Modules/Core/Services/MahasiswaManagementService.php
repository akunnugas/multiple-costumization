<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Mahasiswa;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\User;
use Modules\Gate\Services\UserManagementService;

class MahasiswaManagementService
{
    /**
     * @var Mahasiswa
     */
    protected $model = Mahasiswa::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Mahasiswa;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Mahasiswa
     */
    public function show(int $id): Mahasiswa
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Mahasiswa
     */
    public function store(array $data): Mahasiswa
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Mahasiswa
     */
    public function update(array $data, int $id): Mahasiswa
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
    public function syncMahasiswaFromSiakadv1($limit = null)
    {
        $siakadV1Connection = DB::connection('siakadv1');
        $sql = "select * from akademik.ak_mahasiswa " . ($limit ? "limit $limit" : '');

        try {
            $result = $siakadV1Connection->select($sql);
            $dataMahasiswa = json_decode(json_encode($result), true);
        } catch (Exception $e) {
            return [true, $e->getMessage()];
        }

        // sync data from siakad v1 [ref.ms_unit] to siakad v2
        $dataSiakadOrganization = new UnitKerjaManagementService();
        list($err, $msg) = $dataSiakadOrganization->syncFromSiakadv1();
        if ($err) {
            return [$err, $msg];
        }

        // sync data from siakad v1 [akademik.ak_mahasiswa] to siakad v2 [gate.user]
        list($err, $msg) = (new UserManagementService())->syncUserMahasiswaFromSiakadV1($dataMahasiswa);
        if ($err) {
            return [$err, $msg];
        }

        // sync data from siakad v1 [akademik.ak_mahasiswa] to siakad v2 [core.biodata]
        list($err, $msg) = (new BiodataManagementService())->syncBiodataMahasiswaFromSiakadv1($dataMahasiswa);
        if ($err) {
            return [$err, $msg];
        }

        $optOrganization = UnitKerja::pluck('kode_unit', 'id')->toArray();

        // sync data from siakad v1 [akademik.ak_mahasiswa] to siakad v2 [core.mahasiswa]
        $mapping = [];
        $mapping['id_person'] = ['column' => 'id_biodata', 'options' => Biodata::options(), 'search' => 'nama', 'notnull' => true];
        $mapping['nama'] = ['column' => 'nama_mahasiswa'];
        $mapping['nim'] = ['column' => 'nim', 'notnull' => true];
        $mapping['idunit'] = ['column' => 'id_unit', 'options' => $optOrganization, 'notnull' => true];

        $pk = ['nim']; // pk siakad

        list($err, $msg) = SyncSiakad::sync($mapping, $dataMahasiswa, Mahasiswa::class, $pk);

        return [$err, $msg];
    }

    public function syncSingleMahasiswaFromSiakadv1($arr_nim = [])
    {
        $siakadV1Connection = DB::connection('siakadv1');
        $sql = "select * from akademik.ak_mahasiswa where nim in ('" . implode("','", $arr_nim) . "') and idstatusmhs = 'A'";

        $result = $siakadV1Connection->select($sql);
        $dataMahasiswa = json_decode(json_encode($result), true);

        foreach ($dataMahasiswa as $key => $mahasiswa){
            // sync data from siakad v1 [akademik.ak_mahasiswa] to siakad v2 [gate.user]
            $gateUser = User::where('ref_key_siakad', $mahasiswa['nim'])->first();
            $payload = [
                'nama_user' => $mahasiswa['nama'],
                'email_user' => $mahasiswa['email'],
                'telepon_user' => $mahasiswa['telepon'],
            ];
            if ($gateUser) {
                DB::table('gate.user')->where('id', $gateUser->id)->update($payload);
            } else {
                $payload['ref_key_siakad'] = $mahasiswa['nim'];
                DB::table('gate.user')->insert($payload);
                $gateUser = User::where('ref_key_siakad', $mahasiswa['nim'])->first();
            }

            // sync data from siakad v1 [akademik.ak_mahasiswa] to siakad v2 [core.biodata]
            $biodata = Biodata::where('ref_key_mahasiswa', $mahasiswa['nim'])->first();
            $payload = [
                'nama' => $mahasiswa['nama'],
                'email' => $mahasiswa['email'],
                'telepon' => $mahasiswa['telepon'],
                'gelar_depan' => $mahasiswa['gelardepan'],
                'gelar_belakang' => $mahasiswa['gelarbelakang'],
                'tanggal_lahir' => $mahasiswa['tgllahir'],
                'tempat_lahir' => $mahasiswa['tmplahir'],
                'alamat' => $mahasiswa['alamat'],
                'rt' => $mahasiswa['rt'],
                'rw' => $mahasiswa['rw'],
                'kode_pos' => $mahasiswa['kodepos'],
                'nik' => $mahasiswa['nik'],
                'no_kk' => $mahasiswa['nokk'],
            ];
            if ($biodata) {
                DB::table('core.biodata')->where('id', $biodata->id)->update($payload);
            } else {
                $payload['ref_key_mahasiswa'] = $mahasiswa['nim'];
                DB::table('core.biodata')->insert($payload);
                $biodata = Biodata::where('ref_key_mahasiswa', $mahasiswa['nim'])->first();
            }

            // sync data from siakad v1 [akademik.ak_mahasiswa] to siakad v2 [core.mahasiswa]
            $dataMhs = Mahasiswa::where('nim', $mahasiswa['nim'])->first();
            $unit = UnitKerja::where('kode_unit', $mahasiswa['idunit'])->first();
            $payload = [
                'id_biodata' => $biodata->id,
                'nama_mahasiswa' => $mahasiswa['nama'],
                'nim' => $mahasiswa['nim'],
                'id_unit' => $unit->id,
            ];
            if ($dataMhs) {
                DB::table('core.mahasiswa')->where('id', $dataMhs->id)->update($payload);
            } else {
                DB::table('core.mahasiswa')->insert($payload);
            }
        }
    }
}
