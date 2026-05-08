<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;

class AspekPenilaianKomposisiProposalService
{
    /**
     * @var AspekPenilaianKomposisiProposal
     */
    protected $model = AspekPenilaianKomposisiProposal::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AspekPenilaianKomposisiProposal;
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
                apkp.id,
                apkp.id_periode_pendanaan,
                apkp.nama_komposisi_proposal,
                apkp.bobot_komposisi_proposal,
                apkp.no
            FROM $table apkp";

        $fieldMap = [
            'id' => 'apkp.id',
            'id_periode_pendanaan' => 'apkp.id_periode_pendanaan',
            'nama_komposisi_proposal' => 'apkp.nama_komposisi_proposal',
            'bobot_komposisi_proposal' => 'CAST(apkp.bobot_komposisi_proposal AS TEXT)',
            'no' => 'CAST(apkp.no AS TEXT)',
        ];

        $defaultFilter = "apkp.waktu_dihapus is null";

        if (empty($order)) {
            $defaultOrder = [
                'field' => 'apkp.no'
            ];
        }

        [$sql, $params] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultOrder: $defaultOrder ?? [],
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $params, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return AspekPenilaianKomposisiProposal|Error
     */
    public function show(int $id): AspekPenilaianKomposisiProposal|Error
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Get data komposisi proposal berdasarkan jenis pendanaan dan periode pendanaan.
     *
     * @param string $kodeJenisPendanaan
     * @param int $idPeriodePendanaan
     * @return mixed
     */
    public function getByJenisPendanaanDanPeriodePendanaan(string $kodeJenisPendanaan, int $idPeriodePendanaan)
    {
        return $this->model->select('id', 'nama_komposisi_proposal', 'bobot_komposisi_proposal', 'no')
            ->where('kode_jenis_pendanaan', $kodeJenisPendanaan)
            ->where('id_periode_pendanaan', $idPeriodePendanaan)
            ->orderBy('no')
            ->get();
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return AspekPenilaianKomposisiProposal|Error
     * @throws ValidationException
     */
    public function store(array $data): AspekPenilaianKomposisiProposal|Error
    {
        try {
            $data['nama_komposisi_proposal'] = htmlspecialchars_decode($data['nama_komposisi_proposal']);
            // validasi bobot_komposisi_proposal total tidak boleh lebih dari 100 jika satu periode dan jenis pendanaan
            $totalBobot = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->sum('bobot_komposisi_proposal');

            $check = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where(function ($query) use ($data) {
                    $query->where('nama_komposisi_proposal', $data['nama_komposisi_proposal'])
                        ->orWhere('no', $data['no']);
                })
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->first();

            if ($check) {
                return new Error($check->no == $data['no'] ? 'Nomor sudah digunakan' : 'Kriteria Penilaian sudah ada');
            }

            if ($totalBobot + $data['bobot_komposisi_proposal'] > 100) {
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
     * @return AspekPenilaianKomposisiProposal|Error
     * @throws ValidationException
     */
    public function update(array $data, int $id): AspekPenilaianKomposisiProposal|Error
    {


        try {
            $data['nama_komposisi_proposal'] = htmlspecialchars_decode($data['nama_komposisi_proposal']);
            // validasi bobot_komposisi_proposal total tidak boleh lebih dari 100 jika satu periode dan jenis pendanaan
            $totalBobot = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->where('id', '!=', $id)
                ->sum('bobot_komposisi_proposal');

            $check = $this->model->where('id_periode_pendanaan', $data['id_periode_pendanaan'])
                ->where(function ($query) use ($data) {
                    $query->where('nama_komposisi_proposal', $data['nama_komposisi_proposal'])
                        ->orWhere('no', $data['no']);
                })
                ->where('kode_jenis_pendanaan', $data['kode_jenis_pendanaan'])
                ->where('id', '!=', $id)
                ->first();

            if ($check) {
                return new Error($check->no == $data['no'] ? 'Nomor sudah digunakan' : 'Kriteria Penilaian sudah ada');
            }

            if ($totalBobot + $data['bobot_komposisi_proposal'] > 100) {
                return new Error('Total bobot tidak boleh lebih dari 100%');
            }

            $model = $this->model->findOrFail($id);

            //check relation
            $relationsForCheck = ['penilaianReviewerKomposisiProposal'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Kriteria Penilaian tidak bisa diubah karena sudah memiliki data penilaian dari reviewer.", Error::SQLSTATE_VIOLATION_FK);
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
            $relationsForCheck = ['penilaianReviewerKomposisiProposal'];
            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
            // If data has relation, return error
            if (!empty($hasRelations['status'])) {
                return new Error("Kriteria Penilaian tidak bisa dihapus karena sudah memiliki data penilaian dari reviewer.", Error::SQLSTATE_VIOLATION_FK);
            }
            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }
}
