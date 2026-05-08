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
use Modules\DMS\Services\DokumenManagementService;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanAktivitasPenelitian;

class PengajuanPendanaanAktivitasPenelitianService
{
    /**
     * @var PengajuanPendanaanAktivitasPenelitian
     */
    protected $model = PengajuanPendanaanAktivitasPenelitian::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanAktivitasPenelitian;
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
        $idPengajuanPendanaan = request()->route()->parameter('pengajuan_pendanaan') ?? null;
        if (empty($idPengajuanPendanaan)) {
            return new Error('Pengajuan Pendanaan tidak ditemukan.', 404);
        }

        $table = $this->model->getTable();

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
     * Buat data baru.
     *
     * @param array $data
     * @param int $idPengajuanPendanaan
     * @return PengajuanPendanaanAktivitasPenelitian|Error
     */
    public function store(array $data, int $idPengajuanPendanaan): PengajuanPendanaanAktivitasPenelitian|Error
    {
        // 1. Cek pengajuan pendanaan
        $pengajuanPendanaan = PengajuanPendanaan::where('id', $idPengajuanPendanaan)
            ->select('id_klaster_pendanaan')
            ?->first();
        if (empty($pengajuanPendanaan)) {
            return new Error('Data pengajuan pendanaan tidak ditemukan.');
        }

        DB::beginTransaction();

        // upload document
        $uploaded = $this->processUploadDocument(
            $data,
            'id_dokumen_logbook',
            FolderStructure::LITABMAS_AKTIVITAS_PENELITIAN_PENDUKUNG,
        );
        if (Error::isError($uploaded->getError())) { // jika ada error saat proses dms
            DB::rollBack();
            return $uploaded->getError();
        }

        try {
            $idDokuemnLogbook = $uploaded->get()->id ?? null;
            $data['id_dokumen_logbook'] = $idDokuemnLogbook;
            $data['id_pengajuan_pendanaan'] = $idPengajuanPendanaan;
            $data = $this->model->create($data);

            // execute upload ke s3
            $uploaded->executeUpload();
            if (Error::isError($uploaded->getError())) { // jika ada error saat proses dms
                DB::rollBack();
                return $uploaded->getError();
            }
        } catch (Exception $e) {
            return new Error(message: $message ?? null, exception: $e);
        }

        DB::commit();

        return $data;
    }

    /**
     * Update data aktivitas penelitian di suatu pengajuan pendanaan.
     *
     * @param array $data
     * @param int $idPengajuanPendanaan
     * @param int $idPengajuanPendanaanAktivitasPenelitian
     * @return PengajuanPendanaanAktivitasPenelitian|Error
     */
    public function update(array $data, int $idPengajuanPendanaan, int $idPengajuanPendanaanAktivitasPenelitian): PengajuanPendanaanAktivitasPenelitian|Error
    {
        // 1. Cek pengajuan pendanaan
        $pengajuanPendanaan = PengajuanPendanaan::where('id', $idPengajuanPendanaan)
            ->select('id_klaster_pendanaan')
            ?->first();
        if (empty($pengajuanPendanaan)) {
            return new Error('Data pengajuan pendanaan tidak ditemukan.', 404);
        }

        // 2. cek aktivitas penelitian
        $model = $this->model->find($idPengajuanPendanaanAktivitasPenelitian);
        if (empty($model)) {
            return new Error('Data tidak ditemukan.', 404);
        }

        DB::beginTransaction();

        // upload document
        if ($data['id_dokumen_logbook']) {
            $file = $data['id_dokumen_logbook'];
            $fileName = explode('.', $file?->getClientOriginalName())[0];

            $upload = new UploadDokumen();
            $upload = $upload->upload(
                file: $file,
                name: $fileName,
                folderCode: UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_REVIEWER_SK,
                moduleCode: Modul::CODE_LITABMAS,
                note: null,
                withTransaction: false
            );

            if (Error::isError($upload->getError())) {
                return new Error($upload->getError());
            }

            // Execute upload
            $upload->executeUpload();

            $data['id_dokumen_logbook'] = $upload->get()?->id;
        } else {
            unset($data['id_dokumen_logbook']);
        }

        try {
            $model->update($data);

            // execute upload ke s3
            if (!empty($uploaded)) {
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

        return $model;
    }

    /**
     * Delete aktivitas penelitian di suatu pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @param int $idPengajuanPendanaanAktivitasPenelitian
     * @return mixed|Error
     */
    public function destroy(int $idPengajuanPendanaan, int $idPengajuanPendanaanAktivitasPenelitian)
    {
        // 1. Cek pengajuan pendanaan
        $pengajuanPendanaan = PengajuanPendanaan::where('id', $idPengajuanPendanaan)
            ->select('id_klaster_pendanaan')
            ?->first();
        if (empty($pengajuanPendanaan)) {
            return new Error('Data pengajuan pendanaan tidak ditemukan.');
        }

        // 2. cek sudah masuk masa pelaksanaan penelitian
        $check = (new KlasterPendanaanService())->getInfoPelaksanaanPenelitian($pengajuanPendanaan->id_klaster_pendanaan);
        if (!$check['sudah_masuk_masa_pelaksanaan_penelitian']) {
            $tanggalReview = Date::formatDateRange($check['waktu_mulai'], $check['waktu_selesai'], isoFormatMonth: 'MMMM');
            return new Error('Penghapusan aktivitas penelitian hanya dapat dilakukan pada tanggal ' . $tanggalReview . '.');
        }

        // 3. cek pengajuan pendanaan akttivitas penelitian
        $model = $this->model->where('id', $idPengajuanPendanaanAktivitasPenelitian)
            ->where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->first();
        if (empty($model)) {
            return new Error('Data tidak ditemukan.', 404);
        }

        DB::beginTransaction();

        try {
            // hapus dokumen
            if (!empty($model->id_dokumen_logbook)) {
                (new DokumenManagementService())->destroy($model->id_dokumen_logbook);
            }

            $model->delete();
        } catch (Exception $e) {
            $message = null;
            if ($e->getCode() === '23503') {
                // cek constraint terkait 'pengajuan_pendanaan_aktivitas_penelitian'
                if (str_contains($e->getMessage(), 'delete on table "pengajuan_pendanaan_aktivitas_penelitian" violates foreign key constraint')) {
                    $message = 'Data aktivitas penelitian tidak dapat dihapus karena sudah diberi penilaian oleh pembimbing.';
                }
            }

            DB::rollBack();
            return new Error($message, exception: $e);
        }

        DB::commit();

        return $model;
    }

    /**
     * Get daftar aktivitas penelitian tanpa pagination.
     *
     * @param int $idPengajuanPendanaan
     * @param bool $withDetailDocument
     * @return mixed
     */
    public function getDaftarByIdPengajuanPendanaan(int $idPengajuanPendanaan, bool $withDetailDocument = false)
    {
        $table = $this->model->getTable();
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

        $sql .= "where ppap.waktu_dihapus is null
            and ppap.id_pengajuan_pendanaan = :idPengajuanPendanaan";
        return json_decode(json_encode(DB::select($sql, $bindings)), true);
    }

    private function processUploadDocument(array $data, string $field, string $folderCode, PengajuanPendanaanAktivitasPenelitian $model = null): UploadDokumen|null|Error
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
}
