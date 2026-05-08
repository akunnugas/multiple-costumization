<?php

namespace Modules\Core\Console;

use Modules\Core\Extensions\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServiceMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:make-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The service model will be created.'],
            ['module', InputArgument::REQUIRED, 'The name of module will be created.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['test', null, InputOption::VALUE_NONE, 'With service test.'],
        ];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $model = $this->argument('model');
        $module = $this->argument('module');
        return Stub::create('/custom/service.stub', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $validPath = $path . 'Services/' . $this->getFileName() . '.php';
        return $validPath;
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        $model = $this->argument('model');
        return $model . 'ManagementService';
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {

        $this->components->info('Creating service...');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }
        
        if ($this->option('test') == true) {
            $this->call('app:make-service-test', [
                'model' => $this->argument('model'),
                'module' => $this->argument('module'),
            ]);
        }

        return 0;
    }
}
