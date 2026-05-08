<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Jobs\ProcessSyncIndikatorBobot;
use Modules\Core\Models\Shared\KlienConfig;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkAuditor;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\TargetIndikator;

class AuditPeriodeManagementService
{
    /**
     * @var AuditPeriode
     */
    protected $model = AuditPeriode::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AuditPeriode;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return AuditPeriode
     */
    public function show(int $id): AuditPeriode
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return AuditPeriode
     */
    public function store(array $data): AuditPeriode
    {
        DB::beginTransaction();
        $model = $this->model->create($data);

        $kode_klien = KlienConfig::getKodeKlien();
        ProcessSyncIndikatorBobot::dispatch($kode_klien, null, [$model->id], null);

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return AuditPeriode
     */
    public function update(array $data, int $id): AuditPeriode
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        if ($this->checkReference($id)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Gagal menghapus data');
        }

        return true;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        if ($this->checkReferenceMultiple($ids)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    protected function checkReference($id)
    {
        // Hanya pengecekan di alur sebelum audit dilaksanakan
        $isReferenceTarget = TargetIndikator::where('id_audit_periode', $id)->exists();
        $isReferenceJadwalAudit = JadwalAudit::where('id_audit_periode', $id)->exists();
        $isReferenceSKAuditor = SkAuditor::where('id_audit_periode', $id)->exists();
        $isReferenceSuratTugas = SuratTugasAuditor::where('id_audit_periode', $id)->exists();

        return $isReferenceTarget || $isReferenceJadwalAudit || $isReferenceSKAuditor || $isReferenceSuratTugas;
    }

    protected function checkReferenceMultiple($ids)
    {
        // Hanya pengecekan di alur sebelum audit dilaksanakan
        $isReferenceTarget = TargetIndikator::whereIn('id_audit_periode', $ids)->exists();
        $isReferenceJadwalAudit = JadwalAudit::whereIn('id_audit_periode', $ids)->exists();
        $isReferenceSKAuditor = SkAuditor::whereIn('id_audit_periode', $ids)->exists();
        $isReferenceSuratTugas = SuratTugasAuditor::whereIn('id_audit_periode', $ids)->exists();

        return $isReferenceTarget || $isReferenceJadwalAudit || $isReferenceSKAuditor || $isReferenceSuratTugas;
    }


    public function syncFromSiakadv1()
    {
        $years = range(now()->year - 5, now()->year);
        rsort($years);

        $yearsFilter = implode("', '", $years);

        $sql =
            "SELECT
                SUBSTR(idperiode,5,1) periode,
                idtahunajaran tahun,
                tanggalawal,
                tanggalakhir
            FROM ref.ms_periode
            WHERE idtahunajaran IN('{$yearsFilter}')
                AND SUBSTR(idperiode,5,1) IN ('1', '2')
            ORDER BY idtahunajaran DESC, SUBSTR(idperiode,5,1) ASC";

        $data = DB::connection('siakadv1')->select($sql);

        // Mapping ambil periode awal dan akhir sesuai tahun ajaran
        $recentYear = null;
        $listPeriode = [];
        foreach ($data as $item) {
            if (empty($recentYear)) {
                $listPeriode[$item->tahun][] = $item;

                $recentYear = $item->tahun;
                continue;
            }

            $listPeriode[$item->tahun][] = $item;
            $recentYear = $item->tahun;
        }

        DB::beginTransaction();

        $err = false;
        $message = 'Berhasil sinkronisasi periode audit';

        // Ambil tanggal awal di periode terkecil dan tanggal akhir di periode terbesar
        foreach ($listPeriode as $tahun => $periode) {
            $model = $this->model->findByYear($tahun);
            $tanggalMulai = $model?->tanggal_mulai;
            $tanggalSelesai = $model?->tanggal_selesai;

            usort($periode, function ($a, $b) {
                return $a->periode <=> $b->periode;
            });

            $firstPeriode = $periode[0];

            if (count($periode) > 1) {
                $lastPeriode = end($periode);
            }

            $data = [
                'tahun_audit' => $tahun,
                'tanggal_mulai' => $firstPeriode->tanggalawal,
                'tanggal_selesai' => $lastPeriode?->tanggalakhir ?? null,
            ];

            if (!empty($tanggalMulai)) {
                unset($data['tanggal_mulai']);
            }

            if (!empty($tanggalSelesai)) {
                unset($data['tanggal_selesai']);
            }

            try {
                $model = $this->model->updateOrCreate(
                    ['tahun_audit' => $tahun],
                    $data
                );

            } catch (\Exception $e) {
                $err = true;
                $message = $e->getMessage();
            }
        }

        $kode_klien = KlienConfig::getKodeKlien();
        ProcessSyncIndikatorBobot::dispatch($kode_klien);

        if ($err) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        return [$err, $message];
    }
}
