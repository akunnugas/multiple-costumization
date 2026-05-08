<?php

namespace Modules\Core\Console;

use Illuminate\Support\Str;
use Modules\Core\Console\Traits\TableFields;
use Modules\Core\Extensions\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class ModelMakeCommand extends GeneratorCommand
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
    protected $name = 'app:make-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for the specified module.';

    /**
     * The skip default column.
     *
     * @var array
     */
    protected $skipDefaultColumn =['waktu_dibuat', 'dibuat_oleh', 'waktu_diubah', 'diubah_oleh', 'waktu_dihapus', 'dihapus_oleh'];

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
     * @return mixed
     * @throws \InvalidArgumentException
     *
     */
    protected function getTemplateContents()
    {
        return Stub::create('/custom/model.stub', [
            'class' => $this->getModelName(),
            'fillable' => $this->getFillable(),
            'module' => $this->getModuleName(),
            'rules' => $this->getRules(),
            'table' => $this->argument('table'),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('model');

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . '.php';
    }

    /**
     * @return string
     */
    private function getFillable()
    {
        if (empty($this->fields)) {
            return '[]';
        }

        $fillable = '[';
        foreach ($this->fields as $field) {
            $fieldName = $field->getName();
            if (in_array($fieldName, $this->skipDefaultColumn)) {
                continue;
            }

            $fillable .= "
        '" . $fieldName . "',";
        }
        $fillable .= '
    ]';

        return $fillable;
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
     * @return string
     */
    private function getRules()
    {
        if (empty($this->fields)) {
            return '[]';
        }

        $rules = [];
        foreach ($this->fields as $field) {
            $col = $field->getName();
            $type = $field->getType()->getName();

            if (in_array($col, $this->skipDefaultColumn)) {
                continue;
            }

            $rule = [];

            // required
            if ($field->getNotnull()) {
                $rule['required'] = true;
            }

            // string
            if ($type == 'string') {
                $rule['maxlength'] = $field->getLength();
            }

            // date
            if ($type == 'datetimetz') {
                if (Str::startsWith($col, 'tanggal_')) {
                    $rule['type'] = 'date';
                } elseif (Str::startsWith($col, 'waktu_')) {
                    $rule['type'] = 'timestamp';
                }
            }

            // integer
            if ($type == 'integer' || $type == 'bigint') {
                if (Str::startsWith($col, 'id_')) {
                    $table = Str::replaceFirst('id_', '', $col);
                    $rule['options'] = Str::studly($table) . '::class';
                } else {
                    $rule['type'] = 'integer';
                }
            }

            // guid
            if ($type == 'guid') {
                $rule['type'] = 'uuid';
            }

            // default type
            if ($type == 'boolean') {
                $rule['type'] = $type;
            }

            // email
            if (Str::startsWith($col, 'email')) {
                $rule['type'] = 'email';
            }

            // options
            if ($type == 'integer' && Str::endsWith($col, '_id')) {
                $rule['type'] = 'email';
            }

            // add rule
            $rules[$col] = $rule;
        }

        $rules = array_map(function ($rule) {
            $elems = [];
            foreach ($rule as $k => $v) {
                $val = $v;
                if (is_bool($val)) {
                    $val = empty($val) ? 'false' : 'true';
                } elseif (is_string($val) && !Str::endsWith($val, '::class')) {
                    $val = "'$val'";
                }

                $elems[] = "'$k' => $val";
            }

            return $elems;
        }, $rules);

        $str = '[';
        foreach ($rules as $col => $rule) {
            // handle if comment is empty
            $comment = $this->fields[$col]->getComment();

            $str .= "
        '$col' => [" . implode(', ', $rule) . "], ";
        if (!empty($comment)) {
            $str .= "// $comment";
        }
        }
        $str .= '
    ]';

        return $str;
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating model...');
        $this->initTableFields();

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        return 0;
    }
}
