<?php

namespace Modules\Core\Console;

use Modules\Core\Extensions\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class ModelTestMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'model';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:make-model-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model test for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of model will be used.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        return Stub::create('/custom/model-test.stub', [
            'model' => $this->getClass(),
            'module' => $this->getModuleName(),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        return $path . 'Tests/Model/' . $this->getClass() . 'ModelTest.php';
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating model test...');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        $this->call('app:make-model-test-fixture', ['model' => $this->argument('model'), 'module' => $this->argument('module')]);

        return 0;
    }
}
