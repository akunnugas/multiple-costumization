<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Illuminate\Support\Facades\DB;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\Kegiatan;
use Str;

class KegiatanManagementService
{
    /**
     * @var Kegiatan
     */
    protected $model = Kegiatan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Kegiatan;
    }

    protected function defineIndexQuery(array $filter = [], array $order = []): array
    {
        $table = $this->model->getTable();
        $sql = "select 
                    k.*, 
                    m.nama_mitra as id_mitra,
                    concat_ws(' - ', jp.kode_jenjang, uk.nama_unit) AS id_unit_kerja,
                    ik.judul_kerjasama as id_induk_kerjasama
                from $table k
                    left join kerjasama.kerjasama ik on ik.id = k.id_induk_kerjasama
                    left join kerjasama.mitra m on ik.id_mitra = m.id
                    left join core.unit_kerja uk on k.id_unit_kerja = uk.id
                    left join core.jenjang_pendidikan jp on jp.id = uk.id_jenjang_pendidikan    
                ";

        $fieldMap = [
            'judul_kegiatan' => 'k.judul_kegiatan',
            'nomor_dokumen' => 'k.nomor_dokumen',
            'id_mitra' => 'm.id',
            'id_unit_kerja' => 'uk.id',
            'tanggal_mulai_berlaku' => "CONCAT(k.tanggal_mulai_berlaku::text, ' - ', k.tanggal_akhir_berlaku::text)",
            'id_induk_kerjasama' => "ik.id",
            'anggaran' => "k.anggaran::text"
        ];

        $defaultFilter = "k.waktu_dihapus IS NULL AND k.id_induk_kerjasama IS NOT NULL";
        $defaultOrder = "k.tanggal_mulai_berlaku desc";

        foreach ($filter as $index => $item) {
            if (!empty($item['field']) && $item['field'] == 'tanggal_akhir_berlaku') {
                $filter[$index] = [
                    ...$item,
                    'operator' => '<='
                ];
            }
        }

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
     * @return Kegiatan
     */
    public function show(int $id): Kegiatan
    {
        $data = $this
            ->model
            ->with([
                'pihak_penanggung_jawab.penanggung_jawab',
                'indukKerjasama',
                'sasaranKinerja.indikator',
                'dokumenKegiatan',
                'pelaksanaKegiatan',
                'pelaksanaKegiatan.mahasiswa',                
            ])
            ->where('id', $id)
            ->whereNotNull('id_induk_kerjasama')
            ->firstOrFail();

        $unitKerjaOptions = (new KerjasamaManagementService)->getUnitKerjaOptions();
        $data->dokumenKegiatan = $data->dokumenKegiatan->map(function ($dokumenKegiatan) {
            return [
                ...$dokumenKegiatan->toArray(),
                ...$dokumenKegiatan->dokumen->toArray(),
                'nama_dokumen' => $dokumenKegiatan->dokumen->nama_dokumen,
                'lampiran' => $dokumenKegiatan->dokumen->lastVersionTemporaryUrl(),
                'ukuran' => $dokumenKegiatan->dokumen->lastVersionSize
            ];
        });

        $data->pelaksanaKegiatan->each(function($pelaksana) use ($unitKerjaOptions) {
            $pelaksana->id_mahasiswa = $pelaksana->mahasiswa->nim . ' - ' . $pelaksana->mahasiswa->nama_mahasiswa;
            $pelaksana->unit_kerja = $unitKerjaOptions[$pelaksana->mahasiswa->id_unit];
        });

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Kegiatan
     */
    public function store(array $data): Kegiatan|Error
    {
        DB::beginTransaction();

        $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_KEGIATAN);
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

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Kegiatan
     */
    public function update(array $data, int $id): Kegiatan|Error
    {
        DB::beginTransaction();
        $model = $this->model->findOrFail($id);
        if (array_key_exists('nomor_dokumen', $data) && empty($data['nomor_dokumen'])) {
            $data['nomor_dokumen'] = null;
        }
        $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_KEGIATAN, $model);

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
            ->when(!empty($exceptIds), function($query) use ($exceptIds) {
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
