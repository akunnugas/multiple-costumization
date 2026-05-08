<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Services\DatabaseDistributionService;

class MigrateRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'app:migrate-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate role data.';

    protected $registeredRoles = [
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DatabaseDistributionService::runToAllDatabase(function () {
            foreach ($this->registeredRoles as $role) {
                $this->migrateRole($role);
            }
        });

        $this->info('Role data migrated successfully.');
    }

    private function migrateRole($role)
    {
        $roleData = new $role();
        $className = get_class($roleData);

        $this->warn("Migrating role data from {$className}...");

        $roleData->migrate();
    }
}
