<?php

namespace Modules\Core\Console;

use Illuminate\Support\Str;
use Modules\Core\Console\Traits\TableFields;
use Modules\Core\Extensions\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class FactoryMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait, TableFields;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'table';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:make-factory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new factory for the specified module.';

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
            ['table', InputArgument::REQUIRED, 'The name of table will be used.'],
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
        return Stub::create('/custom/factory.stub', [
            'definition' => $this->getFactoryDefinition(),
            'model' => $this->getModelName(),
            'module' => $this->getModuleName(),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('factory');

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . 'Factory.php';
    }

    /**
     * @return string
     */
    private function getFactoryDefinition()
    {
        if (empty($this->fields)) {
            return '[]';
        }

        $definition = '[';
        foreach ($this->fields as $field) {
            $col = $field->getName();

            if (in_array($col, $this->skipDefaultColumn)) {
                continue;
            }

            $length = $field->getLength();
            $type = $field->getType()->getName();

            if (Str::startsWith($col, 'id_')) {
                $table = Str::replaceFirst('id_', '', $col);
                $value = Str::studly($table) . '::factory()->create()->id';
            } else if (Str::startsWith($col, 'email')) {
                $value = "strtolower(fake()->firstName() . '.' . fake()->lastName()) . '@email.com'";
            } else {
                $param = '';
                if ($col == 'name') {
                    $method = 'name';
                } else if ($type == 'boolean') {
                    $method = 'boolean';
                } else if ($type == 'datetimetz') {
                    $method = 'iso8601';
                } else if ($type == 'guid') {
                    $method = 'uuid';
                } else if ($type == 'integer') {
                    $method = 'randomNumber';
                    $param = 5;
                } elseif ($type == 'decimal') {
                    $method = 'randomFloat';
                    $param = '2, 1, 100';
                } else {
                    $method = 'text';
                    $param = $length;
                }

                $value = 'fake()->' . $method . '(' . $param . ')';
            }

            $definition .= "
            '" . $col . "' => " . $value . ",";
        }
        $definition .= '
        ]';

        return $definition;
    }

    /**
     * @return string
     */
    private function getModelName()
    {
        [, $table] = explode('.', $this->argument('table'));

        return Str::studly($table);
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating factory...');
        $this->initTableFields();

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        return 0;
    }
}
