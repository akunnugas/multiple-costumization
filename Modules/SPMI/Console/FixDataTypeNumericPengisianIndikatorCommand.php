<?php

namespace Modules\SPMI\Console;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\SPMI\Services\MigrationService;

class FixDataTypeNumericPengisianIndikatorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spmi:fix-data-type-numeric-pengisian {--tenant=} {--id=} {--kode=} {--butir=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbaikan data type pengisian indikator';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenant = $this->option('tenant');
        $tenants = $tenant ? explode(',', $tenant) : [];

        $idPengisianIndikator = $this->option('id');
        $kodePengisian = $this->option('kode');
        $butirPengisian = $this->option('butir');

        if (empty($tenants)) {
            DatabaseDistributionService::runToAllDatabase(function () use ($idPengisianIndikator, $kodePengisian, $butirPengisian) {
                MigrationService::fixDataTypeNumericPengisianIndikator($idPengisianIndikator, $kodePengisian, $butirPengisian);
            });
        } else {
            $runTenant = DatabaseDistributionService::runToSpecificTenant($tenants, function () use ($idPengisianIndikator, $kodePengisian, $butirPengisian) {
                MigrationService::fixDataTypeNumericPengisianIndikator($idPengisianIndikator, $kodePengisian, $butirPengisian);
            });

            if (Error::isError($runTenant)) {
                $this->error($runTenant->message);
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
