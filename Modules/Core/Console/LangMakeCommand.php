<?php

namespace Modules\Core\Console;

use Illuminate\Support\Str;
use Modules\Core\Console\Traits\TableFields;
use Modules\Core\Extensions\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class LangMakeCommand extends GeneratorCommand
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
    protected $name = 'app:make-lang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new translation for the specified module.';

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
        return Stub::create('/custom/lang.stub', [
            'translation' => $this->getTranslation(),
            'module' => $this->getModuleName(),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $langPath = GenerateConfigReader::read('lang');

        return $path . $langPath->getPath() . '/id/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        [, $table] = explode('.', $this->argument('table'));

        return $table;
    }

    /**
     * @return string
     */
    private function getTranslation()
    {
        [, $table] = explode('.', $this->argument('table'));

        $translation = "[
    'main' => '" . Str::headline($table) . "',
";
        foreach ($this->fields as $col => $field) {
            if (in_array($col, $this->skipDefaultColumn)) {
                continue;
            }

            $translation .= "
    '" . $col . "' => '" . ($field->getComment() ?: Str::headline($col)) . "',";
        }
        $translation .= '
]';

        return $translation;
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating translation...');
        $this->initTableFields();

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        return 0;
    }
}
