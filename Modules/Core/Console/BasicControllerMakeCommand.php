<?php

namespace Modules\Core\Console;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Modules\Core\Extensions\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use ReflectionProperty;
use Symfony\Component\Console\Input\InputOption;

class BasicControllerMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller for the specified module.';

    /**
     * The skip default column.
     *
     * @var array
     */
    protected array $skipDefaultColumn = [
        'waktu_dibuat', 'dibuat_oleh', 'waktu_diubah', 'diubah_oleh', 'waktu_dihapus', 'dihapus_oleh'
    ];

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['service', InputArgument::REQUIRED, 'The service controller will be created.'],
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
            ['lang', null, InputOption::VALUE_NONE, 'With resource lang.'],
            ['origin', null, InputOption::VALUE_OPTIONAL, 'Origin service'],
        ];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $attributes = $this->getAttributes();
        $formField = $this->getField($attributes->model);
        return Stub::create('/custom/resource-controller.stub', [
            'service' => $attributes->serviceName,
            'field' => $formField,
            'model' => $attributes->modelName,
            'module' => $attributes->module,
            'origin' => $attributes->origin,
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $validPath = $path . 'Http/Controllers/Web/' . $this->getFileName() . '.php';
        return $validPath;
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        $attributes = $this->getAttributes();
        return $attributes->modelName . 'Controller';
    }

    private function getAttributes()
    {
        $origin = $this->option('origin');
        $service = $this->argument('service');
        $module = $this->getModuleName();
        $serviceOrigin = isset($origin) ? $origin : $this->getModuleName();
        $serviceNamespace = "Modules\\$serviceOrigin\\Services\\" . $service;
        $class = App::make($serviceNamespace);
        $classReflect = new ReflectionProperty($class, 'model');
        $classReflect->setAccessible(true);
        $model = $classReflect->getValue($class);
        $modelName = explode('\\', get_class($model));
        $modelName = end($modelName);
        $table = $model->getTable();
        return (object) [
            'serviceName' => $service,
            'service' => $class,
            'module' => $module,
            'model' => $model,
            'modelName' => $modelName,
            'table' => $table,
            'origin' => $serviceOrigin,
        ];
    }

    private function getField(Model $model)
    {
        $rules = $model::rules();
        $fields = array_keys($rules);
        $fields = array_filter($fields, function ($field) {
            return !preg_match('/_id$/', $field);
        });
        $fields = array_values($fields);
        $formField = '';
        foreach ($fields as $i => $field) {
            if (in_array($field, $this->skipDefaultColumn)) {
                continue;
            }

            $formField .= ($i > 0 ? "\t\t\t" : '') . "['field' => '$field']," . ($i < count($fields) - 1 ? "\n" : '');
        }
        return $formField;
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {

        $this->components->info('Creating controller...');

        if ($this->option('lang') == true) {
            $attributes = $this->getAttributes();
            $this->call('app:make-lang', [
                'table' => $attributes->table,
                'module' => $attributes->module,
            ]);
        }

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        return 0;
    }
}
