<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\ServiceReturn;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Core\Services\WilayahManagementService;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\PenilaianPanduan;

class ProcessSyncIndikatorBobot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private string|int $kode_klien,
        private array|null $unit_ids = null,
        private array|null $audit_periode_ids = null,
        private array|null $penilaian_panduan_ids = null,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DatabaseDistributionService::runToSpecificTenant([$this->kode_klien], function () {
            $this->sync();
        });
    }

    public function sync()
    {
        $units = UnitKerja::select('id', 'jenis_unit')
            ->whereIn('jenis_unit', [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI])
            ->when($this->unit_ids, function ($query) {
                $query->whereIn('id', $this->unit_ids);
            })
            ->get();
        $auditPeriode = AuditPeriode::select('id')
            ->pluck('id')
            ->when($this->audit_periode_ids, function ($query) {
                $query->whereIn('id', $this->audit_periode_ids);
            })
            ->toArray();
        $penilaianPanduan = PenilaianPanduan::select('id', 'apakah_data_default')
            ->when($this->penilaian_panduan_ids, function ($query) {
                $query->whereIn('id', $this->penilaian_panduan_ids);
            })
            ->get();

        foreach ($units as $unit) {
            foreach ($auditPeriode as $periode) {
                foreach ($penilaianPanduan as $panduan) {
                    IndikatorBobot::firstOrCreate([
                        'id_audit_periode' => $periode,
                        'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKU,
                        'id_unit' => $unit->id,
                        'id_penilaian_panduan' => $panduan->id,
                    ], [
                        'id_audit_periode' => $periode,
                        'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKU,
                        'nama_kategori_indikator' => 'Indikator Kinerja Utama',
                        'id_unit' => $unit->id,
                        'persentase' => $panduan->apakah_data_default ? 0.00 : 100.00,
                        'id_penilaian_panduan' => $panduan->id,
                    ]);

                    IndikatorBobot::firstOrCreate([
                        'id_audit_periode' => $periode,
                        'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT,
                        'id_unit' => $unit->id,
                        'id_penilaian_panduan' => $panduan->id,
                    ], [
                        'id_audit_periode' => $periode,
                        'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT,
                        'nama_kategori_indikator' => 'Indikator Kinerja Tambahan',
                        'id_unit' => $unit->id,
                        'persentase' => $panduan->apakah_data_default ? 0.00 : 100.00,
                        'id_penilaian_panduan' => $panduan->id,
                    ]);
                }
            }
        }
    }

    /**
     * Handle final failed job.
     *
     * @param \Throwable $e
     * @return void
     */
    public function failed(\Throwable $e): void
    {
        Log::channel('queue')->error('Failed Sync: SPMI - ProcessSyncIndikatorBobot', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
