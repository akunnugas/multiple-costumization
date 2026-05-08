<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FeatureMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-feature {table} {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make full feature command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');
        $module = $this->argument('module');

        if (!$table) {
            $table = $this->error('Table name is required');
            return E_ERROR;
        }

        if (!$module) {
            $module = $this->error('Module name is required');
            return E_ERROR;
        }

        $this->start($table, $module);

        return 0;
    }

    private function start($table, $module)
    {
        $this->info('Starting...');

        $modelName = $this->getModelName($table);
        $status = 0;

        $status = $this->artisanCall('app:make-model', [
            'table' => $table,
            'module' => $module,
        ]);

        $status = $this->artisanCall('app:make-factory', [
            'table' => $table,
            'module' => $module,
        ]);

        $status = $this->artisanCall('app:make-service', [
            'model' => $modelName,
            'module' => $module,
            '--test' => true,
        ]);

        $status = $this->artisanCall('app:make-controller', [
            'service' => $modelName . 'ManagementService',
            'module' => $module,
            '--lang' => true,
        ]);

        return $status;
    }

    private function artisanCall($command, $params = [])
    {
        $status = 0;
        $status = $this->call($command, $params);
        if ($status !== 0) {
            $this->error('Error when running command: ' . $command);
            return E_ERROR;
        }
        return $status;
    }

    /**
     * @return string
     */
    private function getModelName()
    {
        [, $table] = explode('.', $this->argument('table'));

        return Str::singular(Str::studly($table));
    }
}
