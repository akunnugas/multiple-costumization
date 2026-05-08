<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Services\DokumenManagementService;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\SpmiDokumen;
use Modules\SPMI\Models\SpmiJenisDokumen;

class SpmiDokumenManagementService
{
    /**
     * @var SpmiDokumen
     */
    protected $model;

    public function __construct()
    {
        $this->model = new SpmiDokumen;
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
                    qd.id,
                    qd.kode_spmi_dokumen,
                    qd.nama_spmi_dokumen,
                    qd.versi,
                    qd.waktu_diubah,
                    qd.apakah_aktif,
                    d.extension_versi_terbaru
                FROM $table qd
                LEFT JOIN dms.dokumen d on d.id = qd.id_dokumen
                    AND qd.waktu_dihapus is null";

        $defaultFilter = "qd.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan list data
     *
     * @param array $order
     *
     * @return mixed
     */
    public function indexByType(array|null $order = []): mixed
    {
        $qualityTypes = SpmiJenisDokumen::get();

        $data = $qualityTypes->map(function ($item) use ($order) {
            // Filter order berdasarkan tipe dokumen
            $order = array_filter($order, function ($orderVal) use ($item) {
                return ((int) $orderVal['id_jenis']) == $item['id'];
            });

            $list = $this->index(perPage: 100, order: $order, filter: [
                ['field' => 'id_jenis', 'value' => $item->id, 'operator' => '=']
            ]);

            return [
                'id' => $item->id,
                'nama_spmi_jenis_dokumen' => $item->nama_spmi_jenis_dokumen,
                'alamat_berkas' => $item->alamat_berkas,
                'deskripsi_singkat' => $item->deskripsi_singkat,
                'data' => $list
            ];
        })->toArray();

        return $data;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return SpmiDokumen
     */
    public function show(int $id): SpmiDokumen
    {
        $model = $this->model->findOrFail($id);

        return $model;
    }

    /**
     * Menampilkan spesifik tipe dokumen berdasarkan id.
     *
     * @param int $id
     *
     * @return SpmiDokumen|null
     */
    public function showTypeDocument(int $id): SpmiJenisDokumen|null
    {
        $model = SpmiJenisDokumen::find($id);

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SpmiDokumen
     */
    public function store(array $data): SpmiDokumen|Error
    {
        $file = $data['id_dokumen'];

        // Pengecekan jika versi sudah ada, maka tidak lolos validasi
        $isExistVersion = !!SpmiDokumen::where('id_jenis', $data['id_jenis'])
            ->where('versi', $data['versi'])->first();

        if ($isExistVersion) {
            return new Error('Versi dokumen sudah ada, silahkan masukkan versi lain', 409);
        }

        DB::beginTransaction();

        $upload = new UploadDokumen();
        $upload = $upload->upload(
            file: $file,
            name: $data['nama_spmi_dokumen'],
            folderCode: UploadDokumen::SPMI_PENJAMINAN_MUTU,
            moduleCode: Modul::CODE_SPMI,
            note: $data['deskripsi'],
            withTransaction: false
        );

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()->id;

        $model = $this->model->create($data);

        // Execute upload
        $upload->executeUpload();

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SpmiDokumen
     */
    public function update(array $data, int $id): SpmiDokumen
    {
        DB::beginTransaction();

        $model = $this->model->findOrFail($id);

        $upload = new UploadDokumen();
        $upload = $upload->update(
            data: [
                'file' => $data['id_dokumen'] ?? null,
                'name' => $data['nama_spmi_dokumen'],
                'note' => $data['deskripsi']
            ],
            id: $model->id_dokumen,
            withTransaction: false,
            isReplace: false
        );

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()?->id ?? $model->id_dokumen;

        $model->update($data);

        // Execute upload
        $upload->executeUpload();

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id): void
    {
        $model = $this->model->findOrFail($id);

        DB::beginTransaction();
        // Hapus dokumen di dms juga
        (new DokumenManagementService)->destroy($model->id_dokumen);
        $model->destroy($model->id);
        DB::commit();
    }
}
