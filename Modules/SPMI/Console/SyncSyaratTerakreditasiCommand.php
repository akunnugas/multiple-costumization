<?php

namespace Modules\SPMI\Console;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\SPMI\Services\MigrationService;

class SyncSyaratTerakreditasiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spmi:sync-syarat-terakreditasi {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate to all client database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenant = $this->option('tenant');
        $tenants = $tenant ? explode(',', $tenant) : [];

        if (empty($tenants)) {
            DatabaseDistributionService::runToAllDatabase(function () {
                MigrationService::syncSyaratTerakreditasi();
            });
        } else {
            $runTenant = DatabaseDistributionService::runToSpecificTenant($tenants, function () {
                MigrationService::syncSyaratTerakreditasi();
            });

            if (Error::isError($runTenant)) {
                $this->error($runTenant->message);
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
