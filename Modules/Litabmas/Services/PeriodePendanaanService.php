<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Core\Helpers\Relation;
use Modules\Litabmas\Models\AspekPenilaianIsianProposal;
use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
use Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan;
use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
use Modules\Litabmas\Models\SumberPendanaan;

class PeriodePendanaanService
{
    /**
     * @var PeriodePendanaan
     */
    protected $model = PeriodePendanaan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PeriodePendanaan;
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
        $fieldMap = [
            'maksimal_ketua_mendaftar' => 'CAST(maksimal_ketua_mendaftar AS TEXT)',
            'maksimal_anggota_mendaftar' => 'CAST(maksimal_anggota_mendaftar AS TEXT)',
        ];

        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter, fieldMap: $fieldMap);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PeriodePendanaan|Error
     */
    public function show(int $id): PeriodePendanaan|Error
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
     * @return PeriodePendanaan|Error
     */
    public function store(array $data): PeriodePendanaan|Error
    {
        try {
            // mengecek apakah ada tanggal_mulai dan tanggal_akhir yang berpotongan dengan periode lainnya
            $check = $this->model->where(function ($query) use ($data) {
                // handle jika tanggal_mulai dan tanggal_akhir berpotongan dengan periode lainnya
                $query->whereBetween('tanggal_mulai', [$data['tanggal_mulai'], $data['tanggal_akhir']])
                    ->orWhereBetween('tanggal_akhir', [$data['tanggal_mulai'], $data['tanggal_akhir']]);
            })->orWhere(function ($query) use ($data) {
                // handle jika tanggal_mulai dan tanggal_akhir berada di dalam periode lainnya
                $query->where('tanggal_mulai', '<=', $data['tanggal_mulai'])
                    ->where('tanggal_akhir', '>=', $data['tanggal_akhir']);
            });

            if ($check->exists()) {
                return new Error(message: 'Tanggal yang Anda masukkan berselisihan dengan periode lainnya.');
            }

            $model = $this->model->create($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return PeriodePendanaan|Error
     */
    public function update(array $data, int $id): PeriodePendanaan|Error
    {
        try {
            $model = $this->model->findOrFail($id);

            DB::enableQueryLog();

            if ($data['tahun'] != $model->tahun) {
                // check if data has relation
                $relationsForCheck = [
                    'sumberPendanaan',
                ];
                $hasRelations = Relation::hasRelationsData($model, $relationsForCheck);
                if (!empty($hasRelations['status'])) {
                    $relation = __('litabmas::periode_pendanaan.' . $hasRelations['relation']);
                    return new Error("Periode Pendanaan tidak bisa diubah karena sudah memiliki Data $relation.", Error::SQLSTATE_VIOLATION_FK);
                }
            }

            // validasi tidak boleh ada tanggal yg berselisihan dengan periode lainnya yg sudah ada
            $check = $this->model->where(function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    // handle jika tanggal_mulai dan tanggal_akhir berpotongan dengan periode lainnya
                    $query->whereBetween('tanggal_mulai', [$data['tanggal_mulai'], $data['tanggal_akhir']])
                        ->orWhereBetween('tanggal_akhir', [$data['tanggal_mulai'], $data['tanggal_akhir']]);
                })->orWhere(function ($query) use ($data) {
                    // handle jika tanggal_mulai dan tanggal_akhir berada di dalam periode lainnya
                    $query->where('tanggal_mulai', '<=', $data['tanggal_mulai'])
                        ->where('tanggal_akhir', '>=', $data['tanggal_akhir']);
                });
            })->where('id', '<>', $id);

            if ($check->exists()) {
                return new Error(message: 'Tanggal yang Anda masukkan berselisihan dengan periode lainnya.');
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
     * @return Error|null
     */
    public function destroy(int $id): Error|null
    {
        try {
            $model = $this->model->findOrFail($id);

            // cek sumber pendanaan
            if (SumberPendanaan::where('id_periode_pendanaan', $id)->exists()) {
                return new Error('Periode Pendanaan tidak bisa dihapus karena sudah memiliki Data Sumber Pendanaan.', Error::SQLSTATE_VIOLATION_FK);
            }

            // cek aspek penilaian isian proposal
            if (AspekPenilaianIsianProposal::where('id_periode_pendanaan', $id)->exists()) {
                return new Error('Periode Pendanaan tidak bisa dihapus karena sudah memiliki Data Aspek Penilaian Isian Proposal.', Error::SQLSTATE_VIOLATION_FK);
            }

            // cek aspek penilaian komposisi proposal
            if (AspekPenilaianKomposisiProposal::where('id_periode_pendanaan', $id)->exists()) {
                return new Error('Periode Pendanaan tidak bisa dihapus karena sudah memiliki Data Aspek Penilaian Komposisi Proposal.', Error::SQLSTATE_VIOLATION_FK);
            }

            // cek aspek penilaian presentasi proposal
            if (AspekPenilaianPresentasiProposal::where('id_periode_pendanaan', $id)->exists()) {
                return new Error('Periode Pendanaan tidak bisa dihapus karena sudah memiliki Data Aspek Penilaian Presentasi Proposal.', Error::SQLSTATE_VIOLATION_FK);
            }

            // cek Kriteria Penilaian Luaran pertanyaan
            if (AspekPenilaianOutputPertanyaan::where('id_periode_pendanaan', $id)->exists()) {
                return new Error('Periode Pendanaan tidak bisa dihapus karena sudah memiliki Data Kriteria Penilaian Luaran Pertanyaan.', Error::SQLSTATE_VIOLATION_FK);
            }

            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
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
            return ManagementService::create($this->model)->destroySome($ids);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * @param int $idPeriodePendanaan
     * @return int|null
     */
    public function getMaxKetuaMendaftar(int $idPeriodePendanaan)
    {
        return PeriodePendanaan::select('maksimal_ketua_mendaftar')
            ->find($idPeriodePendanaan)?->maksimal_ketua_mendaftar;
    }

    /**
     * Get maksimal mendaftar utk Anggota.
     *
     * @param int $idPeriodePendanaan
     * @return int|null
     */
    public function getMaxAnggotaMendaftar(int $idPeriodePendanaan)
    {
        return PeriodePendanaan::select('maksimal_anggota_mendaftar')
            ->find($idPeriodePendanaan)?->maksimal_anggota_mendaftar;
    }
}
