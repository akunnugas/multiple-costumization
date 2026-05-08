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
use Modules\Kerjasama\Models\JawabanPeserta;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Str;

class JawabanManagementService
{
    /**
     * @var Kegiatan
     */
    protected $model = JawabanPeserta::class;
    protected $kerjasama = Kerjasama::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JawabanPeserta;
    }

    protected function defineIndexQuery(array $filter = [], array $order = []): array
    {
        $sql = "
        SELECT 
            p.id AS pertanyaan_id,
            p.pertanyaan,
            oj.id AS opsi_jawaban_id,
            oj.jawaban AS opsi_jawaban,
            COUNT(jp.id) AS total_jawaban,
            AVG(CASE WHEN jp.jawaban ~ '^[0-9.]+$' THEN jp.jawaban::float ELSE NULL END) AS rata_rata_jawaban
        FROM kerjasama.pertanyaan p
        LEFT JOIN kerjasama.opsi_jawaban oj ON oj.pertanyaan_id = p.id
        LEFT JOIN kerjasama.jawaban_peserta jp ON jp.pertanyaan_id = p.id AND jp.opsi_jawaban_id = oj.id
        WHERE p.waktu_dihapus IS NULL
        GROUP BY p.id, p.pertanyaan, oj.id, oj.jawaban
    ";

        $fieldMap = [
            'pertanyaan_id' => 'p.id',
            'pertanyaan' => 'p.pertanyaan',
            'opsi_jawaban_id' => 'oj.id',
            'opsi_jawaban' => 'oj.jawaban',
            'total_jawaban' => 'total_jawaban',
            'rata_rata_jawaban' => 'rata_rata_jawaban',
        ];

        $defaultFilter = "1=1";
        $defaultOrder = "p.id ASC";

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
        // Ensure filter for id_induk_kerjasama is set
        $filter = [
            ...$filter,
            [
                'field' => 'model_id',
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
     * @return Evaluasi
     */
public function show(int $id): Evaluasi
{
    $evaluasi = Evaluasi::with([
        'pertanyaan.opsiJawaban',
        'pertanyaan' => function ($q) {
            $q->with(['jawabanPeserta' => function ($q2) {
                $q2->with(['peserta', 'opsiJawaban']);
            }]);
        }
    ])->findOrFail($id);

    return $evaluasi;
}

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Kegiatan
     */
    public function store(array $data): JawabanPeserta|Error
    {
        DB::beginTransaction();


        // Filter out fields not in fillable
        $data = collect($data)->only((new JawabanPeserta)->getFillable())->toArray();

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
     * @return Kegiatan
     */
    public function update(array $data, int $id): Evaluasi|Error
    {
        DB::beginTransaction();
        $model = $this->model->findOrFail($id);

        // Filter out fields not in fillable
        $data = collect($data)->only($model->getFillable())->toArray();

        // if (array_key_exists('nomor_dokumen', $data) && empty($data['nomor_dokumen'])) {
        //     $data['nomor_dokumen'] = null;
        // }
        // $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_KEGIATAN, $model);

        // if (!empty($upload)) {
        //     $data['id_dokumen'] = $upload->get()?->id;
        // } else {
        //     unset($data['id_dokumen']);
        // }

        $model->update($data);

        // if (!empty($upload) && Error::isError($upload->getError())) {
        //     DB::rollBack();
        //     return $upload->getError();
        // }

        // Execute upload
        // $upload?->executeUpload();

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


    public function determinteModelTypeAndId($idInduk)
    {
        $indukKerjasama = $this->show($idInduk);

        if (empty($indukKerjasama)) {
            return [null, null];
        }


        return $indukKerjasama;
    }
}
