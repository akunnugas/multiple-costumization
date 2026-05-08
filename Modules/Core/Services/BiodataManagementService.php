<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\User;
use Modules\Gate\Services\UserManagementService;

class BiodataManagementService
{
    /**
     * @var Biodata
     */
    protected $model = Biodata::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Biodata;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Biodata
     */
    public function show(int $id): Biodata
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Biodata
     */
    public function store(array $data): Biodata
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Biodata
     */
    public function update(array $data, int $id): Biodata
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
     * Sync data from SIAKAD V1 to SIAKAD V2
     *
     * return array
     */
    public function syncBiodataPegawaiFromSiakadv1($dataSiakad = [], $isHr = false)
    {
        // sync data from siakad v1 [gate.sc_user] to siakad v2
        $dataSiakadGate = new UserManagementService();
        list($err, $msg) = $dataSiakadGate->syncFromSiakadv1($dataSiakad, $isHr);
        if ($err) {
            return [$err, $msg];
        }

        if ($isHr) {
            $userOptions = User::options();
            $searchUserId = 'nama';
        }

        $mapping = [];
        $mapping['id_user'] = ['column' => 'id_user', 'options' => $userOptions ?? [], 'search' => $searchUserId ?? null];
        $mapping['nama'] = ['column' => 'nama'];
        $mapping['gelardepan'] = ['column' => 'gelar_depan'];
        $mapping['gelarbelakang'] = ['column' => 'gelar_belakang'];
        $mapping['tgllahir'] = ['column' => 'tanggal_lahir'];
        $mapping['tmplahir'] = ['column' => 'tempat_lahir'];
        $mapping['jeniskelamin'] = ['column' => 'jenis_kelamin'];
        $mapping['alamat'] = ['column' => 'alamat'];
        $mapping['rt'] = ['column' => 'rt'];
        $mapping['rw'] = ['column' => 'rw'];
        $mapping['kodepos'] = ['column' => 'kode_pos'];
        $mapping['nik'] = ['column' => 'nik'];
        $mapping['nokk'] = ['column' => 'no_kk'];
        $mapping['email'] = ['column' => 'email', 'lowercase' => true];
        $mapping['nohp'] = ['column' => 'telepon'];

        // pk
        $pk = ['idpegawai'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, $this->model, $pk, 'ref_key_pegawai', ['ref_key_mahasiswa']);

        return [$err, $msg];
    }

    /**
     * Sync data mahasiswa dari SIAKAD V1 ke table core.person SIAKAD V2
     *
     * return array
     */
    public function syncBiodataMahasiswaFromSiakadv1($dataMahasiswa)
    {
        $userOptions = User::options();
        $searchUserId = 'nama';

        // TODO: mapping tablenya belum lengkap
        $mapping = [];
        $mapping['id_user'] = ['column' => 'id_user', 'options' => $userOptions ?? [], 'search' => $searchUserId];
        $mapping['nama'] = ['column' => 'nama'];
        $mapping['gelardepan'] = ['column' => 'gelar_depan'];
        $mapping['gelarbelakang'] = ['column' => 'gelar_belakang'];
        $mapping['tgllahir'] = ['column' => 'tanggal_lahir'];
        $mapping['tmplahir'] = ['column' => 'tempat_lahir'];
        $mapping['jk'] = ['column' => 'jenis_kelamin'];
        $mapping['alamat'] = ['column' => 'alamat'];
        $mapping['rt'] = ['column' => 'rt'];
        $mapping['rw'] = ['column' => 'rw'];
        $mapping['kodepos'] = ['column' => 'kode_pos'];
        $mapping['nik'] = ['column' => 'nik'];
        $mapping['nokk'] = ['column' => 'no_kk'];
        $mapping['email'] = ['column' => 'email', 'lowercase' => true];
        $mapping['hp'] = ['column' => 'telepon'];

        // pk
        $pk = ['nim'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataMahasiswa, $this->model, $pk, 'ref_key_mahasiswa', ['ref_key_pegawai']);

        return [$err, $msg];
    }
}
