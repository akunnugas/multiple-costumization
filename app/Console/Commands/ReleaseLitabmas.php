<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Schema;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Core\Helpers\Error;

class ReleaseLitabmas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-litabmas {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        global $litabmas;
        // get file litabmas.json from this path
        $litabmas = json_decode(file_get_contents(base_path('app/Console/Commands/litabmas.json')), true);

        // get tenant option and split by comma
        $tenantOption = $this->option('tenant');
        $tenants = $tenantOption ? explode(',', $tenantOption) : [];

        // run to all tenant
        if (empty($tenants)) {
            echo 'Start Release Litabmas To All Database' . PHP_EOL;

            DatabaseDistributionService::runToAllDatabase(function () {
                global $litabmas;
                // Get connection name
                $connection = config('database.connections.pgsql');

                DB::table('public.migrations')->whereIn('migration', $litabmas)->delete();

                // Drops the entire schema if it exists
                DB::statement('DROP SCHEMA IF EXISTS litabmas CASCADE');
                echo 'RUN MIGRATION Pre-Release LITABMAS : ' . $connection['database'] . PHP_EOL;
            });
        } else { // run to specific tenant
            echo 'Start Release Litabmas To Specific Database' . PHP_EOL;

            $response = DatabaseDistributionService::runToSpecificTenant($tenants, function () use ($litabmas, $tenantOption) {

                DB::table('public.migrations')->whereIn('migration', $litabmas)->delete();

                // Drops the entire schema if it exists
                DB::statement('DROP SCHEMA IF EXISTS litabmas CASCADE');

                echo 'RUN MIGRATION Pre-Release LITABMAS : ' . $tenantOption . PHP_EOL;
            });

            if (Error::isError($response)) {
                $message = $response?->message;
                echo $message . PHP_EOL;
            } else {
                echo 'Finish Release Litabmas' . PHP_EOL;
            }
        }
    }
}
