<?php

namespace Modules\SPMI\Console;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\SPMI\Data\PenilaianMatriks\IAPS51S1AKRE;
use Modules\SPMI\Data\PenilaianMatriks\IAPS51S1UNG;
use Modules\SPMI\Data\PenilaianMatriks\IAPS51S2AKRE;
use Modules\SPMI\Data\PenilaianMatriks\IAPS51S2UNG;
use Modules\SPMI\Data\PenilaianMatriks\IAPSD3;
use Modules\SPMI\Data\PenilaianMatriks\IAPSD4;
use Modules\SPMI\Data\PenilaianMatriks\IAPSS1;
use Modules\SPMI\Data\PenilaianMatriks\IAPSS2;
use Modules\SPMI\Data\PenilaianMatriks\IAPSS3;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputOption;

class MigrateRumusPenilaianCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'spmi:migrate-rumus-penilaian {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migration rumus penilaian';

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

    protected $rumusClass = [
        IAPS51S1UNG::class,
        IAPS51S1AKRE::class,
        IAPS51S2AKRE::class,
        IAPS51S2UNG::class,
        IAPSS1::class,
        IAPSS2::class,
        IAPSS3::class,
        IAPSD3::class,
        IAPSD4::class,
    ];

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
            DatabaseDistributionService::runToAllDatabase(fn() => $this->startMigrate());
        } else {
            DatabaseDistributionService::runToSpecificTenant($tenants, fn() => $this->startMigrate());
        }

        return CommandAlias::SUCCESS;
    }

    protected function startMigrate()
    {
        $connection = config('database.connections.pgsql');

        foreach ($this->rumusClass as $rumus) {
            $rumus = (new $rumus)->startMigrate();

            if (Error::isError($rumus)) {
                $this->error($rumus->message);
                return;
            }
        }

        $this->info('Successfully migrate rumus penilaian: ' . $connection['database']);
    }
}
