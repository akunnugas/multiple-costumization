<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;
use Modules\Litabmas\Models\PengajuanPendanaan;

class AspekPenilaianOutputService
{
    /**
     * @var AspekPenilaianOutputPertanyaan
     */
    protected $model = AspekPenilaianOutputPertanyaan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AspekPenilaianOutputPertanyaan;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();

        $sql = "SELECT
            apop.id,
            apop.id_periode_pendanaan,
            apop.pertanyaan_penilaian_output,
            apop.no,
            STRING_AGG(apoj.jawaban_penilaian_output, ' / ') AS daftar_jawaban
        FROM
            $table apop
        LEFT JOIN
            litabmas.aspek_penilaian_output_jawaban apoj ON apoj.id_aspek_penilaian_output_pertanyaan = apop.id AND apoj.waktu_dihapus IS NULL";

        $fieldMap = [];

        $defaultFilter = "apop.waktu_dihapus is null";

        if (empty($order)) {
            $defaultOrder = [
                'field' => 'apop.no'
            ];
        }

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultOrder: $defaultOrder ?? [],
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            groupBy: "apop.id, apop.id_periode_pendanaan, apop.pertanyaan_penilaian_output, apop.no"
        );

        $result = Pagination::create($sql, $bindings, $page, $perPage);

        return $result;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return AspekPenilaianOutputPertanyaan|Error
     */
    public function show(int $id): AspekPenilaianOutputPertanyaan|Error
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return AspekPenilaianOutputPertanyaan|Error
     */
    public function store(array $data): AspekPenilaianOutputPertanyaan|Error
    {
        DB::beginTransaction();

        try {
            $data['pertanyaan_penilaian_output'] = htmlspecialchars_decode($data['pertanyaan_penilaian_output']);
            // save pertanyaan dulu
            $lastNo = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->select('no')
                ->orderBy('no', 'desc')
                ->first();
            $record = Arr::only($data, ['pertanyaan_penilaian_output', 'id_periode_pendanaan']);
            $record['no'] = $lastNo ? $lastNo->no + 1 : 1;
            $pertanyaan = $this->model->create($record);

            // save jawaban
            $record = [];
            foreach ($data['jawaban_penilaian_output'] as $jawaban) {
                $record[] = [
                    'id_aspek_penilaian_output_pertanyaan' => $pertanyaan->id,
                    'jawaban_penilaian_output' => $jawaban
                ];
            }
            $pertanyaan->jawaban()->createMany($record);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $pertanyaan;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return AspekPenilaianOutputPertanyaan|Error
     */
    public function update(array $data, int $id): AspekPenilaianOutputPertanyaan|Error
    {
        DB::beginTransaction();

        try {
            $data['pertanyaan_penilaian_output'] = htmlspecialchars_decode($data['pertanyaan_penilaian_output']);
            $pertanyaan = $this->model->findOrFail($id);
            $pertanyaan->update(Arr::only($data, ['pertanyaan_penilaian_output']));

            // delete then create jawaban
            $jawaban = $pertanyaan->jawaban;
            $jawaban->each(function ($item) {
                $item->delete();
            });

            $record = [];
            foreach ($data['jawaban_penilaian_output'] as $jawaban) {
                $record[] = [
                    'id_aspek_penilaian_output_pertanyaan' => $pertanyaan->id,
                    'jawaban_penilaian_output' => $jawaban
                ];
            }
            $pertanyaan->jawaban()->createMany($record);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $pertanyaan;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {

        try {
            $pertanyaan = $this->model->findOrFail($id);
            $relationsForCheck = ['penilaianReviewerOutputBersama'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($pertanyaan, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Kriteria Penilaian Luaran tidak bisa dihapus karena sudah memiliki data penilaian dari reviewer.", Error::SQLSTATE_VIOLATION_FK);
            }
            $pertanyaan->jawaban()->delete();
            $pertanyaan->delete();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }
}
