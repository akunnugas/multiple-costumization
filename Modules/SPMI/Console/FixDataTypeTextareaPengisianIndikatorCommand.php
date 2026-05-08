<?php

namespace Modules\SPMI\Console;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\SPMI\Services\MigrationService;

class FixDataTypeTextareaPengisianIndikatorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spmi:fix-data-type-textarea-pengisian-3-a-1 {--tenant=}';

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

        if (empty($tenants)) {
            DatabaseDistributionService::runToAllDatabase(function () {
                MigrationService::fixDataTypeTextareaPengisianIndikator3a1();
            });
        } else {
            $runTenant = DatabaseDistributionService::runToSpecificTenant($tenants, function () {
                MigrationService::fixDataTypeTextareaPengisianIndikator3a1();
            });

            if (Error::isError($runTenant)) {
                $this->error($runTenant->message);
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
