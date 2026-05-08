<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\StatusKerjasama;

// use Modules\Kerjasama\Models\PengisianPanduan;

class KerjasamaManagementService
{
    /**
     * @var Kerjasama
     */
    protected $model = Kerjasama::class;
    protected StatusKerjasamaManagementService $statusKerjasamaService;
    protected KegiatanManagementService $kegiatanService;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Kerjasama;
        $this->statusKerjasamaService = new StatusKerjasamaManagementService;
        $this->kegiatanService = new KegiatanManagementService;
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
    public function indexForMitra(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = [], int $idMitra): mixed
    {
        $filter = [
            ...$filter,
            [
                'field' => 'k.id_mitra',
                'value' => $idMitra
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
     * @return Kerjasama
     */
    public function show(string $id): Kerjasama|Error
    {
        try {
            $model = $this
                ->model
                ->with([
                    'pihak_penanggung_jawab',
                    'pihak_penanggung_jawab.penanggung_jawab',
                    'dokumenKerjasama'
                ])
                ->where('id', $id)
                ->whereNull('id_parent')
                ->firstOrFail();

            $model->realisasi = $this->kegiatanService->getTotalRealisasi($model->id);
            $model->dokumenKerjasama = $model->dokumenKerjasama->map(function ($dokumenKerjasama) {
                return [
                    ...$dokumenKerjasama->toArray(),
                    ...$dokumenKerjasama->dokumen->toArray(),
                    'nama_dokumen' => $dokumenKerjasama->dokumen->nama_dokumen,
                    'lampiran' => $dokumenKerjasama->dokumen->lastVersionTemporaryUrl(),
                    'ukuran' => $dokumenKerjasama->dokumen->lastVersionSize
                ];
            });
        } catch (\Throwable $th) {
            return new Error(code: 404);
        }

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Kerjasama
     */
    public function store(array $data): Kerjasama|Error
    {
        DB::beginTransaction();

        $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_DATA);

        if (!empty($upload) && Error::isError($upload->getError())) {
            return $upload->getError();
        }

        if (!empty($upload)) {
            $data['id_dokumen'] = $upload->get()?->id;
        }

        try {
            $model = $this->model->create($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Execute upload
        if (!empty($upload)) {
            $upload->executeUpload();
        }

        if (!empty($upload) && Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        DB::commit();

        return $model;
    }


    public function storeImport(array $data): Kerjasama|Error
    {
        Kerjasama::unguard();

        $importRules = [
            'id_mitra' => 'required|integer',
            'judul_kerjasama' => 'required|string|max:255',
            'id_unit_kerja' => 'required|integer|',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai_berlaku' => 'required',
            'tanggal_akhir_berlaku' => 'required|after:tanggal_mulai_berlaku',
            'id_jenis_dokumen' => 'required|integer',
            'nomor_dokumen' => 'nullable|max:255',
            'nomor_dokumen_mitra' => 'nullable|string|max:255',
            'id_status_kerjasama' => 'required|integer',
            'id_dokumen' => 'nullable|integer',
            'id_sumber_dana' => 'nullable|integer|exists:sumber_dana,id',
            'anggaran' => 'nullable|numeric|max:9999999999999999999',
            'hasil_pelaksanaan' => 'nullable|string',
        ];

        $validator = Validator::make($data, $importRules);
        
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(', ', $errors);
            Log::error('Validation failed in storeImport', [
                'data' => $data,
                'errors' => $errors
            ]);
            return new Error('Validation failed: ' . $errorMessage);
        }

        DB::beginTransaction();

        try {
            $model = new Kerjasama();
            $model->fill($data);
            $model->saveQuietly(); 
            
            Log::info('Kerjasama created successfully in storeImport', ['id' => $model->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in storeImport', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return new Error('Database error: ' . $e->getMessage(), exception: $e);
        }

        DB::commit();
        Kerjasama::reguard();

        return $model;
    }
    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Kerjasama
     */
    public function update(array $data, int $id): Kerjasama|Error
    {
        DB::beginTransaction();
        $model = $this->model->findOrFail($id);
        $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_DATA, $model);

        if (!empty($upload)) {
            $data['id_dokumen'] = $upload->get()?->id;
        } else {
            unset($data['id_dokumen']);
        }

        $model->update($data);

        if (!empty($upload) && Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        // Execute upload
        $upload?->executeUpload();

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
        if ($this->checkReference($id)) {
            return new Error([
                'Oops.. Terjadi Kesalahan',
                'Data tidak bisa dihapus karena sudah digunakan sebagai referensi ' . __('kerjasama::kegiatan.main') . '.',
                'double'
            ]);
        }

        [$isError, $errorMessage] = $this->statusKerjasamaValidationOnDelete($id);

        if ($isError) {
            return new Error($errorMessage);
        }


        $model = $this->model->with(['status_kerjasama'])->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
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

        if ($this->checkReferenceMultiple($ids)) {
            return new Error([
                'Oops.. Terjadi Kesalahan',
                'Data tidak bisa dihapus karena sudah digunakan sebagai referensi ' . __('kerjasama::kegiatan.main') . '.',
                'double'
            ]);
        }

        [$isError, $errorMessage] = $this->statusKerjasamaValidationOnDelete($ids);

        if ($isError) {
            return new Error($errorMessage);
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
     * Report
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function report(array $order = [], array $filter = []): mixed
    {
        $startDate = null;
        $endDate = null;
        foreach ($filter as $key => $value) {
            if ($value['field'] == 'tanggal_mulai_berlaku') {
                $startDate = $value['value'];
                unset($filter[$key]);
            } else if ($value['field'] == 'tanggal_akhir_berlaku') {
                $endDate = $value['value'];
                unset($filter[$key]);
            }
        }

        $table = $this->model->getTable();
        $sql = "
            select
                k.*,
                m.nama_mitra as id_mitra,
                jd.jenis_dokumen as id_jenis_dokumen,
                sk.status_kerjasama AS id_status_kerjasama,
                concat_ws(' - ', jp.kode_jenjang, uk.nama_unit) AS id_unit_kerja,
                sd.sumber_dana AS id_sumber_dana,
                sum(kg.anggaran) AS realisasi
            from $table k
                left join kerjasama.mitra m on k.id_mitra = m.id
                left join kerjasama.jenis_dokumen jd on k.id_jenis_dokumen = jd.id
                left join kerjasama.status_kerjasama sk on k.id_status_kerjasama = sk.id
                left join core.unit_kerja uk on k.id_unit_kerja = uk.id
                left join core.jenjang_pendidikan jp on jp.id = uk.id_jenjang_pendidikan
                left join kerjasama.sumber_dana sd on sd.id = k.id_sumber_dana
                left join kerjasama.kegiatan kg on kg.id_induk_kerjasama = k.id
        ";

        if ($startDate) {
            $filter[] = [
                'field' => 'k.tanggal_akhir_berlaku',
                'operator' => '>=',
                'value' => $startDate
            ];
        }
        if ($endDate) {
            $filter[] = [
                'field' => 'k.tanggal_mulai_berlaku',
                'operator' => '<=',
                'value' => $endDate
            ];
        }

        [$sql, $bindings] = $this->defineIndexQuery(
            filter: $filter,
            order: $order,
            customSql: $sql,
            customFieldMap: [
                'id_status_kerjasama' => 'sk.id',
                'id_unit_kerja' => 'uk.id',
                'id_jenis_dokumen' => 'jd.id',
            ],
            groupBy: "k.id, m.nama_mitra, jd.jenis_dokumen, sk.status_kerjasama, jp.kode_jenjang, uk.nama_unit, sd.sumber_dana"
        );

        return array_map(function ($value) {
            return (array) $value;
        }, DB::select($sql, $bindings) ?? []);
    }
    public function getCountByStatus(): array|null
    {
        $sql = "
            SELECT
                sk.status_kerjasama,
                COUNT(k.id) as total
            FROM
                kerjasama.status_kerjasama sk
            LEFT JOIN
                kerjasama.kerjasama k ON
                    sk.id = k.id_status_kerjasama
                        AND k.waktu_dihapus IS NULL
            WHERE
                sk.waktu_dihapus is null
            AND
                k.id_parent is null
            GROUP BY
                sk.status_kerjasama;
        ";
        $data = DB::select($sql);
        foreach ($data as $key => $value) {
            $data[$value->status_kerjasama] = $value->total;
            unset($data[$key]);
        }
        return $data;
    }

    public function getUnitKerjaOptions()
    {
        $unitKerja = DB::table('core.unit_kerja as uk')
            ->leftJoin('core.jenjang_pendidikan as jp', 'jp.id', '=', 'uk.id_jenjang_pendidikan')
            ->select('uk.id', 'jp.kode_jenjang', 'uk.nama_unit', 'uk.info_left')
            ->orderBy('uk.info_left', 'asc')
            ->get();

        $unitKerjaOptions = [];
        foreach ($unitKerja as $item) {
            $unitKerjaOptions[$item->id] = !empty($item->kode_jenjang) ? $item->kode_jenjang . ' - ' . $item->nama_unit : $item->nama_unit;
        }

        return $unitKerjaOptions;
    }

    public function getIndukKerjasamaOptions(bool $withFilterStatusKerjasama = true)
    {
        return $this->model
            ->whereNull('waktu_dihapus')
            ->whereNull('id_parent')
            ->when($withFilterStatusKerjasama, function ($query) {
                $statusKerjasama = StatusKerjasama::whereIn('status_kerjasama', [
                    StatusKerjasama::AKTIF,
                    StatusKerjasama::PERPANJANG
                ])
                    ->get('id')
                    ->toArray();

                $query->whereIn('id_status_kerjasama', $statusKerjasama);
            })
            ->get(['id', 'judul_kerjasama'])
            ->pluck('judul_kerjasama', 'id')
            ->toArray();
    }

    public function isIndukKerjasamaExists($id)
    {
        return $this->model
            ->where('id', $id)
            ->whereNull('waktu_dihapus')
            ->whereNull('id_parent')
            ->exists();
    }

    protected function defineIndexQuery(array $filter = [], array $order = [], string $customSql = null, array $customFieldMap = [], string $groupBy = null): array
    {
        $table = $this->model->getTable();
        $sql = "select
            k.*,
            m.nama_mitra as id_mitra,
            jd.jenis_dokumen as id_jenis_dokumen,
            sk.status_kerjasama AS id_status_kerjasama,
            concat_ws(' - ', jp.kode_jenjang, uk.nama_unit) AS id_unit_kerja,
            sd.sumber_dana AS id_sumber_dana,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM kerjasama.dokumen_kerjasama dk 
                    WHERE dk.id_kerjasama = k.id 
                    AND dk.waktu_dihapus IS NULL
                ) THEN true 
                ELSE false 
            END as has_dokumen
        from $table k
        left join kerjasama.mitra m on k.id_mitra = m.id
        left join kerjasama.jenis_dokumen jd on k.id_jenis_dokumen = jd.id
        left join kerjasama.status_kerjasama sk on k.id_status_kerjasama = sk.id
        left join core.unit_kerja uk on k.id_unit_kerja = uk.id
        left join core.jenjang_pendidikan jp on jp.id = uk.id_jenjang_pendidikan
        left join kerjasama.sumber_dana sd on sd.id = k.id_sumber_dana
        ";
        
        $sql = !empty($customSql) ? $customSql : $sql;
        $fieldMap = [
            'id_mitra' => 'm.id',
            'id_jenis_dokumen' => 'jd.jenis_dokumen',
            'id_status_kerjasama' => 'sk.id',
            'id_unit_kerja' => 'uk.nama_unit',
            'tanggal_mulai_berlaku' => "CONCAT(k.tanggal_mulai_berlaku::text, ' - ', k.tanggal_akhir_berlaku::text)",
            'id_sumber_dana' => 'sd.sumber_dana',
            'has_dokumen' => 'has_dokumen',
            ...$customFieldMap
        ];

        $defaultFilter = "k.waktu_dihapus IS NULL AND k.id_parent IS NULL";
        $defaultOrder = "k.tanggal_mulai_berlaku desc";

        foreach ($filter as $index => $item) {
            if (!empty($item['field']) && $item['field'] == 'tanggal_akhir_berlaku' && empty($item['operator'])) {
                $filter[$index] = [
                    ...$item,
                    'operator' => '<='
                ];
            }
        }
        return Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            defaultOrder: $defaultOrder,
            fieldMap: $fieldMap,
            groupBy: $groupBy
        );
    }

    /**
     * Proses upload document ke DMS.
     *
     * @param array $data
     * @param string $field
     * @param string $folderCode
     * @param Kerjasama|null $model
     * @return UploadDokumen|null|Error
     */
    private function processUploadDocument(array $data, string $field, string $folderCode, Kerjasama $model = null): UploadDokumen|null|Error
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

    protected function statusKerjasamaValidationOnDelete(mixed $ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }


        $data = DB::table('kerjasama.kerjasama', 'k')
            ->leftJoin("kerjasama.status_kerjasama as sk", 'k.id_status_kerjasama', '=', "sk.id")
            ->whereIn('k.id', $ids)
            ->get(['sk.status_kerjasama', 'k.nomor_dokumen']);

        foreach ($data as $item) {
            if ($item->status_kerjasama != StatusKerjasama::DRAFT) {
                return [
                    true,
                    ["Oops.. Terjadi Kesalahan", "Hanya bisa menghapus data kerjasama dengan status 'Draft'", "double"]
                ];
            }
        }

        return [false, null];
    }

    protected function checkReference($id)
    {
        $isReferenceKegiatan = Kegiatan::where('id_induk_kerjasama', $id)->exists();
        return $isReferenceKegiatan;
    }

    protected function checkReferenceMultiple($ids)
    {
        $isReferenceKegiatan = Kegiatan::whereIn('id_induk_kerjasama', $ids)->exists();
        return $isReferenceKegiatan;
    }
}
