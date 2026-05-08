<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Error;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\TargetSkor;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use Modules\SPMI\Models\TargetIndikator;
use Illuminate\Support\Facades\Validator;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Export\ExportPenilaianMatriksError;
use Modules\SPMI\Services\MappingPenilaianMatriksManagementService;

class PenilaianMatriksIKTManagementService
{
    /**
     * @var PenilaianMatriks
     */
    protected $model = PenilaianMatriks::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianMatriks;
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
        $filter[] = [
            'field' => 'apakah_data_default',
            'value' => 'IKT',
        ];

        $sql = "SELECT
                    pm.id,
                    pm.pertanyaan_penilaian,
                    pm.kategori_penilaian,
                    pm.jenis_penilaian,
                    pm.referensi_penilaian,
                    pm.bobot_penilaian,
                    pm.apakah_data_default,
                    pm.apakah_aktif AS apakah_aktif_matriks,
                    pm.info_level
                FROM " . $this->model->getTable() . " pm";

        // Defaultnya order berdasarkan info left
        if (!empty($order) && $order['no'] == 1) {
            $order = ['field' => 'pm.info_left', 'direction' => 'asc', 'desc' => false];
        }

        // Remove filter if empty
        foreach ($filter as $key => $obj) {
            if (!isset($obj['field'])) {
                continue;
            }
            if ($obj['field'] == 'jenis_penilaian' && $obj['value'] == 1) {
                unset($filter[$key]);
            }
        }

        $fieldMap = [
            'referensi_penilaian' =>
            "CASE WHEN pm.referensi_penilaian = 'pr' THEN 'Laporan Kinerja'
                WHEN pm.referensi_penilaian = 'se' THEN 'Evaluasi Diri' END",
            'bobot_penilaian' => 'pm.bobot_penilaian::text',
            'apakah_aktif_matriks' => "CASE WHEN pm.apakah_aktif THEN 'Aktif' ELSE 'Tidak Aktif' END",
            'apakah_data_default' => 'CASE WHEN pm.apakah_data_default = true THEN \'IKU\' ELSE \'IKT\' END',
        ];

        $defaultFilter = "pm.waktu_dihapus is null";

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
     * Menampilkan semua matrix berdasarkan assessment guide.
     *
     * @param int $assessmentGuideId
     * @return mixed
     */
    public function showAllMatricesByPenilaianPanduan(int $assessmentGuideId): mixed
    {
        $table = $this->model->getTable();
        $sql = "SELECT
                    pm.id,
                    pm.nomor_penilaian,
                    pm.pertanyaan_penilaian,
                    pm.kategori_penilaian,
                    pm.id_parent,
                    pm.apakah_data_default,
                    pm.info_level,
                    JSON_AGG(
                        CASE WHEN pmp.id_penilaian_matriks IS NOT NULL THEN
                            json_build_object(
                                'id', pmp.id,
                                'nilai', pmp.nilai,
                                'deskripsi', pmp.deskripsi,
                                'apakah_nonaktif', pmp.apakah_nonaktif
                            )
                        ELSE null END
                    ) skor_indikator
                FROM $table pm
                LEFT JOIN spmi.penilaian_matriks_predikat pmp ON pmp.id_penilaian_matriks = pm.id
                    AND pmp.waktu_dihapus is null
                WHERE pm.waktu_dihapus is null AND pm.id_penilaian_panduan = ?
                GROUP BY pm.id, pm.pertanyaan_penilaian, pm.kategori_penilaian,
                    pm.info_left, pmp.id_penilaian_matriks
                ORDER BY pm.info_left ASC";

        $data = DB::select($sql, [$assessmentGuideId]);

        return $data;
    }

    public function showMatrixScores(int $PenilaianMatriksId): mixed
    {
        $sql = "SELECT
                    pmp.id,
                    skmpp.nilai,
                    pmp.rumus_penilaian,
                    pmp.kriteria,
                    pmp.deskripsi,
                    pmp.apakah_nonaktif
                FROM spmi.penilaian_matriks_predikat pmp
                JOIN spmi.skor_matriks_predikat_penilaian skmpp
                    ON skmpp.id = pmp.id_skor_matriks_predikat_penilaian
                WHERE pmp.id_penilaian_matriks = ?
                    AND pmp.waktu_dihapus is null
                ORDER BY skmpp.nilai DESC";

        $data = DB::select($sql, [$PenilaianMatriksId]);

        return $data;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PenilaianMatriks
     */
    public function show(int $id): PenilaianMatriks
    {
        $model = $this->model->findOrFail($id);
        $this->setHakAksesIKT($model);

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PenilaianMatriks|Error
     */
    public function store(array $data): PenilaianMatriks|Error
    {
        if (empty($data['jenis_penilaian'])) {
            return new Error('Jenis Penilaian tidak boleh kosong.');
        }

        DB::beginTransaction();

        $panduan = PenilaianPanduan::find($data['id_penilaian_panduan']);

        if ($panduan->apakah_data_default) {
            $listC10 = ['IAPS-S1', 'IAPS-D4', 'IAPS-PROF', 'IAPS-D3', 'IAPS-S3', 'IAPS-S2'];
            $nomorPenilaian = in_array($panduan->kode_penilaian_panduan, $listC10) ? "C.10" : "IKT";
            $matriksPenilaianElemenTambahan = PenilaianMatriks::where('id_penilaian_panduan', $data['id_penilaian_panduan'])->where('nomor_penilaian', "C.10")->first();

            if (empty($matriksPenilaianElemenTambahan)) {
                $matriksPenilaianElemenTambahan = new PenilaianMatriks();
                $matriksPenilaianElemenTambahan->id_penilaian_panduan = $data['id_penilaian_panduan'];
                $matriksPenilaianElemenTambahan->id_parent = null;
                $matriksPenilaianElemenTambahan->nomor_penilaian = $nomorPenilaian;
                $matriksPenilaianElemenTambahan->kategori_penilaian = PenilaianMatriks::CATEGORY_ELEMENT;
                $matriksPenilaianElemenTambahan->pertanyaan_penilaian = "Indikator Tambahan";
                $matriksPenilaianElemenTambahan->jenis_penilaian = PenilaianMatriks::TYPE_FINAL_SCORE;
                $matriksPenilaianElemenTambahan->apakah_nilai_ditampilkan = true;
                $matriksPenilaianElemenTambahan->apakah_data_default = false;
                $matriksPenilaianElemenTambahan->apakah_aktif = 1;
                $matriksPenilaianElemenTambahan->save();
            }

            $data['id_parent'] = $matriksPenilaianElemenTambahan->id;
        }

        if (empty($data['id_parent'])) {
            $data['id_parent'] = null;
        }

        try {
            // validasi nomor_penilaian tidak boleh sama jika sudah ada
            $isHasDuplicateNomor = PenilaianMatriks::where('id_penilaian_panduan', $data['id_penilaian_panduan'])
                ->where('nomor_penilaian', $data['nomor_penilaian'])
                ->where('waktu_dihapus', null)
                ->exists();

            if ($isHasDuplicateNomor) {
                return new Error('Nomor butir sudah ada sebelumnya.');
            }

            $model = $this->model->create($data);

            if (isset($data['program_studi']) && isset($data['periode_ami'])) {
                $mappingPenilaian = [];
                foreach ($data['periode_ami'] as $periode) {
                    foreach ($data['program_studi'] as $unit) {
                        $mappingPenilaian[] = [
                            'id_penilaian_matriks' => $model->id,
                            'id_audit_periode' => $periode,
                            'id_unit' => $unit,
                        ];
                    }
                }

                MappingPenilaianMatriks::insert($mappingPenilaian);
            }

            $this->addReferenceItems($data['butir_akreditasi'] ?? [], $model->id, $data['referensi_penilaian']);

            // Update total indikator matrix panduan penilaian
            if ($data['kategori_penilaian'] == PenilaianMatriks::CATEGORY_INDICATOR) {
                $countIndicator = PenilaianMatriks::where('id_penilaian_panduan', $model->id_penilaian_panduan)
                    ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
                    ->where('apakah_aktif', true)
                    ->count();
                $assessmentGuide = PenilaianPanduan::findOrFail($model->id_penilaian_panduan);
                $assessmentGuide->total_indikator_matriks = $countIndicator;
                $assessmentGuide->save();
            }
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
     * @return PenilaianMatriks|Error
     */
    public function update(array $data, int $id): PenilaianMatriks|Error
    {
        if (empty($data['jenis_penilaian'])) {
            return new Error('Jenis Penilaian tidak boleh kosong.');
        }

        $model = $this->model->findOrFail($id);
        if ($model->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR) {
            if ($model->kategori_penilaian != $data['kategori_penilaian']) {
                $checkSkorReference = $this->checkIsOnReference($id);

                if (Error::isError($checkSkorReference)) {
                    return $checkSkorReference;
                }
            }
        }

        DB::beginTransaction();

        if (empty($data['id_parent'])) {
            $data['id_parent'] = null;
        }

        $recentIsActive = $model->apakah_aktif;

        try {
            // validasi nomor_penilaian tidak boleh sama jika sudah ada
            $isHasDuplicateNomor = PenilaianMatriks::where('id_penilaian_panduan', $data['id_penilaian_panduan'])
                ->where('nomor_penilaian', $data['nomor_penilaian'])
                ->where('waktu_dihapus', null)
                ->where('id', '!=', $id)
                ->exists();
            if ($isHasDuplicateNomor) {
                return new Error('Nomor butir sudah ada sebelumnya.');
            }

            if (empty($data['id_akreditasi_standar'])) {
                $data['id_akreditasi_standar'] = null;
            }

            $model->update($data);

            if (isset($data['program_studi']) && isset($data['periode_ami']) && !empty($data['program_studi']) && !empty($data['periode_ami'])) {
                MappingPenilaianMatriks::where('id_penilaian_matriks', $model->id)
                    ->whereNotIn('id_unit', $data['program_studi'])
                    ->delete();

                MappingPenilaianMatriks::where('id_penilaian_matriks', $model->id)
                    ->whereNotIn('id_audit_periode', $data['periode_ami'])
                    ->delete();

                $exists_mapping = MappingPenilaianMatriks::where(function ($query) use ($data) {
                    $query->whereIn('id_audit_periode', $data['periode_ami'])
                        ->whereIn('id_unit', $data['program_studi']);
                })->get();

                $mappingPenilaian = [];
                foreach ($data['periode_ami'] as $periode) {
                    foreach ($data['program_studi'] as $unit) {
                        if ($exists_mapping->where('id_penilaian_matriks', $id)->where('id_audit_periode', $periode)->where('id_unit', $unit)->first()) {
                            continue;
                        }

                        $mappingPenilaian[] = [
                            'id_penilaian_matriks' => $model->id,
                            'id_audit_periode' => $periode,
                            'id_unit' => $unit,
                        ];
                    }
                }

                MappingPenilaianMatriks::insert($mappingPenilaian);
            } else {
                MappingPenilaianMatriks::where('id_penilaian_matriks', $model->id)->delete();
            }

            $this->addReferenceItems($data['butir_akreditasi'] ?? [], $model->id, $data['referensi_penilaian']);
            $model->apakah_status_aktif_berubah = $data['apakah_aktif'] != $recentIsActive;

            if ($model->kategori_penilaian == PenilaianMatriks::CATEGORY_ELEMENT) {
                $updateChildStatus = $this->updateChildrenActiveStatus($model->toArray());

                if ($updateChildStatus instanceof Error) {
                    DB::rollBack();
                    return $updateChildStatus;
                }
            }

            if ($data['apakah_aktif'] != $recentIsActive) {
                $countIndicator = PenilaianMatriks::where('id_penilaian_panduan', $model->id_penilaian_panduan)
                    ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
                    ->where('apakah_aktif', true)
                    ->count();
                $assessmentGuide = PenilaianPanduan::findOrFail($model->id_penilaian_panduan);
                $assessmentGuide->total_indikator_matriks = $countIndicator;
                $assessmentGuide->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $model;
    }

    public function customDetailPage($id)
    {
        $matrik = PenilaianMatriks::findOrFail($id);
        $penilaianMatriksScores = PenilaianMatriksPredikat::join('spmi.skor_matriks_predikat_penilaian as skmpp', 'skmpp.id', '=', 'spmi.penilaian_matriks_predikat.id_skor_matriks_predikat_penilaian')
            ->where('spmi.penilaian_matriks_predikat.id_penilaian_matriks', $id)
            ->orderBy('skmpp.nilai', 'desc')
            ->get(['spmi.penilaian_matriks_predikat.*', 'skmpp.nilai']);
        $skorMatriksPredikat = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $matrik->id_penilaian_panduan)
            ->orderBy('nilai', 'asc')
            ->pluck('deskripsi', 'nilai')
            ->toArray();
        return [
            'PenilaianMatriksScores' => $penilaianMatriksScores,
            'scoreOptions' => $skorMatriksPredikat
        ];
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id)
    {
        $model = $this->model->findOrFail($id);
        $penilaianPanduan = PenilaianPanduan::find($model->id_penilaian_panduan);

        if ($penilaianPanduan->apakah_data_default && $model->nomor_penilaian === PenilaianMatriks::CODE_INDIKATOR_TAMBAHAN) {
            return new Error('Elemen butir C.10 tidak dapat dihapus.');
        }

        if (MappingPenilaianMatriks::where('id_penilaian_matriks', $id)->exists()) {
            return new Error('Data butir tidak dapat dihapus karena sedang digunakan pada pemetaan penilaian matriks.');
        }

        $checkReference = $this->checkIsOnReference($id);
        if (Error::isError($checkReference)) {
            return $checkReference;
        }

        DB::beginTransaction();

        try {
            // delete scores
            PenilaianMatriksPredikat::where('id_penilaian_matriks', $model->id)->delete();

            // Hapus semua children
            PenilaianMatriks::where('info_left', '>=', $model->info_left)
                ->where('info_right', '<=', $model->info_right)
                ->delete();

            $model->destroy($model->id);

            // Update total indikator matrix panduan penilaian
            $countIndicator = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
                ->where('apakah_aktif', true)
                ->count();
            $assessmentGuide = PenilaianPanduan::findOrFail($penilaianPanduan->id);
            $assessmentGuide->total_indikator_matriks = $countIndicator;
            $assessmentGuide->save();

            if ($penilaianPanduan->apakah_data_default) {
                $this->revalidateBobotIndikator();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return new Error('Gagal menghapus butir, silahkan coba lagi.');
        }

        DB::commit();
    }

    // FIXME: Ini harus diperbaiki ketika ada panduan baru selain IAPS
    private function revalidateBobotIndikator()
    {
        $totalChilds = PenilaianMatriks::where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
            ->where('apakah_data_default', false)
            ->count();

        if (empty($totalChilds)) {
            IndikatorBobot::where('jenis_indikator_bobot', IndikatorBobot::TYPE_IKU)->update(['persentase' => 100]);
            IndikatorBobot::where('jenis_indikator_bobot', IndikatorBobot::TYPE_IKT)->update(['persentase' => 0]);
        }
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|void
     */
    public function destroySome($ids)
    {
        $models = $this->model->whereIn('id', $ids)->get();

        if ($models->isEmpty()) {
            return;
        }

        $nomorPenilaianPluck = $models->pluck('nomor_penilaian')->toArray();

        if (in_array(PenilaianMatriks::CODE_INDIKATOR_TAMBAHAN, $nomorPenilaianPluck)) {
            return new Error('Elemen butir C.10 tidak dapat dihapus.');
        }

        $penilaianPanduan = PenilaianPanduan::find($models->first()?->id_penilaian_panduan);
        if (empty($penilaianPanduan)) {
            return new Error('Gagal menghapus butir, silahkan coba lagi.');
        }

        if (MappingPenilaianMatriks::whereIn('id_penilaian_matriks', $ids)->exists()) {
            return new Error('Beberapa data butir tidak dapat dihapus karena sedang digunakan pada pemetaan penilaian matriks.');
        }

        DB::beginTransaction();

        try {
            $isReferenceOnTargetSkor = $this->checkIsOnReference($ids);
            if (Error::isError($isReferenceOnTargetSkor)) {
                return $isReferenceOnTargetSkor;
            }

            PenilaianMatriksPredikat::whereIn('id_penilaian_matriks', $ids)->delete();

            // Hapus semua children
            foreach ($models as $model) {
                PenilaianMatriks::where('info_left', '>=', $model->info_left)
                    ->where('info_right', '<=', $model->info_right)
                    ->delete();
            }
            $this->model->destroy($ids);

            $assessmentGuide = PenilaianPanduan::findOrFail($penilaianPanduan->id);
            $assessmentGuide->total_indikator_matriks = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
                ->where('apakah_aktif', true)
                ->count();
            $assessmentGuide->save();

            if ($penilaianPanduan->apakah_data_default) {
                $this->revalidateBobotIndikator();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return new Error('Gagal menghapus butir, silahkan coba lagi.');
        }

        DB::commit();
    }

    public function importFromExcelNonDefault(array $data, int $idPenilaianPanduan, array $unitKerjaIds, array $periodIds, string $uploadedFilePath)
    {
        list($success, $data) = $this->initImportData($data, $unitKerjaIds, $periodIds, $idPenilaianPanduan, false);
        if (!$success) {
            return [false, $data];
        }

        list($success, $standarIds, $errorData) = $this->validateImportData($data, $idPenilaianPanduan, false);
        if (!$success) {
            $errorFile = $this->saveErrorFile($uploadedFilePath, $errorData, false);
            session()->flash('import_error_file', base64_encode($errorFile));

            return [false, "Import data matriks IKT gagal. Silakan unduh file error untuk melihat detail kesalahan."];
        }

        $mappingPenilaian = DB::table('spmi.mapping_penilaian_matriks')
            ->select(
                'spmi.mapping_penilaian_matriks.id',
                'spmi.mapping_penilaian_matriks.id_penilaian_matriks',
                'spmi.mapping_penilaian_matriks.id_audit_periode',
                'spmi.mapping_penilaian_matriks.id_unit'
            )
            ->join('spmi.penilaian_matriks as pm', 'pm.id', '=', 'spmi.mapping_penilaian_matriks.id_penilaian_matriks')
            ->join('spmi.penilaian_panduan as pp', 'pp.id', '=', 'pm.id_penilaian_panduan')
            ->when(
                is_array($unitKerjaIds) && count($unitKerjaIds) > 0,
                function ($query) use ($unitKerjaIds) {
                    $query->whereIn('id_unit', $unitKerjaIds);
                }
            )->when(is_array($periodIds) && count($periodIds) > 0, function ($query) use ($periodIds) {
                $query->whereIn('id_audit_periode', $periodIds);
            })
            ->where('pp.id', $idPenilaianPanduan)
            ->get();

        try {
            DB::beginTransaction();

            $penilaian_ids = [];

            $parentNumber = array_column($data, 1);
            $parentNumber = array_filter(array_unique($parentNumber));
            $parentMatrices = array_filter($data, function ($row) use ($parentNumber) {
                return in_array($row[0], $parentNumber);
            });
            $parentMatriceKeys = array_keys($parentMatrices);

            $savedParents = [];
            foreach ($parentMatrices as $index => $row) {
                $standarId = $standarIds[$index] ?? null;

                $penilaian = PenilaianMatriks::updateOrCreate([
                    'id_penilaian_panduan' => $idPenilaianPanduan,
                    'nomor_penilaian' => $row[0],
                ], [
                    'id_penilaian_panduan' => $idPenilaianPanduan,
                    'id_parent' => null,
                    'nomor_penilaian' => $row[0],
                    'kategori_penilaian' => PenilaianMatriks::CATEGORY_ELEMENT,
                    'pertanyaan_penilaian' => $row[2],
                    'id_akreditasi_standar' => $standarId ?? null,
                    'apakah_aktif' => $row[4] == 'Aktif' ? 1 : 0,
                    'jenis_penilaian' => PenilaianMatriks::TYPE_FINAL_SCORE,
                    'bobot_penilaian' => is_numeric($row[5]) ? (float) $row[5] : null,
                    'butir_indikator_spme' => $row[6] == 'Ya' ? 1 : 0,
                    'butir_indikator_iku' => $row[7] == 'Ya' ? 1 : 0,
                    'apakah_nilai_ditampilkan' => isset($row[8]) ? ($row[8] == 'Ya' ? 1 : 0) : 0,
                ]);

                $savedParents[$row[0]] = $penilaian->id;
                $penilaian_ids[] = $penilaian->id;
            }


            // Filter where not in parent matrice keys
            $indicatorMatrices = array_filter($data, function ($rowKey) use ($parentMatriceKeys) {
                return !in_array($rowKey, $parentMatriceKeys);
            }, ARRAY_FILTER_USE_KEY);
            foreach ($indicatorMatrices as $index => $row) {
                $standarId = $standarIds[$index] ?? null;

                // Get parent id from savedParents if exists
                $parentId = null;
                if (isset($savedParents[$row[1]])) {
                    $parentId = $savedParents[$row[1]];
                }

                $penilaian = PenilaianMatriks::updateOrCreate([
                    'id_penilaian_panduan' => $idPenilaianPanduan,
                    'nomor_penilaian' => $row[0],
                    'apakah_data_default' => false,
                ], [
                    'id_penilaian_panduan' => $idPenilaianPanduan,
                    'id_parent' => $parentId,
                    'nomor_penilaian' => $row[0],
                    'kategori_penilaian' => PenilaianMatriks::CATEGORY_INDICATOR,
                    'pertanyaan_penilaian' => $row[2],
                    'id_akreditasi_standar' => $standarId ?? null,
                    'apakah_aktif' => $row[4] == 'Aktif' ? 1 : 0,
                    'jenis_penilaian' => PenilaianMatriks::TYPE_QUALITATIVE,
                    'bobot_penilaian' => is_numeric($row[5]) ? (float) $row[5] : null,
                    'butir_indikator_spme' => $row[6] == 'Ya' ? 1 : 0,
                    'butir_indikator_iku' => $row[7] == 'Ya' ? 1 : 0,
                    'apakah_nilai_ditampilkan' => $row[8] == 'Ya' ? 1 : 0,
                ]);
                $penilaian_ids[] = $penilaian->id;

                $skorMatriks = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $idPenilaianPanduan)
                    ->orderBy('nilai', 'asc')
                    ->get();
                foreach ($skorMatriks as $index => $skor) {
                    PenilaianMatriksPredikat::updateOrCreate([
                        'id_penilaian_matriks' => $penilaian->id,
                        'id_skor_matriks_predikat_penilaian' => $skor->id,
                    ], [
                        'deskripsi' => $row[9 + $index] ?? '',
                        'apakah_nonaktif' => false,
                    ]);
                }
            }

            DB::commit();

            try {
                $mapping = new MappingPenilaianMatriksManagementService();
                foreach ($periodIds as $periodId) {
                    foreach ($unitKerjaIds as $unitId) {
                        $oldMapping = $mappingPenilaian
                            ->where('id_audit_periode', $periodId)
                            ->where('id_unit', $unitId)
                            ->pluck('id_penilaian_matriks')->toArray();
                        $merge_mapping = array_unique(array_merge($oldMapping, $penilaian_ids));

                        $mapping->update([
                            'checkedButir' => $merge_mapping,
                            'unitId' => $unitId,
                            'id_penilaian_panduan' => $idPenilaianPanduan,
                            'auditPeriodId' => $periodId,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                return [false, 'Import data matriks IKT gagal. ' . $e->getMessage()];
            }

            return [true, 'Import data matriks IKT berhasil.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, $e->getMessage()];
        }
    }

    public function importFromExcelDefault(array $data, int $idPenilaianPanduan, array $unitKerjaIds, array $periodIds, string $uploadedFilePath)
    {
        list($success, $data) = $this->initImportData($data, $unitKerjaIds, $periodIds, $idPenilaianPanduan);
        if (!$success) {
            return [false, $data];
        }

        list($success, $standarIds, $errorData) = $this->validateImportData($data, $idPenilaianPanduan);
        if (!$success) {
            $errorFile = $this->saveErrorFile($uploadedFilePath, $errorData, true);
            session()->flash('import_error_file', base64_encode($errorFile));
            return [false, "Import data matriks IKT gagal. Silakan unduh file error untuk melihat detail kesalahan."];
        }

        try {
            DB::beginTransaction();

            $matriksPenilaianElemenTambahan = PenilaianMatriks::where('id_penilaian_panduan', $idPenilaianPanduan)->where('nomor_penilaian', "C.10")->first();

            if (empty($matriksPenilaianElemenTambahan)) {
                $matriksPenilaianElemenTambahan = new PenilaianMatriks();
                $matriksPenilaianElemenTambahan->id_penilaian_panduan = $idPenilaianPanduan;
                $matriksPenilaianElemenTambahan->id_parent = null;
                $matriksPenilaianElemenTambahan->nomor_penilaian = "C.10";
                $matriksPenilaianElemenTambahan->kategori_penilaian = "E";
                $matriksPenilaianElemenTambahan->pertanyaan_penilaian = "C.10. Indikator Tambahan";
                $matriksPenilaianElemenTambahan->jenis_penilaian = "SA";
                $matriksPenilaianElemenTambahan->apakah_nilai_ditampilkan = true;
                $matriksPenilaianElemenTambahan->apakah_data_default = false;
                $matriksPenilaianElemenTambahan->apakah_aktif = 1;
                $matriksPenilaianElemenTambahan->save();
            }

            $penilaian_ids = [];
            foreach ($data as $index => $row) {
                $standarId = $standarIds[$index] ?? null;

                // Get parent id from savedParents if exists
                $parentId = $matriksPenilaianElemenTambahan->id;

                $penilaian = PenilaianMatriks::updateOrCreate([
                    'id_penilaian_panduan' => $idPenilaianPanduan,
                    'nomor_penilaian' => $row[0],
                    'apakah_data_default' => false,
                ], [
                    'id_penilaian_panduan' => $idPenilaianPanduan,
                    'id_parent' => $parentId,
                    'nomor_penilaian' => $row[0],
                    'kategori_penilaian' => PenilaianMatriks::CATEGORY_INDICATOR,
                    'pertanyaan_penilaian' => $row[1],
                    'id_akreditasi_standar' => $standarId ?? null,
                    'apakah_aktif' => $row[3] == 'Aktif' ? 1 : 0,
                    'jenis_penilaian' => PenilaianMatriks::TYPE_QUALITATIVE,
                    'bobot_penilaian' => is_numeric($row[4]) ? (float) $row[4] : null,
                    'butir_indikator_spme' => false,
                    'butir_indikator_iku' => false,
                    'apakah_nilai_ditampilkan' => isset($row[5]) ? ($row[5] == 'Ya' ? 1 : 0) : 0,
                ]);
                $penilaian_ids[] = $penilaian->id;

                $skorMatriks = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $idPenilaianPanduan)
                    ->orderBy('nilai', 'asc')
                    ->get();
                foreach ($skorMatriks as $index => $skor) {
                    PenilaianMatriksPredikat::updateOrCreate([
                        'id_penilaian_matriks' => $penilaian->id,
                        'id_skor_matriks_predikat_penilaian' => $skor->id,
                    ], [
                        'deskripsi' => $row[6 + $index] ?? '',
                        'apakah_nonaktif' => false,
                    ]);
                }
            }

            DB::commit();

            try {
                $mappingPenilaian = DB::table('spmi.mapping_penilaian_matriks')
                    ->select(
                        'spmi.mapping_penilaian_matriks.id',
                        'spmi.mapping_penilaian_matriks.id_penilaian_matriks',
                        'spmi.mapping_penilaian_matriks.id_audit_periode',
                        'spmi.mapping_penilaian_matriks.id_unit'
                    )
                    ->join('spmi.penilaian_matriks as pm', 'pm.id', '=', 'spmi.mapping_penilaian_matriks.id_penilaian_matriks')
                    ->join('spmi.penilaian_panduan as pp', 'pp.id', '=', 'pm.id_penilaian_panduan')
                    ->when(
                        is_array($unitKerjaIds) && count($unitKerjaIds) > 0,
                        function ($query) use ($unitKerjaIds) {
                            $query->whereIn('id_unit', $unitKerjaIds);
                        }
                    )->when(is_array($periodIds) && count($periodIds) > 0, function ($query) use ($periodIds) {
                        $query->whereIn('id_audit_periode', $periodIds);
                    })
                    ->where('pp.id', $idPenilaianPanduan)
                    ->get();

                $mapping = new MappingPenilaianMatriksManagementService();
                foreach ($periodIds as $periodId) {
                    foreach ($unitKerjaIds as $unitId) {
                        $oldMapping = $mappingPenilaian
                            ->where('id_audit_periode', $periodId)
                            ->where('id_unit', $unitId)
                            ->pluck('id_penilaian_matriks')->toArray();
                        $merge_mapping = array_unique(array_merge($oldMapping, $penilaian_ids));

                        $mapping->update([
                            'checkedButir' => $merge_mapping,
                            'unitId' => $unitId,
                            'id_penilaian_panduan' => $idPenilaianPanduan,
                            'auditPeriodId' => $periodId,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                return [false, 'Import data matriks IKT gagal. ' . $e->getMessage()];
            }

            return [true, 'Import data matriks IKT berhasil.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, $e->getMessage()];
        }
    }

    private function initImportData(array $data, array $unitKerjaIds, array $periodIds, int $idPenilaianPanduan, bool $isDefault = true)
    {
        if (empty($data)) {
            return [false, 'Import data matriks IKT gagal. Data yang diunggah kosong.'];
        }

        $isTargetFinal = TargetIndikator::whereIn('id_audit_periode', $periodIds)
            ->whereIn('id_unit', $unitKerjaIds)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->where('apakah_terfinalisasi', true)
            ->exists();

        if ($isTargetFinal) {
            return [false, 'Import data matriks IKT gagal. Terdapat target indikator yang sudah terfinalisasi pada periode dan unit kerja yang dipilih.'];
        }

        $scoreCount = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $idPenilaianPanduan)->count();
        $baseColumns = $isDefault ? 6 : 9;
        $jumlahKolom = $baseColumns + $scoreCount;

        // check jumlah kolom header
        $data = reset($data);
        $header = array_filter($data[0], function ($col) {
            return !empty($col);
        });
        if (count($header) != $jumlahKolom) {
            return [false, 'Import data gagal. Format template tidak sesuai. Gunakan template terbaru untuk mengimpor data.'];
        }

        // remove first row (header)
        array_shift($data);

        // remove empty rows
        $data = array_filter($data, function ($row) use ($jumlahKolom) {
            $empty = 0;
            for ($i = 0; $i <= ($jumlahKolom - 1); $i++) {
                if (empty($row[$i])) {
                    $empty++;
                }
            }

            return !($empty == $jumlahKolom);
        });

        return [true, $data];
    }

    private function validateImportData(array $data, int $idPenilaianPanduan, bool $isDefault = true)
    {
        $requiredColumns = $isDefault ? [0, 1, 2, 3] : [0, 2, 3, 4, 6, 7];

        $errorData = [];
        $hasError = false;

        // Validasi nomor matriks duplikat dalam file
        $nomorPenilaian = array_map('strval', array_filter(array_column($data, 0), function ($value) {
            return !empty($value);
        }));
        $duplicates = array_filter(array_count_values($nomorPenilaian), function($count) {
            return $count > 1;
        });

        $penilaianPanduan = PenilaianPanduan::find($idPenilaianPanduan);
        $akreditasiStandars = AkreditasiStandar::select('id', 'kode_standar')
            ->where('id_jenis_standar', $penilaianPanduan->id_jenis_standar)
            ->get();

        $standarIds = [];
        foreach ($data as $index => $row) {
            $errors = [];

            // Check required columns
            foreach ($requiredColumns as $colIndex) {
                if (empty($row[$colIndex])) {
                    $errors[$colIndex][] = 'tidak boleh kosong';
                }
            }

            // Validate standar akreditasi
            $standarIndex = $isDefault ? 2 : 3;
            $standarString = $row[$standarIndex];
            if (str_contains($standarString, ' - ')) {
                $standarParts = explode(' - ', $standarString);
                $standarString = trim($standarParts[0]);
            }
            $standarId = $akreditasiStandars->firstWhere('kode_standar', $standarString);
            if (empty($standarId)) {
                $errors[$standarIndex][] = 'tidak ditemukan';
            } else {
                $standarIds[$index] = $standarId->id;
            }

            // Validasi No Penilaian duplikat
            if (isset($duplicates[$row[0]])) {
                $errors[0][] = 'duplikat dalam file';
            }

            // Validasi parent (untuk non-default)
            if (!$isDefault && !empty($row[1])) {
                $parentExists = array_filter($data, function($r) use ($row) {
                    return $r[0] === $row[1];
                });
                if (empty($parentExists)) {
                    $errors[1][] = 'tidak ditemukan dalam dokumen';
                }
            }

            // Validasi bobot
            $bobotIndex = $isDefault ? 4 : 5;
            if (!empty($row[$bobotIndex]) && !is_numeric($row[$bobotIndex])) {
                $errors[$bobotIndex][] = 'harus berupa angka';
            }

            // Validasi status aktif
            $statusIndex = $isDefault ? 3 : 4;
            if (!in_array($row[$statusIndex], ['Aktif', 'Tidak Aktif'])) {
                $errors[$statusIndex][] = 'hanya boleh "Aktif" atau "Tidak Aktif"';
            }

            // Validasi Ya/Tidak fields
            $yaFieldsIndices = $isDefault ? [5] : [6, 7, 8];
            foreach ($yaFieldsIndices as $fieldIndex) {
                if (!empty($row[$fieldIndex]) && !in_array($row[$fieldIndex], ['Ya', 'Tidak'])) {
                    $errors[$fieldIndex][] = 'harus berisi "Ya" atau "Tidak"';
                }
            }

            // Build error row
            if (!empty($errors)) {
                $hasError = true;
                $errorData[$index] = $errors;
            }
        }

        return [!$hasError, $standarIds, $errorData];
    }

    private function saveErrorFile(string $uploadedFilePath, array $errorData, bool $isDefault): string
    {
        $fileName = 'error_import_' . Str::random(32) . '_' . time() . '.xlsx';

        // Pastikan folder temp ada
        if (!file_exists(public_path('temp'))) {
            mkdir(public_path('temp'), 0755, true);
        }

        // Export ke file Excel menggunakan file upload user
        Excel::store(
            new ExportPenilaianMatriksError($uploadedFilePath, $errorData, $isDefault),
            'temp/' . $fileName,
            'public'
        );

        // Hapus file error yang lebih dari 1 jam
        // $this->cleanupOldErrorFiles();

        return $fileName;
    }

    private function updateChildrenActiveStatus(array $data)
    {
        // Cek penilaian audit aktif
        $isHasActivePenilaianAudit = PenilaianAudit::where('id_audit_periode', AuditPeriode::findNowYearPeriod()?->id)
            ->where('id_penilaian_panduan', $data['id_penilaian_panduan'])
            ->where('apakah_terfinalisasi', false)
            ->exists();

        $isJadwalAuditAktif = JadwalAudit::where('id_audit_periode', AuditPeriode::findNowYearPeriod()?->id)->value('apakah_audit_aktif') ?? false;

        if ($isHasActivePenilaianAudit && $data['apakah_status_aktif_berubah'] && $isJadwalAuditAktif) {
            return new Error('Tidak dapat mengubah status aktif butir, terdapat penilaian yang masih aktif di periode audit ' . now()->year . '.');
        }

        $isActive = $data['apakah_aktif'] ?? false;
        PenilaianMatriks::where('info_left', '>', $data['info_left'])
            ->where('info_right', '<=', $data['info_right'])
            ->update([
                'apakah_aktif' => $isActive
            ]);
    }

    private function addReferenceItems($data, $matrixId, $itemType): void
    {
        // Hapus item yang tidak ada di data butir
        PenilaianMatriksReferensi::where('id_penilaian_matriks', $matrixId)
            ->whereNotIn('id_butir_referensi', $data)->delete();

        // Hapus butir yang tipenya tidak sama dengan tipe butir yang dipilih
        PenilaianMatriksReferensi::where('id_penilaian_matriks', $matrixId)
            ->where('jenis_referensi', '!=', $itemType)
            ->delete();

        foreach ($data as $itemId) {
            PenilaianMatriksReferensi::updateOrCreate(
                [
                    'id_penilaian_matriks' => $matrixId,
                    'id_butir_referensi' => $itemId
                ],
                [
                    'id_penilaian_matriks' => $matrixId,
                    'id_butir_referensi' => $itemId,
                    'jenis_referensi' => $itemType,
                ]
            );
        }
    }

    // Set akses IKT
    protected function setHakAksesIKT($model)
    {
        if ($model->apakah_data_default) {
            // set permission in request
            $permission = request()->permission;
            $permission['put'] = false;
            request()->merge(['permission' => $permission]);
        }
    }

    protected function checkIsOnReference($id)
    {
        $isParent = PenilaianMatriks::where('id_parent', $id)->exists();
        if ($isParent) {
            return new Error('Elemen ini tidak dapat dihapus karena masih memiliki indikator di dalamnya. Hapus indikatornya terlebih dahulu.');
        }
        if (is_array($id)) {
            $isReferenceOnTargetSkor = TargetSkor::whereIn('id_penilaian_matriks', $id)->exists();
        } else {
            $isReferenceOnTargetSkor = TargetSkor::where('id_penilaian_matriks', $id)->exists();

            // untuk kasus memperbaiki data bug yang sudah terlanjur masuk by: 26 september 2025
            // check if all has been soft deleted
            $countSoftDeleted = DB::table('spmi.penilaian_matriks_predikat')
                ->whereNotNull('waktu_dihapus')
                ->where('id_penilaian_matriks', $id)
                ->count();

            $countNotDeleted = DB::table('spmi.penilaian_matriks_predikat')
                ->whereNull('waktu_dihapus')
                ->where('id_penilaian_matriks', $id)
                ->count();

            if ($countNotDeleted == 0 && $countSoftDeleted > 0) {
                $idChildSkor = DB::table('spmi.penilaian_matriks_predikat')->where('id_penilaian_matriks', $id)->pluck('id')->toArray();
                PenilaianMatriksPredikat::whereIn('id', $idChildSkor)->delete();
                return false;
            }
        }

        if ($isReferenceOnTargetSkor) {
            return new Error('Butir sudah memiliki penilaian pada target capaian, tidak dapat dihapus.');
        }

        return false;
    }
}
