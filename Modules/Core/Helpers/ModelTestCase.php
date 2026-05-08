<?php

namespace Modules\Core\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class ModelTestCase extends TestCase
{
    use RefreshDatabase;

    protected $model;

    /**
     * @dataProvider validInputProvider
     */
    public function test_valid_input($input)
    {
        $model = $this->model::create($this->setupInputWithDependency($input));

        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
    }

    /**
     * @dataProvider invalidInputProvider
     */
    public function test_invalid_input($input, $expected)
    {
        $this->assertThrows(function () use ($input) {
            $this->model::create($this->setupInputWithDependency($input));
        }, ValidationException::class, $expected);
    }

    public static function validInputProvider()
    {
        return static::getValidInput(static::getTestName() . '.yml');
    }

    protected static function getValidInput($fileName)
    {
        return static::getCasesByFixtures($fileName);
    }

    public static function invalidInputProvider()
    {
        return static::getInvalidInput(static::getTestName() . '.yml');
    }

    protected static function getInvalidInput($fileName)
    {
        return static::getCasesByFixtures($fileName, false);
    }

    protected function setupInputWithDependency($input)
    {
        foreach ($input as $key => $value) {
            if (Str::startsWith($value, 'dependency.')) {
                // if input has dependency, create dependency
                $model = explode('\\', $this->model);
                $model[count($model) - 1] = str_replace('dependency.', '', $value);
                $model = implode('\\', $model);

                $dep = $model::factory()->create();
                $input[$key] = $dep->id;
            } elseif ($value === 'unique') {
                // if input is unique, create other
                $dep = ($this->model)::factory()->create();
                $input[$key] = $dep->$key;
            }
        }
        return $input;
    }

    protected static function getCasesByFixtures($fileName, $isValid = true)
    {
        $file = explode('\\', get_called_class());
        $file[count($file) - 1] = 'Fixtures/' . $fileName;
        $file = implode('/', $file);

        // load dataset file
        $dataset = Yaml::parseFile($file);

        // ambil yang sesuai aja
        $caseDataset = $dataset[($isValid ? '' : 'in') . 'validDataset'];

        // wrapping untuk return
        $caseInput = [];

        // looping untuk masukin ke array
        for ($i = 0; $i < count($caseDataset); $i++) {
            $caseInput[$caseDataset[$i]['case']] = [$caseDataset[$i]['input'], $caseDataset[$i]['expected']];
        }

        return $caseInput;
    }

    protected static function getTestName()
    {
        return basename(get_called_class());
    }
}
