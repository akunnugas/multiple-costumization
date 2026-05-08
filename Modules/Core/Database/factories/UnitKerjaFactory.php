<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;

class UnitKerjaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\UnitKerja::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $isOrganizationHasUniversity = $this->model::where('jenis_unit', UnitKerja::UNIVERSITY)->exists();
        $generatedType = fake()->randomElement([
            UnitKerja::FACULTY, UnitKerja::STUDY_PROGRAM, UnitKerja::UNIVERSITY
        ]);

        if ($generatedType === UnitKerja::UNIVERSITY && $isOrganizationHasUniversity) {
            $generatedType = fake()->randomElement([
                UnitKerja::FACULTY, UnitKerja::STUDY_PROGRAM
            ]);
        }

        $parent = null;
        if ($generatedType === UnitKerja::STUDY_PROGRAM) {
            $parent = $this->model::where('jenis_unit', UnitKerja::FACULTY)->inRandomOrder()->first();
            $degreeId = JenjangPendidikan::firstOrCreate(
                [
                    'kode_jenjang' => 'S1',
                ],
                [
                    'nama_jenjang' => 'Strata 1',
                    'nama_jenjang_en' => 'Bachelor Degree',
                    'kode_dikti' => fake()->randomNumber(),
                    'apakah_akademik' => true,
                    'apakah_pt' => true,
                    'apakah_pasca' => false,
                    'urutan' => 1,
                ]
            );
        } else if ($generatedType === UnitKerja::FACULTY) {
            $parent = $this->model::where('jenis_unit', UnitKerja::UNIVERSITY)->inRandomOrder()->first();
        }

        return [
            'nama_unit' => fake()->word(),
            'kode_unit' => Str::password(10, false, true, false),
            'kode_dikti' => Str::password(10, false, true, false),
            'jenis_unit' => $generatedType,
            'id_parent' => $parent?->id,
            'info_left' => null,
            'info_right' => null,
            'info_level' => null,
            'apakah_akademik' => fake()->boolean(),
            'apakah_satker' => fake()->boolean(),
            'apakah_aktif' => fake()->boolean(),
            'apakah_aktif_pmb' => fake()->boolean(),
            'id_jenjang_pendidikan' => isset($degreeId) ? $degreeId?->id : null,
        ];
    }
}
