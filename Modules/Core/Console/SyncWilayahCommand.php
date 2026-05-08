<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Jobs\ProcessSyncWilayah;
use Modules\Core\Services\DatabaseDistributionService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SyncWilayahCommand extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'app:sync-wilayah {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data wilayah dari Siakad V1.';

    /**
     * The context of the command.
     *
     * @var string
     */
    private string $context = 'Wilayah';

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
            $this->components->info("Migrating Data $this->context To All Database");

            DatabaseDistributionService::runToAllDatabase(function ($client) {
                return $this->processSync($client);
            });
        } else { // run to specific tenant
            $this->components->info("Migrating Data $this->context To Specific Database");

            DatabaseDistributionService::runToSpecificTenant($tenants, function($client) {
                return $this->processSync($client);
            });
        }

        // finish
        return CommandAlias::SUCCESS;
    }

    /**
     * Synchronize data.
     *
     * @return int
     */
    private function processSync(array $client)
    {
        // Get connection name
        $connection = config('database.connections.pgsql');

        $this->components->info("Start sinkronisasi data $this->context di koneksi: " . $connection['database']);

        ProcessSyncWilayah::dispatch($client);

        $this->components->info("Informasi berhasil/gagal ada di file queue.log");
        return CommandAlias::SUCCESS;
    }
}
