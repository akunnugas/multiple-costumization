<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;

class AspekPenilaianPresentasiProposalService
{
    /**
     * @var AspekPenilaianPresentasiProposal
     */
    protected $model = AspekPenilaianPresentasiProposal::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AspekPenilaianPresentasiProposal;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "SELECT
                appp.id,
                appp.id_periode_pendanaan,
                appp.kode_jenis_pendanaan,
                appp.pertanyaan_presentasi_proposal,
                appp.bobot_pertanyaan_presentasi_proposal,
                appp.no
            FROM $table appp";

        $fieldMap = [
            'id' => 'appp.id',
            'id_periode_pendanaan' => 'appp.id_periode_pendanaan',
            'kode_jenis_pendanaan' => 'appp.kode_jenis_pendanaan',
            'pertanyaan_presentasi_proposal' => 'appp.pertanyaan_presentasi_proposal',
            'bobot_pertanyaan_presentasi_proposal' => 'CAST(appp.bobot_pertanyaan_presentasi_proposal AS TEXT)',
            'no' => 'CAST(appp.no AS TEXT)',
        ];

        $defaultFilter = "appp.waktu_dihapus is null";

        if (empty($order)) {
            $defaultOrder = [
                'field' => 'appp.no'
            ];
        }

        [$sql, $params] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultOrder: $defaultOrder ?? [],
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $params, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return AspekPenilaianPresentasiProposal|Error
     */
    public function show(int $id): AspekPenilaianPresentasiProposal|Error
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
     * @return AspekPenilaianPresentasiProposal|Error
     * @throws ValidationException
     */
    public function store(array $data): AspekPenilaianPresentasiProposal|Error
    {
        try {
            $data['pertanyaan_presentasi_proposal'] = htmlspecialchars_decode($data['pertanyaan_presentasi_proposal']);
            // cek duplikasi
            $check = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where(function ($query) use ($data) {
                    $query->where('pertanyaan_presentasi_proposal', $data['pertanyaan_presentasi_proposal'])
                        ->orWhere('no', $data['no']);
                })
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->first();

            if ($check) {
                return new Error($check->no == $data['no'] ? 'Nomor sudah digunakan' : 'Pertanyaan Penilaian Presentasi sudah ada');
            }

            // validasi bobot_pertanyaan_presentasi_proposal
            $totalBobot = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->sum('bobot_pertanyaan_presentasi_proposal');

            if ($totalBobot + $data['bobot_pertanyaan_presentasi_proposal'] > 100) {
                return new Error('Total bobot tidak boleh lebih dari 100%');
            }

            return $this->model->create($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return AspekPenilaianPresentasiProposal|Error
     * @throws ValidationException
     */
    public function update(array $data, int $id): AspekPenilaianPresentasiProposal|Error
    {
        try {
            
            $data['pertanyaan_presentasi_proposal'] = htmlspecialchars_decode($data['pertanyaan_presentasi_proposal']);

            // cek duplikasi
            $check = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where(function ($query) use ($data) {
                    $query->where('pertanyaan_presentasi_proposal', $data['pertanyaan_presentasi_proposal'])
                        ->orWhere('no', $data['no']);
                })
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->where('id', '!=', $id)
                ->first();

            if ($check) {
                return new Error($check->no == $data['no'] ? 'Nomor sudah digunakan' : 'Pertanyaan Penilaian Presentasi sudah ada');
            }

            // validasi bobot_pertanyaan_presentasi_proposal
            $totalBobot = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->where('id', '!=', $id)
                ->sum('bobot_pertanyaan_presentasi_proposal');

            if ($totalBobot + $data['bobot_pertanyaan_presentasi_proposal'] > 100) {
                return new Error('Total bobot tidak boleh lebih dari 100%');
            }

            $model = $this->model->findOrFail($id);

            //check relation
            $relationsForCheck = ['penilaianReviewerPresentasiProposal'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Kriteria Penilaian Presentasi tidak bisa diubah karena sudah memiliki data penilaian dari reviewer.", Error::SQLSTATE_VIOLATION_FK);
            }

            $model->update($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
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
            $model = $this->model->findOrFail($id);
            //check relation
            $relationsForCheck = ['penilaianReviewerPresentasiProposal'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Kriteria Penilaian Presentasi tidak bisa diubah karena sudah memiliki data penilaian dari reviewer.", Error::SQLSTATE_VIOLATION_FK);
            }
            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }
}
