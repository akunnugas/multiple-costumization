<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Core\Services\PegawaiManagementService;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputOption;

class SyncKepegawaianCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'app:sync-kepegawaian {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data kepegawaian dari Siakad V1.';

    /**
     * The context of the command.
     *
     * @var string
     */
    private string $context = 'Kepegawaian';

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
            $this->components->info(now() . " - Start Migrating Data $this->context To All Database");

            DatabaseDistributionService::runToAllDatabase(function () {
                $this->processSync();
            });
        } else { // run to specific tenant
            $this->components->info(now() . " - Start Migrating Data $this->context To Specific Database");

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
        $this->components->info(now() . " - Finish Migrating Data $this->context");
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

        [$err, $message] = (new PegawaiManagementService())->syncFromSiakadv1();

        if ($err) {
            $this->log('syncFromSiakadv1', new \Exception($message));
            $this->info(now() . " - Error sync $this->context in connection: " . $connection['database'] . " - " . $message);
        } else {
            $this->info(now() . " - Successfully sync $this->context in connection: " . $connection['database']);
        }
    }

    private function log($methodName, $exception)
    {
        Log::channel('scheduler')->error('Core - ' . $methodName, [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
