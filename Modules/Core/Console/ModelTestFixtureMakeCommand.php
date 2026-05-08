<?php

namespace Modules\Core\Console;

use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Unique;
use Modules\Core\Extensions\Stub;
use Modules\Core\Helpers\Page;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class ModelTestFixtureMakeCommand extends GeneratorCommand
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
    protected $name = 'app:make-model-test-fixture';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model test fixture for the specified module.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of table will be used.'],
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
        return Stub::create('/custom/model-test-fixture.stub', [
            'content' => $this->getContent(),
        ]);
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        return $path . 'Tests/Model/Fixtures/' . $this->getClass() . 'ModelTest.yml';
    }

    /**
     * @return string
     */
    private function getContent()
    {
        $content = 'validDataset:
' . $this->getContentValidFixture() . '
invalidDataset:
' . $this->getContentInvalidFixture();

        return $content;
    }

    /**
     * @return string
     */
    private function getContentValidFixture()
    {
        $str = "  - case : 'valid input'
    input :";

        $fake = $this->getContentFakeData();
        foreach ($fake as $k => $v) {
            $str .= "
      " . $k . " : " . $this->getContentEscapedValue($v);
        }

        $str .= '
    expected : null';

        return $str;
    }

    /**
     * @return string
     */
    private function getContentInvalidFixture()
    {
        $model = $this->getModelClass();

        $str = '';
        foreach ($model::rules() as $field => $rules) {
            // skip beberapa
            $rules = array_filter($rules, function ($v) {
                return $v != 'nullable';
            });
            if (empty($rules)) {
                continue;
            }

            $str .= "#" . $field . "
";

            foreach ($rules as $rule) {
                $param = $this->getContentRuleProperties($rule, $field, $model);

                $str .= "  - case : '" . $field . ' ' . $param['case'] . "'
    input :";

                $fake = $this->getContentFakeData($model);
                foreach ($fake as $k => $v) {
                    if ($k == $field) {
                        if (!empty($param['invalid_function'])) {
                            $v = $param['invalid_function']($v);
                        } else {
                            $v = $param['invalid'];
                        }
                    }

                    $str .= "
      " . $k . " : " . $this->getContentEscapedValue($v);
                }

                if (!array_key_exists($field, $fake)) {
                    $str .= "
      " . $field . " : " . $this->getContentEscapedValue($param['invalid']);
                }

                $str .= "
    expected : '" . $param['expected'] . "'
";
            }
        }

        return $str;
    }

    /**
     * @return array
     */
    private function getContentRuleProperties(mixed $rule, string $field): array
    {
        if (is_object($rule)) {
            return $this->getContentObjectRuleProperties($rule, $field);
        }

        $rulePart = explode(':', $rule);
        $expected = ['attribute' => $this->getContentAttributeValue($field)];

        // nama case
        if ($rulePart[0] == 'max') {
            $case = 'max ' . $rulePart[1];
        } else {
            $case = 'is ' . $rulePart[0];
        }

        // data salah
        $invalidFunction = null;
        switch ($rulePart[0]) {
            case 'boolean':
                $invalid = 'Valid';
                break;
            case 'date':
                $invalid = 'Date';
                break;
            case 'email':
                $invalid = 'email.at.sevima.com';
                break;
            case 'integer':
                $invalid = 'A';
                break;
            case 'lowercase':
                $invalid = 'A';
                $invalidFunction = fn($v) => strtoupper($v);
                break;
            case 'max':
                $invalid = Str::random($rulePart[1] + 1);
                $invalidFunction = fn($v) => str_pad($v, $rulePart[1] + 1, 'a', STR_PAD_LEFT);
                break;
            case 'uuid':
                $invalid = Str::random(10);
                break;
            default:
                $invalid = null;
        }

        // expected
        $validation = $rulePart[0];
        if ($rulePart[0] == 'max') {
            $validation .= '.string';
            $expected['max'] = $rulePart[1];
        }

        return [
            'case' => $case,
            'invalid' => $invalid,
            'invalid_function' => $invalidFunction,
            'expected' => __('validation.' . $validation, $expected)
        ];
    }

    /**
     * @return array
     */
    protected function getContentObjectRuleProperties(object $rule, string $field): array
    {
        $xrule = [];
        foreach ((array)$rule as $k => $v) {
            $xrule[substr($k, 3)] = $v;
        }

        if ($rule instanceof Exists) {
            $case = 'from ' . $xrule['table'];
            $invalid = 0;
            $validation = 'exists';
        } elseif ($rule instanceof Unique) {
            $case = 'is unique';
            $invalid = 'unique';
            $validation = 'unique';
        } elseif ($rule instanceof In) {
            $case = 'in values';
            $invalid = $xrule['values'][0] . 'x';
            $validation = 'in';
        } else {
            $name = Str::snake(basename(get_class($rule)));
            $case = 'is ' . $name;
            $invalid = Str::random(); // harusnya bukan ini
            $validation = $name;
        }

        return [
            'case' => $case,
            'invalid' => $invalid,
            'expected' => __('validation.' . $validation, ['attribute' => $this->getContentAttributeValue($field)])
        ];
    }

    /**
     * @return string
     */
    private function getContentAttributeValue(string $field)
    {
        $resource = Str::snake($this->getClass());
        $module = $this->laravel['modules']->findOrFail($this->getModuleName())->getLowerName();

        return Page::translateResource($resource, $field, $module);
    }

    /**
     * @return string
     */
    private function getContentEscapedValue($value)
    {
        if (!isset($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return empty($value) ? 'false' : 'true';
        }

        return "'" . $value . "'";
    }

    /**
     * @return array
     */
    private function getContentFakeData()
    {
        $model = $this->getModelClass();
        $data = $model::factory()->make();

        // termasuk field hidden
        $hidden = $data->getHidden();
        if (!empty($hidden)) {
            $data->makeVisible($hidden);
        }

        return $data->toArray();
    }

    /**
     * @return string
     */
    private function getModelClass()
    {
        return '\Modules\\' . $this->getModuleName() . '\Models\\' . $this->getClass();
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        $this->components->info('Creating model test fixture...');

        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        return 0;
    }
}
