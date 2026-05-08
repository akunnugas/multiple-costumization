<?php

namespace Modules\SPMI\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Services\AuditPeriodeManagementService;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputOption;

class SyncPeriodeAuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'spmi:sync-periode-audit {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync periode audit dari Siakad V1';

    /**
     * The context of the command.
     *
     * @var string
     */
    private string $context = 'Periode Audit';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['tenant', null, InputOption::VALUE_OPTIONAL, 'Tenant(s) to migrate, separated by comma.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // get tenant option and split by comma
        $tenantOption = $this->option('tenant');
        $tenants = $tenantOption ? explode(',', $tenantOption) : [];

        // run to all tenant
        if (empty($tenants)) {
            $this->components->info("Start Migrating Data $this->context To All Database");

            DatabaseDistributionService::runToAllDatabase(function () {
                $this->processSync();
            });
        } else { // run to specific tenant
            $this->components->info("Start Migrating Data $this->context To Specific Database");

            $response = DatabaseDistributionService::runToSpecificTenant($tenants, function() {
                $this->processSync();
            });

            if (Error::isError($response)) {
                $message = $response?->message;
                $this->components->error($message);
                return CommandAlias::FAILURE;
            }
        }

        // finish
        $this->components->info("Finish Migrating Data $this->context");
        return CommandAlias::SUCCESS;
    }

    /**
     * Synchronize data.
     *
     * @return void
     */
    private function processSync()
    {
        // Get connection name
        $connection = config('database.connections.pgsql');

        // Buat periode 5 tahun kebelakang terlebih dahulu
        $now = now();
        $year = $now->year;
        $years = range($year - 5, $year);

        $periods = [];
        foreach ($years as $year) {
            $periods[] = AuditPeriode::firstOrCreate([
                'tahun_audit' => $year,
            ]);
        }

        // Pembuatan 2 indikator untuk setiap periode audit
        foreach ($periods as $period) {
            $period->indikatorBobot()->firstOrCreate([
                'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT
            ], [
                'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKU,
                'nama_kategori_indikator' => 'Indikator Kinerja Utama',
                'persentase' => 0.00,
            ]);
            $period->indikatorBobot()->firstOrCreate([
                'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT
            ], [
                'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT,
                'nama_kategori_indikator' => 'Indikator Kinerja Tambahan',
                'persentase' => 0.00,
            ]);
        }

        // Jalankan sync periode audit dari Siakad V1
        $service = new AuditPeriodeManagementService();

        [$err, $message] = $service->syncFromSiakadv1();

        if ($err) {
            $this->log('syncFromSiakadv1', new \Exception($message));
        }

        $this->info("Successfully sync $this->context in connection: " . $connection['database']);
    }

    private function log($methodName, $exception)
    {
        Log::channel('scheduler')->error('SPMI - ' . $methodName, [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
