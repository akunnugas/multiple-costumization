<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Illuminate\Support\Facades\DB;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\Evaluasi;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Pertanyaan;
use Str;

class PertanyaanEvaluasiManagementService
{
    /**
     * @var Kegiatan
     */
    protected $model = Pertanyaan::class;
    protected $kerjasama = Kerjasama::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Pertanyaan;
    }

    protected function defineIndexQuery(array $filter = [], array $order = []): array
    {
        $table = 'kerjasama.evaluasi';
        $sql = "SELECT 
                e.*, 
                (SELECT COUNT(*) FROM kerjasama.pertanyaan p WHERE p.evaluasi_id = e.id AND p.waktu_dihapus IS NULL) AS jumlah_pertanyaan,
                (SELECT COUNT(*) FROM kerjasama.peserta ps WHERE ps.evaluasi_id = e.id AND ps.waktu_dihapus IS NULL) AS jumlah_peserta
            FROM $table e
            LEFT JOIN kerjasama.kegiatan k ON e.model_id = k.id AND e.tipe_model = '$this->kerjasama'
            ";

        $fieldMap = [
            'judul_evaluasi' => 'e.judul_evaluasi',
            'tipe_evaluasi' => 'e.tipe_evaluasi',
            'tipe_model' => 'e.tipe_model',
            'model_id' => 'e.model_id',
            'nama' => 'e.nama',
            'is_published' => 'e.is_published',
            'active' => 'e.active',
            'mulai' => 'e.mulai',
            'selesai' => 'e.selesai',
            'created_at' => 'e.created_at',
            'updated_at' => 'e.updated_at',
            'jumlah_pertanyaan' => 'jumlah_pertanyaan',
            'jumlah_peserta' => 'jumlah_peserta',
        ];

        $defaultFilter = "e.waktu_dihapus IS NULL";
        $defaultOrder = "e.created_at DESC";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            defaultOrder: $defaultOrder,
            fieldMap: $fieldMap,
        );

        return [$sql, $bindings];
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
        [$sql, $bindings] = $this->defineIndexQuery($filter, $order);

        return Pagination::create($sql, $bindings, $page, $perPage);
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
    public function indexForKerjasama(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = [], int $idParent): mixed
    {
        $filter = [
            ...$filter,
            [
                'field' => 'k.id_induk_kerjasama',
                'value' => $idParent
            ]
        ];

        [$sql, $bindings] = $this->defineIndexQuery($filter, $order);

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Pertanyaan
     */
    public function show(int $id): Pertanyaan
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Pertanyaan|Error
     */
    public function store(array $data): Pertanyaan|Error
    {
        DB::beginTransaction();
        try {
            $model = $this->model->create($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
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
     * @return Pertanyaan|Error
     */
    public function update(array $data, int $id): Pertanyaan|Error
    {
        DB::beginTransaction();
        $model = $this->model->findOrFail($id);
        $model->update($data);
        DB::commit();
        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
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
        // Check kalo punya answer atau engga, kalau punya jangan hapus
        $hasAnswers = $this->model->whereIn('id', $ids)->has('jawabanPeserta')->exists();

        if ($hasAnswers) {
            return new Error('Tidak dapat menghapus pertanyaan yang sudah memiliki jawaban.');
        }

        try {
            DB::transaction(function () use ($ids) {
                $this->model->destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Proses upload document ke DMS.
     *
     * @param array $data
     * @param string $field
     * @param string $folderCode
     * @param Kegiatan|null $model
     * @return UploadDokumen|null|Error
     */
    private function processUploadDocument(array $data, string $field, string $folderCode, Kegiatan $model = null): UploadDokumen|null|Error
    {
        $document = $data[$field] ?? null;
        if (empty($document)) {
            return null;
        }
        $docName = $document->getClientOriginalName();
        $docName = pathinfo($docName, PATHINFO_FILENAME);

        $upload = new UploadDokumen();
        if (empty($model->{$field})) {
            $document = $upload->upload(
                file: $document,
                name: $docName,
                folderCode: $folderCode,
                note: null,
                moduleCode: Modul::CODE_KERJASAMA,
                withTransaction: false,
            );
        } else {
            $document = $upload->update(
                data: [
                    'file' => $document,
                    'name' => $docName,
                    'note' => null,
                ],
                id: $model->{$field},
                withTransaction: false,
                isReplace: true
            );
        }

        if ($document instanceof Error) {
            return new Error($document->message);
        }

        return $document;
    }

    public function getTotalRealisasi($idIndukKerjasama): int
    {
        return $this->model
            ->where('id_induk_kerjasama', $idIndukKerjasama)
            ->sum('anggaran');
    }

    /**
     * Export
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function export(array $order = [], array $filter = []): mixed
    {
        [$sql, $bindings] = $this->defineIndexQuery($filter, $order);

        return array_map(function ($value) {
            return (array) $value;
        }, DB::select($sql, $bindings) ?? []);
    }

    /**
     * Untuk mengecek apakah kegiatan termasuk join degree atau double degree
     *
     * @param $kegiatan berupa id_kegiatan atau array assoc berisi data kegiatan
     * @return array [bool, string]
     */
    public function isKegiatanJoinOrDoubleDegree(mixed $kegiatan)
    {
        $bentukKegiatanOptions = BentukKegiatan::optionsWithoutJenisKegiatan();

        // handle jika kegiatan merupakan array assoc berisi data kegiatan
        if (is_array($kegiatan) && !empty($kegiatan['id_bentuk_kegiatan'])) {
            $idBentukKegiatan = $kegiatan['id_bentuk_kegiatan'];
        } else {
            $idBentukKegiatan = $this->model
                ->toBase()
                ->where('id', $kegiatan)
                ->first(['id_bentuk_kegiatan'])
                ->id_bentuk_kegiatan ?? null;
        }

        if (empty($idBentukKegiatan)) {
            return [false, null];
        }

        $namaBentukKegiatan = $bentukKegiatanOptions[$idBentukKegiatan];

        // sementara memakai cara ini terlebih dahulu
        // karena bentuk kegiatan ini termasuk defaut data
        // yang tidak bisa diedit maupun dihapus
        $conditionResult = Str::contains(
            $namaBentukKegiatan,
            ['joint degree', 'dual degree'],
            true
        );

        return [$conditionResult, $namaBentukKegiatan];
    }

    public function isBentukKegiatanAlreadyPick($idBentukKegiatan, $idIndukKerjasama, $exceptIds = [])
    {
        $data = $this->model->query()
            ->whereNull('kerjasama.kegiatan.waktu_dihapus')
            ->when(!empty($exceptIds), function ($query) use ($exceptIds) {
                return $query->whereNotIn('kerjasama.kegiatan.id', $exceptIds);
            })
            ->where('kerjasama.kegiatan.id_induk_kerjasama', $idIndukKerjasama)
            ->where('kerjasama.kegiatan.id_bentuk_kegiatan', $idBentukKegiatan)
            ->leftJoin('kerjasama.bentuk_kegiatan', 'kerjasama.kegiatan.id_bentuk_kegiatan', '=', 'kerjasama.bentuk_kegiatan.id')
            ->first(['nama_bentuk_kegiatan']);

        if (empty($data)) {
            return [false, null];
        }

        return [true, $data->nama_bentuk_kegiatan];
    }
}
