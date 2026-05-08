<?php

namespace Modules\Litabmas\Console;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Litabmas\Services\JenisOutcomePenelitianService;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputOption;

class MigrateJenisPublikasiFromSiakadV1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'app:migrate-jenis-publikasi-from-siakad-v1 {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Data Jenis Publikasi dari Siakad V1.';

    /**
     * The context of the command.
     *
     * @var string
     */
    private string $context = 'Jenis Publikasi';

    /**
     * Create a new command instance.
     *
     * @return void
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
     * @return int
     */
    private function processSync()
    {
        // Get connection name
        $connection = config('database.connections.pgsql');

        list($err, $msg) = (new JenisOutcomePenelitianService())->jenisPublikasiFromHRSiakadV1();

        if ($err) {
            $this->components->error($msg);
            return CommandAlias::FAILURE;
        }

        $this->components->info("Berhasil sinkronisasi data $this->context di koneksi: " . $connection['database']);
        return CommandAlias::SUCCESS;
    }
}
