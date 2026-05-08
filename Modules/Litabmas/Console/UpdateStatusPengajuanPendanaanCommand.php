<?php

namespace Modules\Litabmas\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\CronStatusAgendaKegiatanService;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateStatusPengajuanPendanaanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'litabmas:update-status-pengajuan-pendanaan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status pengajuan pendanaan berdasarkan agenda kegiatan.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // start
        $this->components->info(now() . ' - Start Update Status Pengajuan Pendanaan');

        DatabaseDistributionService::runToAllDatabase(function () {
            $connection = config('database.connections.pgsql');
            $service = new CronStatusAgendaKegiatanService();

            // 1. Diajukan, Konfirmasi Anggota (Pendaftaran) -> Proses Seleksi Administrasi
            try {
                $service->updateStatusMasukSeleksiAdministrasi();
            } catch (Exception $e) {
                $this->log(now() . ' - updateStatusMasukSeleksiAdministrasi', $e);
            }

            // 2. Update status dynamic agenda
            try {
                $service->updateStatusProposalToNext();
            } catch (Exception $e) {
                $this->log(now() . ' - updateStatusProposalToNext', $e);
            }

            $this->components->info(now() . ' - Berhasil update status pengajuan pendanaan di koneksi: ' . $connection['database']);
        });

        // finish
        $this->components->info(now() . ' - Finish Update Status Pengajuan Pendanaan');
        return CommandAlias::SUCCESS;
    }

    private function log($methodName, $exception)
    {
        Log::channel('scheduler')->error('Litabmas - ' . $methodName, [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
