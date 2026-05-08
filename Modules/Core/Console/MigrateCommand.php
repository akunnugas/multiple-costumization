<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Modules\Core\Services\DatabaseDistributionService;
use Symfony\Component\Console\Command\Command as CommandAlias;

class MigrateCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate
        {--shared : migrate ke database shared}
        {--fresh : fresh migrate}
        {--seed : seed database}
        {--tenant= : run migrate for specific tenant}
        {--rollback= : rollback database dengan spesifik step}';

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
        $rollback = $this->option('rollback');
        $shared = $this->option('shared');
        $fresh = $this->option('fresh');
        $tenant = $this->option('tenant');
        $tenants = $tenant ? explode(',', $tenant) : [];
        $outputBuffer = $this->getOutput();

        if (empty($rollback)) { // migrate
//            $confirmMessage = 'Apakah anda yakin akan melakukan migrate '
//                . ($fresh ? ' dengan opsi fresh' : null)
//                . ($shared ? ' ke database shared' : 'ke semua database client')
//                . '?';
//            $confirmation = $this->confirm($confirmMessage);
//            if (!$confirmation) {
//                $this->info('Migrate dibatalkan.');
//                return CommandAlias::SUCCESS;
//            }

            if (empty($shared)) { // db all client
                DatabaseDistributionService::migrate($outputBuffer, $fresh, $tenants);
            } else { // db shared
                DatabaseDistributionService::migrateToShared($outputBuffer, $fresh);
            }

            if ($this->option('seed')) {
                $this->call('module:seed');
            }

            return CommandAlias::SUCCESS;
        }

        // rollback
        $confirmation = $this->confirm('Apakah anda yakin akan melakukan rollback database?');
        if (!$confirmation) {
            $this->info('Rollback dibatalkan.');
            return CommandAlias::SUCCESS;
        }

        DatabaseDistributionService::rollback($outputBuffer, $rollback, $tenants);

        return CommandAlias::SUCCESS;
    }
}
