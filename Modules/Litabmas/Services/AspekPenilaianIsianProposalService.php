<?php

namespace Modules\Litabmas\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\AspekPenilaianIsianProposal;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;

class AspekPenilaianIsianProposalService
{
    /**
     * @var AspekPenilaianIsianProposal
     */
    protected $model = AspekPenilaianIsianProposal::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AspekPenilaianIsianProposal;
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
                apip.id,
                apip.id_periode_pendanaan,
                apip.kode_jenis_pendanaan,
                apip.nama_isian_proposal,
                apip.urutan_isian_proposal
            FROM $table apip";

        $defaultFilter = "apip.waktu_dihapus is null";

        $fieldMap = [
            'id' => 'apip.id',
            'id_periode_pendanaan' => 'apip.id_periode_pendanaan',
            'kode_jenis_pendanaan' => 'apip.kode_jenis_pendanaan',
            'nama_isian_proposal' => 'apip.nama_isian_proposal',
            'urutan_isian_proposal' => 'CAST(apip.urutan_isian_proposal AS TEXT)',
        ];

        if (empty($order)) {
            $defaultOrder = [
                'field' => 'apip.urutan_isian_proposal'
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
     * @return AspekPenilaianIsianProposal|Error
     */
    public function show(int $id): AspekPenilaianIsianProposal|Error
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
     * @return AspekPenilaianIsianProposal|Error
     */
    public function store(array $data): AspekPenilaianIsianProposal|Error
    {
        $data['nama_isian_proposal'] = htmlspecialchars_decode($data['nama_isian_proposal']);
        try {
            $check = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where(function ($query) use ($data) {
                    $query->where('nama_isian_proposal', $data['nama_isian_proposal'])
                        ->orWhere('urutan_isian_proposal', $data['urutan_isian_proposal']);
                })
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->first();

            if ($check) {
                return new Error($check->urutan_isian_proposal == $data['urutan_isian_proposal'] ? 'Nomor sudah digunakan.' : 'Komponen Proposal sudah ada.');
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
     * @return AspekPenilaianIsianProposal|Error
     */
    public function update(array $data, int $id): AspekPenilaianIsianProposal|Error
    {
        $data['nama_isian_proposal'] = htmlspecialchars_decode($data['nama_isian_proposal']);
        try {
            $model = $this->model->findOrFail($id);

            $check = $this->model->where('id_periode_pendanaan', $model->id_periode_pendanaan)
                ->where(function ($query) use ($data, $model) {
                    $query->where('nama_isian_proposal', $data['nama_isian_proposal'])
                        ->orWhere('urutan_isian_proposal', $data['urutan_isian_proposal']);
                })
                ->where('kode_jenis_pendanaan', $model->kode_jenis_pendanaan)
                ->where('id', '!=', $model->id)
                ->first();

            if ($check) {
                return new Error($check->urutan_isian_proposal == $data['urutan_isian_proposal'] ? 'Nomor sudah digunakan.' : 'Komponen Proposal sudah ada.');
            }

            //check relation
            $relationsForCheck = ['pengajuanPendanaanIsianProposal'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Komponen Proposal tidak bisa diubah karena sudah memiliki data pengajuan proposal.", Error::SQLSTATE_VIOLATION_FK);
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
            $relationsForCheck = ['pengajuanPendanaanIsianProposal'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Komponen Proposal tidak bisa dihapus karena sudah memiliki data pengajuan proposal.", Error::SQLSTATE_VIOLATION_FK);
            }
            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Get data aspek review proposal berdasarkan jenis pendanaan dan periode pendanaan.
     *
     * @param string $kodeJenisPendanaan
     * @param int $idPeriodePendanaan
     * @return mixed
     */
    public function getByJenisPendanaanDanPeriodePendanaan(string $kodeJenisPendanaan, int $idPeriodePendanaan)
    {
        return $this->model->select('id', 'nama_isian_proposal', 'urutan_isian_proposal')
            ->where('kode_jenis_pendanaan', $kodeJenisPendanaan)
            ->where('id_periode_pendanaan', $idPeriodePendanaan)
            ->orderBy('urutan_isian_proposal')
            ->get();
    }
}
