<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Date;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanAktivitasPenelitian;
use Modules\Litabmas\Models\PengajuanPendanaanPembimbing;
use Modules\Litabmas\Models\PengajuanPendanaanStatus;
use Modules\Litabmas\Models\PenilaianPembimbingAktivitasPenelitian;

class BimbinganAktivitasPenelitianService
{
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
        $idPengajuanPendanaan = request()->route()->parameter('pengajuan_pendanaan') ?? null;
        if (empty($idPengajuanPendanaan)) {
            return new Error('Pengajuan Pendanaan tidak ditemukan.', 404);
        }

        $table = (new PengajuanPendanaanAktivitasPenelitian())->getTable();

        $sql = "select ppap.id, ppap.tanggal_aktivitas_penelitian, ppap.nama_aktivitas_penelitian,
            ppap.lokasi_aktivitas_penelitian, ppap.id_dokumen_logbook,
            d.extension_versi_terbaru, pmap.feedback_logbook, pmap.id_dokumen_feedback_logbook
        from $table ppap
        left join dms.dokumen d on d.id = ppap.id_dokumen_logbook and d.waktu_dihapus is null
        left join litabmas.penilaian_pembimbing_aktivitas_penelitian pmap on pmap.id_pengajuan_pendanaan_aktivitas_penelitian = ppap.id
            and pmap.waktu_dihapus is null
        left join dms.dokumen d2 on d2.id = pmap.id_dokumen_feedback_logbook and d2.waktu_dihapus is null
        ";

        $bindings = [
            'idPengajuanPendanaan' => $idPengajuanPendanaan,
        ];

        if (empty($order)) {
            $defaultOrder = [
                'field' => 'ppap.waktu_dibuat',
            ];
        }

        $defaultFilter = "ppap.waktu_dihapus IS NULL and ppap.id_pengajuan_pendanaan = :idPengajuanPendanaan";

        $fieldMap = [
            'tanggal_aktivitas_penelitian' => 'CAST(ppap.tanggal_aktivitas_penelitian AS TEXT)',
            'id_dokumen_logbook' => 'd.nama_dokumen',
            'id_dokumen_feedback_logbook' => 'd2.nama_dokumen',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            defaultOrder: $defaultOrder ?? null,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            bindingUsingName: true
        );

        return Pagination::create($sql, $bindings, $page, $perPage, bindingUsingName: true);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return Collection|Error
     * @throws ModelNotFoundException
     */
    public function show(int $id): Collection|Error
    {
        $table = (new PengajuanPendanaanAktivitasPenelitian())->getTable();

        $sql = "select ppap.id, ppap.tanggal_aktivitas_penelitian, ppap.nama_aktivitas_penelitian,
            ppap.id_jenis_aktivitas, ppap.id_dokumen_logbook, ppap.lokasi_aktivitas_penelitian,
            pmap.feedback_logbook, pmap.id_dokumen_feedback_logbook
            from $table as ppap
            left join litabmas.penilaian_pembimbing_aktivitas_penelitian pmap on pmap.id_pengajuan_pendanaan_aktivitas_penelitian = ppap.id
                and pmap.waktu_dihapus is null
            where ppap.waktu_dihapus is null
                and ppap.id = :id";
        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        return Collection::make($select[0]);
    }

    /**
     * @param array $data
     * @param int $idPengajuanPendanaan
     * @param int $idAktivitasPenelitian
     * @return PenilaianPembimbingAktivitasPenelitian|Error
     */
    public function update(array $data, int $idPengajuanPendanaan, int $idAktivitasPenelitian): PenilaianPembimbingAktivitasPenelitian|Error
    {
        // 1. cek pembimbing
        $idPembimbing = PengajuanPendanaanPembimbing::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->where('id_biodata', auth()->user()?->biodata?->id)
            ->first()?->id;
        if (empty($idPembimbing)) {
            return new Error('Anda tidak memiliki akses untuk melakukan penilaian.', 403);
        }

        // 2. cek pengajuan pendanaan
        $pengajuanPendanaan = PengajuanPendanaan::where('id', $idPengajuanPendanaan)
            ->select('id', 'id_klaster_pendanaan')->first();
        if (empty($pengajuanPendanaan)) {
            return new Error('Data pengajuan pendanaan tidak ditemukan.');
        }

        // 3. cek status lolos pendanaan
        $statusPenentuanPendanaan = (new PengajuanPendanaanStatusService())->getStatusPenentuanPendanaan($pengajuanPendanaan->id);
        if ($statusPenentuanPendanaan !== PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN) {
            return new Error('Proposal belum lolos tahap penentuan pendanaan.');
        }

        // 4. cek sudah masuk masa pelaksanaan penelitian
        $check = (new KlasterPendanaanService())->getInfoPelaksanaanPenelitian($pengajuanPendanaan->id_klaster_pendanaan);
        if (!$check['sudah_masuk_masa_pelaksanaan_penelitian']) {
            $tanggalReview = Date::formatDateRange($check['waktu_mulai'], $check['waktu_selesai'], isoFormatMonth: 'MMMM');
            return new Error('Feedback aktivitas penelitian dapat dilakukan pada tanggal ' . $tanggalReview . '.');
        }

        $model = PenilaianPembimbingAktivitasPenelitian::class;

        DB::beginTransaction();

        try {
            $dokumenFeedback = $data['id_dokumen_feedback_logbook'] ?? null;
            unset($data['id_dokumen_feedback_logbook']);

            $data['id_pengajuan_pendanaan_aktivitas_penelitian'] = $idAktivitasPenelitian;
            $data['id_pengajuan_pendanaan_pembimbing'] = $idPembimbing;

            // update or create by key
            // karena satu dosen pembimbing satu aktivitas penelitian,
            // maka key nya adalah id_pengajuan_pendanaan_aktivitas_penelitian & id_pengajuan_pendanaan_pembimbing
            $result = $model::updateOrCreate(
                [
                    'id_pengajuan_pendanaan_aktivitas_penelitian' => $idAktivitasPenelitian,
                    'id_pengajuan_pendanaan_pembimbing' => $idPembimbing,
                ],
                $data
            );

            // upload document
            $data['id_dokumen_feedback_logbook'] = $dokumenFeedback;
            $uploaded = $this->processUploadDocument(
                $data,
                'id_dokumen_feedback_logbook',
                FolderStructure::LITABMAS_AKTIVITAS_PENELITIAN_PENDUKUNG_FEEDBACK,
                $result
            );

            if (!empty($uploaded) && Error::isError($uploaded->getError())) { // jika ada error saat proses dms
                DB::rollBack();
                return $uploaded->getError();
            }

            if (!empty($uploaded)) {
                // set id dokumen feedback logbook
                $result->id_dokumen_feedback_logbook = $uploaded->get()->id;
                $result->save();

                // execute upload ke s3
                $uploaded->executeUpload();
                if (Error::isError($uploaded->getError())) { // jika ada error saat proses dms
                    DB::rollBack();
                    return $uploaded->getError();
                }
            }
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        DB::commit();

        return $result;
    }

    /*** ---[START] PRIVATE METHOD--- ***/
    /**
     * Proses upload document ke DMS.
     *
     * @param array $data
     * @param string $field
     * @param string $folderCode
     * @param PenilaianPembimbingAktivitasPenelitian|null $model
     * @return UploadDokumen|null|Error
     */
    private function processUploadDocument(array $data, string $field, string $folderCode, PenilaianPembimbingAktivitasPenelitian $model = null): UploadDokumen|null|Error
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
                moduleCode: Modul::CODE_LITABMAS,
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
    /*** ---[END] PRIVATE METHOD--- ***/
}
