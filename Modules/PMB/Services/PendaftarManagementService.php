<?php

namespace Modules\PMB\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Pendaftar;

class PendaftarManagementService
{
    /**
     * @var Pendaftar
     */
    protected $model = Pendaftar::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Pendaftar;
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
        $table = $this->model->getTable();

        $sql = "SELECT
                    r.id,
                    r.kode_pendaftar,
                    p.nama,
                    p.jenis_kelamin
                FROM $table r
                LEFT JOIN core.biodata p ON p.id = r.id_biodata and p.waktu_dihapus is null";

        if (!empty($order) && $order['field'] == 'id') {
            $order = ['field' => 'p.nama', 'direction' => 'asc'];
        }

        $defaultFilter = "r.waktu_dihapus is null";

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
     * @return Pendaftar
     */
    public function show(int $id): Collection
    {
        $table = $this->model->getTable();

        $sql = "SELECT
                    r.*,
                    p.nik,
                    p.nama,
                    p.jenis_kelamin,
                    p.tempat_lahir,
                    p.tanggal_lahir,
                    p.no_kk,
                    p.email,
                    p.telepon,
                    p.desa,
                    p.dusun,
                    p.alamat,
                    p.rt,
                    p.rw,
                    p.kode_pos,
                    p.no_kps,
                    p.npsn,
                    p.berat,
                    p.tinggi,
                    p.no_paspor,
                    p.nama_instansi,
                    p.ukuran_seragam,
                    p.id_agama,
                    p.id_suku,
                    p.id_negara,
                    p.id_provinsi,
                    p.id_kota,
                    p.id_kecamatan,
                    p.id_pekerjaan,
                    rel.nama_agama as religion,
                    eth.nama_suku as ethnic,
                    cou.nama_wilayah as country,
                    pro.nama_wilayah as province,
                    cit.nama_wilayah as city,
                    dis.nama_wilayah as district,
                    j.nama_pekerjaan as job
                FROM " . $table . " r
                LEFT JOIN core.biodata p ON p.id = r.id_biodata AND p.waktu_dihapus is null
                LEFT JOIN core.agama rel ON rel.id = p.id_agama AND rel.waktu_dihapus IS NULL
                LEFT JOIN core.suku eth ON eth.id = p.id_suku AND eth.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah cou ON cou.id = p.id_negara AND cou.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah pro ON pro.id = p.id_provinsi AND pro.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah cit ON cit.id = p.id_kota AND cit.waktu_dihapus IS NULL
                LEFT JOIN core.wilayah dis ON dis.id = p.id_kecamatan AND dis.waktu_dihapus IS NULL
                LEFT JOIN core.pekerjaan j ON j.id = p.id_pekerjaan AND j.waktu_dihapus IS NULL
                WHERE r.waktu_dihapus IS NULL
                    AND r.id = :id
                ";

        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        $data = Collection::make($select[0]);

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        if (empty($data['id_periode_akademik'])) {
            return new Error('Periode akademik belum aktif');
        }

        $existingEmail = Biodata::where('email', $data['email'])->first();

        if (!empty($existingEmail)) {
            return new Error('Email sudah terdaftar sebelumnya. Silahkan login terlebih dahulu.');
        }

        try {
            DB::beginTransaction();
            $newUser = User::create($data + ['telepon_user' => $data['telepon']]);
            $newPerson = Biodata::create($data + ['id_user' => $newUser->id]);
            $model = $this->model->create($data + ['id_biodata' => $newPerson->id]);

            DB::commit();

            return $model;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $model = $this->model->findOrFail($id);

        try {
            DB::beginTransaction();

            Biodata::find($model->person_id)->update($data);
            $model->update($data);

            DB::commit();

            return $model;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
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
     * Mengembalikan person_id berdasarkan id pendaftar.
     *
     * @param int $id
     *
     * @return void
     */
    public function getPersonId(int $registrantId): int
    {
        return $this->model->where('id', $registrantId)->first()->person_id ?? 0;
    }
}
