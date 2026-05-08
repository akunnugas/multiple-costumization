<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\SumberPendanaanAgendaKegiatan;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;
use Modules\Litabmas\Models\KlasterPendanaan;

class SumberPendanaanService
{
    /**
     * @var SumberPendanaan
     */
    protected $model = SumberPendanaan::class;

    protected $mataUang = 'IDR'; // default mata uang

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SumberPendanaan;
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
        $table = $this->model->getTable();

        $sql = "SELECT
                    f.id,
                    f.nama_sumber_pendanaan,
                    f.kategori_sumber_pendanaan,
                    f.total_pendanaan,
                    f.mata_uang,
                    f.id_periode_pendanaan,
                    fp.tahun as tahun_periode_pendanaan,
                    f.id_unit_kerja,
                    o.nama_unit
                FROM " . $table . " f
                JOIN core.unit_kerja o ON o.id = f.id_unit_kerja AND o.waktu_dihapus IS NULL
                JOIN litabmas.periode_pendanaan fp on fp.id = f.id_periode_pendanaan and fp.waktu_dihapus is null
                ";

        $defaultFilter = "f.waktu_dihapus IS NULL";

        $fieldMap = [
            'nama_sumber_pendanaan' => 'f.nama_sumber_pendanaan',
            'kategori_sumber_pendanaan' => 'f.kategori_sumber_pendanaan',
            'tahun_periode_pendanaan' => 'fp.tahun',
            'nama_unit' => 'o.nama_unit',
            'total_pendanaan' => 'CAST(f.total_pendanaan AS text)'
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return Collection|ModelNotFoundException
     */
    public function show(int $id): Collection|ModelNotFoundException
    {
        $table = $this->model->getTable();
        $sql = "select sp.id, sp.nama_sumber_pendanaan, sp.kategori_sumber_pendanaan, sp.total_pendanaan, sp.mata_uang,
                sp.id_periode_pendanaan, sp.id_unit_kerja, sp.maksimal_toleransi_similarity, sp.maksimal_toleransi_ai
            from $table as sp
            where sp.waktu_dihapus is null
                and sp.id = :id";
        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        return Collection::make($select[0]);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return SumberPendanaan|Error
     */
    public function store(array $data): SumberPendanaan|Error
    {
        $combinedAgenda = $this->getCombinedAgendaFromData($data);
        unset($data['optional_agenda_ids']);

        DB::beginTransaction();

        try {
            //overide mata uang revamp 1
            $data['mata_uang'] = $this->mataUang;
            $data['sisa_anggaran'] = $data['total_pendanaan'];
            $sumberPendanaan = $this->model->create($data);

            // update or create sumber pendanaan agenda kegiatan mapping
            $this->saveSourceAgendaMapping($sumberPendanaan->id, $combinedAgenda);
        } catch (Exception $e) {
            // check unique constraint
            if (str_contains($e->getMessage(), 'duplicate key value violates unique constraint "sp_periode_unit_kategori_nama_unique"')) {
                $message = "Gagal menyimpan karena duplikasi data Periode, Nama Sumber Pendanaan, Pengelola Pendanaan dan Kategori tidak boleh sama";
            }

            return new Error(message: $message ?? null, exception: $e);
        }

        DB::commit();

        return $sumberPendanaan;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SumberPendanaan|Error
     */
    public function update(array $data, int $id): SumberPendanaan|Error
    {
        $combinedAgenda = $this->getCombinedAgendaFromData($data);
        $existingAgenda = $this->getSourceAgendaMapping($id);

        unset($data['optional_agenda_ids']);

        DB::beginTransaction();

        try {
            $sumberPendanaan = $this->model->findOrFail($id);

            // Check if any of the specified fields have changed
            $fieldsToCheck = [
                'id_periode_pendanaan',
                'mata_uang',
                'maksimal_toleransi_similarity',
                'maksimal_toleransi_ai'
            ];

            $hasChanges = Cstr::isArrayDifferent($sumberPendanaan->toArray(), $data, $fieldsToCheck);

            // Check if any of the agenda kegiatan mapping has changed
            foreach ($combinedAgenda as $agendaId => $isActive) {
                if (!isset($existingAgenda[$agendaId]) || $existingAgenda[$agendaId] != $isActive) {
                    $hasChanges = true;
                    break;
                }
            }

            if ($hasChanges) {
                $relationsForCheck = ['klasterPendanaan'];
                // Check if data has relation
                $hasRelations = Relation::hasRelationsData($sumberPendanaan, $relationsForCheck);
                if (!empty($hasRelations['status'])) {
                    $relation = __('litabmas::sumber_pendanaan.' . $hasRelations['relation']);
                    return new Error("Sumber Pendanaan tidak bisa diubah karena sudah memiliki Data $relation.", Error::SQLSTATE_VIOLATION_FK);
                }
            }

            $totalDanaDiberikan = $this->getTotalPendanaanDiberikan($id);
            // Check if data total_pendanaan has changed
            if ($data['total_pendanaan'] != $sumberPendanaan->total_pendanaan) {
                $recordTotalPendanaan = floatval($data['total_pendanaan']);
                $totalDanaDiberikan = floatval($totalDanaDiberikan);
                if ($recordTotalPendanaan < $totalDanaDiberikan) {
                    $mataUang = $sumberPendanaan->mata_uang ?? 'IDR';
                    $convert = $mataUang !== config('money.defaults.currency');
                    //convert to human readable
                    $humanReadableMaxAnggaran = money($totalDanaDiberikan, $mataUang, $convert);
                    return new Error("Total Pendanaan tidak boleh kurang dari total pendanaan diberikan ($humanReadableMaxAnggaran)");
                }
                $data['sisa_anggaran'] = $data['total_pendanaan'];
            }

            $sumberPendanaan->update($data);

            // update or create sumber pendanaan agenda kegiatan mapping
            $this->saveSourceAgendaMapping($sumberPendanaan->id, $combinedAgenda);
        } catch (Exception $e) {
            // check unique constraint
            if (str_contains($e->getMessage(), 'duplicate key value violates unique constraint "sp_periode_unit_kategori_nama_unique"')) {
                $message = "Gagal menyimpan karena duplikasi data Periode, Nama Sumber Pendanaan, Pengelola Pendanaan dan Kategori tidak boleh sama";
            }

            return new Error(message: $message ?? null, exception: $e);
        }

        DB::commit();

        return $sumberPendanaan;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return Error|null
     */
    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {
            $sumberPendanaan = $this->model->findOrFail($id);

            // cek klaster
            if (KlasterPendanaan::where('id_sumber_pendanaan', $id)->exists()) {
                return new Error('Sumber Pendanaan tidak bisa dihapus karena sudah memiliki Data Klaster Pendanaan.', Error::SQLSTATE_VIOLATION_FK);
            }

            // delete sumber pendanaan agenda kegiatan mapping
            $sourceAgendaMapping = $this->model->find($id)->pivotAgendaKegiatan;
            foreach ($sourceAgendaMapping as $mapping) {
                $mapping->delete();
            }

            // delete sumber pendanaan
            $sumberPendanaan->delete();
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return null;
    }

    /**
     * Get data mapping aktif (yg dipilih user) berdsarkan sumber pendanaan.
     * yg dipilih user: mappingan antara sumber pendanaan memiliki agenda kegiatan apa saja.
     *
     * @param int $idSumberPendanaan
     * @return Collection|null
     */
    public function getAktifAgendaKegiatanByIdSumberPendanaan(int $idSumberPendanaan)
    {
        // agendaKegiatan() dari model SumberPendanaan
        $result = $this->model->find($idSumberPendanaan)?->agendaKegiatan()
            ->wherePivot('apakah_aktif', true)
            ->select(
                'sumber_pendanaan_agenda_kegiatan.id_sumber_pendanaan',
                'sumber_pendanaan_agenda_kegiatan.id_agenda_kegiatan',
                'agenda_kegiatan.nama_agenda',
                'agenda_kegiatan.apakah_wajib',
                'agenda_kegiatan.kode_agenda'
            )
            ->orderBy('agenda_kegiatan.urutan')
            ->get();

        // tambahkan field hanya_ada_waktu_mulai dan hanya_ada_waktu_selesai
        if ($result) {
            $onlyFirstDay = AgendaKegiatan::ONLY_FIRST_DAY_STEPS;
            foreach ($result as $item) {
                $item->hanya_ada_waktu_mulai = in_array($item->kode_agenda, $onlyFirstDay);
            }

            $onlyLastDay = AgendaKegiatan::ONLY_LAST_DAY_STEPS;
            foreach ($result as $item) {
                $item->hanya_ada_waktu_selesai = in_array($item->kode_agenda, $onlyLastDay);
            }
        }

        return $result;
    }

    /**
     * Get total budget/total pendaaan dan mata uang berdasarkan id sumber pendanaan.
     *
     * @param int $idSumberPendanaan
     * @return mixed|null
     */
    public function getTotalPendanaanDanMataUang(int $idSumberPendanaan)
    {
        $sql = "SELECT mata_uang, total_pendanaan, sisa_anggaran
            FROM litabmas.sumber_pendanaan
            WHERE waktu_dihapus is null
                and id = :id LIMIT 1";
        $result = DB::select($sql, ['id' => $idSumberPendanaan]);

        return $result[0] ?? null;
    }

    /**
     * Get id dan nama pengelola bantuan berdasarkan id sumber pendanaan.
     *
     * @param int $idSumberPendanaan
     * @return mixed|null
     */
    public function getPengelolaBantuan(int $idSumberPendanaan)
    {
        $sql = "SELECT sp.id_unit_kerja, uk.nama_unit FROM litabmas.sumber_pendanaan sp
            join core.unit_kerja uk on uk.id = sp.id_unit_kerja and uk.waktu_dihapus is null
        WHERE sp.waktu_dihapus is null
            and sp.id = :id
        LIMIT 1";

        $result = DB::select($sql, ['id' => $idSumberPendanaan]);
        return $result[0] ?? null;
    }

    /**
     * @param int $idPengajuanPendanaan
     * @return int|null
     */
    public function getIdSumberPendanaan(int $idPengajuanPendanaan)
    {
        $sql = "SELECT id_sumber_pendanaan
            FROM litabmas.pengajuan_pendanaan
            WHERE waktu_dihapus is null
                and id = :id
            LIMIT 1";
        $result = DB::select($sql, ['id' => $idPengajuanPendanaan]);

        return $result[0]->id_sumber_pendanaan ?? null;
    }

    /**
     * Update the remaining budget for a funding source.
     *
     * @param int $idSumberPendanaan The ID of the funding source.
     * @param float $biayaDisetujui The approved budget.
     * @param bool $addtotalSumber Whether to add the approved budget to the remaining budget or subtract it.
     * @return mixed The updated funding source model if successful, or an error object if the approved budget exceeds the remaining budget.
     */
    public function updateSisaAnggaran(int $idSumberPendanaan, float $biayaDisetujui, $addtotalSumber = false)
    {
        //if sisa anggaran is null, then set it to total_pendanaan - biayaDisetujui
        //else update sisa anggaran based on the type

        $model = $this->model->find($idSumberPendanaan);
        //if biayaDisetujui > sisa anggaran, then throw error
        if ($model->sisa_anggaran && $model->sisa_anggaran < $biayaDisetujui && !$addtotalSumber) {
            return new Error('Sisa anggaran sumber pendanaan tidak mencukupi, silahkan memasukkan rekomendasi kurang dari sisa anggaran sisa anggaran yang tersedia.');
        }

        if (!$addtotalSumber) {
            $model->sisa_anggaran = $model->sisa_anggaran ? $model->sisa_anggaran - $biayaDisetujui : $model->total_pendanaan - $biayaDisetujui;
        } else {
            $model->sisa_anggaran = $model->sisa_anggaran ? $model->sisa_anggaran + $biayaDisetujui : $model->total_pendanaan + $biayaDisetujui;
        }
        $model->save();

        return $model;
    }

    public function getTotalPendanaanDiberikan($idSumberPendanaan){
        $sql = "select sum(pp.nominal_anggaran_disetujui) as total_pendanaan_diberikan from litabmas.pengajuan_pendanaan pp where id_sumber_pendanaan = :id and waktu_dihapus is null limit 1";
        $result = DB::select($sql, ['id' => $idSumberPendanaan]);

        return $result[0]->total_pendanaan_diberikan ?? 0;
    }

    /**
     * Cek apakah sumber pendanaan tsb memiliki klaster pendanaan.
     *
     * @param int $idSumberPendanaan
     * @return bool
     */
    public function apakahMemilikiKlasterPendanaan(int $idSumberPendanaan)
    {
        return $this->model->join('litabmas.klaster_pendanaan as fc', 'fc.id_sumber_pendanaan', '=', 'litabmas.sumber_pendanaan.id')
            ->where('litabmas.sumber_pendanaan.id', $idSumberPendanaan)
            ->exists();
    }

    /*** --- [START] PRIVATE METHOD--- ***/
    /**
     * Ambil data agenda kegiatan yang wajib diisi dan opsional yang dipilih oleh user.
     * Untuk kemudian diolah menjadi format yang sesuai untuk disimpan di database.
     *
     * @param array $data
     * @return array
     */
    private function getCombinedAgendaFromData(array $data)
    {
        // get agenda yg wajib diisi & agenda opsional yg dipilih oleh user
        $agendaKegiatan = (new AgendaKegiatan())->getListCache();
        $requiredAgendaIds = $agendaKegiatan->where('apakah_wajib', true)->pluck('id')->toArray();
        $optionalAgendaIds = $data['optional_agenda_ids'] ?? [];

        // combine and add new apakah_wajib format to active
        $combinedAgenda = [];
        foreach ($agendaKegiatan as $agenda) {
            // dinyatakan aktif jika ada di required atau optional
            $isActive = in_array($agenda->id, $requiredAgendaIds) || in_array($agenda->id, $optionalAgendaIds);
            $combinedAgenda[$agenda->id] = $isActive;
        }

        return $combinedAgenda;
    }

    /**
     * Simpan (update/create) mapping antara sumber pendanaan dengan agenda kegiatan.
     *
     * @param int $idSumberPendanaan
     * @param array $agendaKegiatan
     * @return void
     */
    private function saveSourceAgendaMapping(int $idSumberPendanaan, array $agendaKegiatan)
    {
        $sourceAgendaMappingModel = new SumberPendanaanAgendaKegiatan();
        foreach ($agendaKegiatan as $agendaId => $isActive) {
            $sourceAgendaMappingModel->updateOrCreate(
                ['id_sumber_pendanaan' => $idSumberPendanaan, 'id_agenda_kegiatan' => $agendaId],
                ['apakah_aktif' => $isActive]
            );
        }
    }

    /**
     * Get the source agenda mapping for a given source of funding.
     *
     * @param int $idSumberPendanaan The ID of the source of funding.
     * @return array The source agenda mapping as an associative array, where the keys are the agenda kegiatan IDs and the values are the apakah_aktif values.
     */
    private function getSourceAgendaMapping(int $idSumberPendanaan)
    {
        return SumberPendanaanAgendaKegiatan::where('id_sumber_pendanaan', $idSumberPendanaan)
            ->get()
            ->pluck('apakah_aktif', 'id_agenda_kegiatan')
            ->toArray();
    }
    /*** --- [END] PRIVATE METHOD--- ***/
}
