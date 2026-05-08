<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str as SupportStr;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\Evaluasi;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Str;

class EvaluasiKerjasamaManagementService
{
    /**
     * @var Kegiatan
     */
    protected $model = Evaluasi::class;
    protected $kerjasama = Kerjasama::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Evaluasi;
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
        // Handle custom filter mapping
        $customWhere = [];

        foreach ($filter as $key => &$f) {
            if (is_array($f) && isset($f['field']) && $f['field'] === 'is_published') {
                if (isset($f['value'])) {
                    if ($f['value'] === 'unpublished') {
                        $f['value'] = 0;
                    } elseif ($f['value'] === 'kadaluwarsa') {
                        unset($filter[$key]);
                        $customWhere[] = "e.selesai < NOW()";
                    } elseif ($f['value'] === 'dijadwalkan') {
                        unset($filter[$key]);
                        $customWhere[] = "e.mulai > NOW()";
                    }
                }
            }
        }
        unset($f);
        
        // Re-index filter array 
        $filter = array_values($filter);

        $filter[] = [
            'field' => 'model_id',
            'value' => $idParent
        ];

        [$sql, $bindings] = $this->defineIndexQuery($filter, $order);

        // Inject custom WHERE 
        if (!empty($customWhere)) {
            $customSqlCondition = implode(' AND ', $customWhere);
            
            $orderByPos = strripos($sql, 'ORDER BY');

            if ($orderByPos !== false) {
                $sql = substr_replace($sql, " AND $customSqlCondition ", $orderByPos, 0);
            } else {
                if (stripos($sql, 'WHERE') !== false) {
                     $sql .= " AND $customSqlCondition";
                } else {
                     $sql .= " WHERE $customSqlCondition";
                }
            }
        }

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
        $data = $this
            ->model
            ->with([
                'pertanyaan.opsiJawaban',
                'peserta',
            ])
            ->where('id', $id)
            ->whereNotNull('model_id')
            ->firstOrFail();

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Kegiatan
     */
    public function store(array $data): Evaluasi|Error
    {
        DB::beginTransaction();


        $data = collect($data)->only((new Evaluasi)->getFillable())->toArray();

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


    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $evaluasi = Evaluasi::with(['pertanyaan.opsiJawaban'])->findOrFail($id);

            $newEvaluasi = $evaluasi->replicate();
            $newEvaluasi->judul_evaluasi = $evaluasi->judul_evaluasi . ' (Copy)';
            $newEvaluasi->uuid = SupportStr::uuid();
            $newEvaluasi->is_published = 0;
            $newEvaluasi->save();

            foreach ($evaluasi->pertanyaan as $pertanyaan) {
                $newPertanyaan = $pertanyaan->replicate();
                $newPertanyaan->evaluasi_id = $newEvaluasi->id;
                $newPertanyaan->save();

                foreach ($pertanyaan->opsiJawaban as $opsi) {
                    $newOpsi = $opsi->replicate();
                    $newOpsi->pertanyaan_id = $newPertanyaan->id;
                    $newOpsi->save();
                }
            }

            DB::commit();

            return $newEvaluasi;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Duplicate Error: ' . $e->getMessage());
            return new Error(exception: $e);
        }
    }

    public function getPublishStatusText($data)
    {
        $now = now();
        $start = isset($data['mulai']) ? \Carbon\Carbon::parse($data['mulai']) : null;
        $end = isset($data['selesai']) ? \Carbon\Carbon::parse($data['selesai']) : null;

        if ($end && $now->gt($end->endOfDay())) {
            return 'Kadaluwarsa';
        } elseif ($start && $now->lt($start)) {
            return 'Dijadwalkan';
        } elseif (isset($data['is_published']) && $data['is_published'] == 1) {
            return 'Aktif';
        } else {
            return 'Tidak Aktif';
        }
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
