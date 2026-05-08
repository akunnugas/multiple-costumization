<?php

namespace Modules\SPMI\Console;

use Illuminate\Console\Command;
use Modules\SPMI\Services\AccreditationSyncManagementService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateSpmeToSpmi extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:migrate-spme';

    /**
     * The console command description.
     */
    protected $description = 'Migrate Data SPME to SPMI.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // // start
        $this->components->info('Prepare Migrate Data SPME to SPMI');

        // sync accreditation agecies
        $this->components->info('Sync Accreditation Agencies');
        list($err, $msg) = AccreditationSyncManagementService::syncAccreditationAgencies($this->components);
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);

        // sync accreditation books
        $this->components->info('Sync Accreditation Books');
        list($err, $msg) = AccreditationSyncManagementService::syncAkreditasiBuku($this->components);
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);

        // sync filling guides
        $this->components->info('Sync Filling Guides');
        list($err, $msg) = AccreditationSyncManagementService::syncPengisianPanduan('BANPT', ['IAPS9', 'LEDPS9']);
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);

        // sync indicator self evaluation
        $this->components->info('Sync Indicator Self Evaluation');
        list($err, $msg) = AccreditationSyncManagementService::syncIndicatorSelfEvaluation('LEDPS9');
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);

        // sync indicator performance reports
        $this->components->info('Sync Indicator Performance Reports');
        list($err, $msg) = AccreditationSyncManagementService::syncIndicatorPerformanceReports('IAPS9');
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);

        $this->migratePenilaianPanduan();

        $this->migrateAssessmentMatrices();

        $this->migrateAkreditasiSyarat();
    }

    protected function migratePenilaianPanduan()
    {
        // sync assessment matrix
        $this->components->info('Sync Assessment Guide');
        [$err, $msg] = AccreditationSyncManagementService::syncPenilaianPanduan();
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);
    }

    protected function migrateAssessmentMatrices()
    {
        $this->components->info('Sync Assessment Matrices');
        [$err, $msg] = AccreditationSyncManagementService::syncPenilaianMatrix();
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);
    }

    protected function migrateAkreditasiSyarat()
    {
        $this->components->info('Sync Accreditation Requirements');
        [$err, $msg] = AccreditationSyncManagementService::syncAkreditasiSyarat();
        if ($err) {
            $this->components->error($msg);
            return E_ERROR;
        }
        $this->components->info($msg);
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
