<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Enums\StatusPublikasiEnum;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\KlasterPendanaanOutcomePenelitian;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanPublikasiArtikel;
use Modules\Litabmas\Models\PengajuanPendanaanPublikasiBuku;
use Modules\Litabmas\Models\PengajuanPendanaanStatus;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;

class PengajuanPendanaanPublikasiManagementService
{
    /**
     * @var PengajuanPendanaanPublikasiArtikel|PengajuanPendanaanPublikasiBuku
     */
    protected $model;

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return PengajuanPendanaanPublikasiArtikel|PengajuanPendanaanPublikasiBuku|Error
     */
    public function store(array $data, int $idProposalPendanaan): PengajuanPendanaanPublikasiArtikel|PengajuanPendanaanPublikasiBuku|Error
    {
        $data['id_pengajuan_pendanaan'] = $idProposalPendanaan;

        $data['status_publikasi'] = StatusPublikasiEnum::STATUS_PUBLIKASI_SELESAI;

        if (isset($data['url_artikel'])) {
            $exists = $this->model->where('url_artikel', $data['url_artikel'])->first();
            if ($exists) {
                return new Error(message: 'Gagal menyimpan karena duplikasi data URL Artikel.');
            }
        }

        if (isset($data['isbn'])) {
            $exists = $this->model->where('isbn', $data['isbn'])->first();
            if ($exists) {
                return new Error(message: 'Gagal menyimpan karena duplikasi data ISBN.');
            }
        }

        $proposalPendanaan = PengajuanPendanaan::find($idProposalPendanaan);
        $klasterPendanaan = KlasterPendanaan::find($proposalPendanaan->id_klaster_pendanaan);
        $listIdJenisOutcome = KlasterPendanaanOutcomePenelitian::where('id_klaster_pendanaan', $klasterPendanaan->id)
            ->where('apakah_wajib', true)
            ->pluck('id_jenis_outcome_penelitian')
            ->toArray();

        if (!$proposalPendanaan) {
            return new Error(message: 'Gagal menyimpan karena data proposal pendanaan tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $this->model->create($data);

            $isValid = true;
            foreach ($listIdJenisOutcome as $idJenisOutcome) {
                $existBuku = PengajuanPendanaanPublikasiBuku::where('id_jenis_outcome_penelitian', $idJenisOutcome)
                    ->where('id_pengajuan_pendanaan', $idProposalPendanaan)
                    ->first();

                $existArtikel = PengajuanPendanaanPublikasiArtikel::where('id_jenis_outcome_penelitian', $idJenisOutcome)
                    ->where('id_pengajuan_pendanaan', $idProposalPendanaan)
                    ->first();

                if (!$existBuku && !$existArtikel) {
                    $isValid = false;
                    break;
                }
            }

            if ($proposalPendanaan->status_agenda_kegiatan !== PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL && $isValid) {
                $proposalPendanaan->update([
                    'status_agenda_kegiatan' => PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $this->model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return PengajuanPendanaanPublikasiArtikel|PengajuanPendanaanPublikasiBuku|Error
     */
    public function update(array $data, int $id): PengajuanPendanaanPublikasiArtikel|PengajuanPendanaanPublikasiBuku|Error
    {
        if (isset($data['url_artikel'])) {
            $exists = $this->model->where('url_artikel', $data['url_artikel'])->first();
            if ($exists && $exists->id !== $id) {
                return new Error(message: 'Gagal menyimpan karena duplikasi data URL Artikel.');
            }
        }

        if (isset($data['isbn'])) {
            $exists = $this->model->where('isbn', $data['isbn'])->first();
            if ($exists && $exists->id !== $id) {
                return new Error(message: 'Gagal menyimpan karena duplikasi data ISBN.');
            }
        }

        try {
            $model = $this->model->findOrFail($id);
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
        $data = $this->model->find($id);

        DB::beginTransaction();
        try {
            $idProposalPendanaan = $data->id_pengajuan_pendanaan;

            $data->delete();

            $proposalPendanaan = PengajuanPendanaan::find($idProposalPendanaan);
            $klasterPendanaan = KlasterPendanaan::find($proposalPendanaan->id_klaster_pendanaan);
            $listIdJenisOutcome = KlasterPendanaanOutcomePenelitian::where('id_klaster_pendanaan', $klasterPendanaan->id)
                ->where('apakah_wajib', true)
                ->pluck('id_jenis_outcome_penelitian')
                ->toArray();

            $existBukuList = PengajuanPendanaanPublikasiBuku::where('id_pengajuan_pendanaan', $idProposalPendanaan)
                ->whereIn('id_jenis_outcome_penelitian', $listIdJenisOutcome)
                ->pluck('id_jenis_outcome_penelitian')
                ->toArray();

            $existArtikelList = PengajuanPendanaanPublikasiArtikel::where('id_pengajuan_pendanaan', $idProposalPendanaan)
                ->whereIn('id_jenis_outcome_penelitian', $listIdJenisOutcome)
                ->pluck('id_jenis_outcome_penelitian')
                ->toArray();

            $existOutcomes = array_merge($existBukuList, $existArtikelList);

            $isValid = empty(array_diff($listIdJenisOutcome, $existOutcomes));
            if (!$isValid) {
                PengajuanPendanaan::where('id', $idProposalPendanaan)->update([
                    'status_agenda_kegiatan' => PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return null;
    }
}
