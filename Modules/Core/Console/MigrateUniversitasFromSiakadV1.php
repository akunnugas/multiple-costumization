<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Core\Services\PerguruanTinggiManagementService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command as CommandAlias;

class MigrateUniversitasFromSiakadV1 extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:migrate-universitas-from-siakad-v1 {--tenant=} {--limit=}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate Data Universitas From Siakad V1.';

    /**
     * The context of the command.
     *
     * @var string
     */
    private string $context = 'Universitas';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['tenant', null, InputOption::VALUE_OPTIONAL, 'Tenant(s) to migrate, separated by comma.'],
            ['limit', null, InputOption::VALUE_OPTIONAL, 'Limit data.'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get tenant option and split by comma
        $tenantOption = $this->option('tenant');
        $tenants = $tenantOption ? explode(',', $tenantOption) : [];
        // get limit option
        $limit = $this->option('limit');

        // run to all tenant
        if (empty($tenants)) {
            $this->components->info("Start Migrating Data $this->context To All Database");

            DatabaseDistributionService::runToAllDatabase(function () use ($limit) {
                $this->syncUniversitas($limit);
            });
        } else { // run to specific tenant
            $this->components->info("Start Migrating Data $this->context To Specific Database");

            $response = DatabaseDistributionService::runToSpecificTenant($tenants, function() use ($limit) {
                $this->syncUniversitas($limit);
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
     * Synchronize universitas data.
     *
     * @param int|null $limit
     * @return int
     */
    private function syncUniversitas($limit)
    {
        $connection = config('database.connections.pgsql');
        list($err, $msg) = (new PerguruanTinggiManagementService())->syncUniversitasFromSiakadV1($limit);

        if ($err) {
            $this->components->error($msg);
            return CommandAlias::FAILURE;
        }

        $this->components->info("Berhasil sinkronisasi data $this->context di koneksi: " . $connection['database']);
        return CommandAlias::SUCCESS;
    }
}
