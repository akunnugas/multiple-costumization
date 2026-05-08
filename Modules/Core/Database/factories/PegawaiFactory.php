<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;
use Modules\HR\Models\JabatanAkademik;
use Modules\HR\Models\EmployeeStatus;
use Modules\HR\Models\FunctionalPosition;
use Modules\HR\Models\StructuralPosition;
use Modules\HR\Models\WorkRelation;

class PegawaiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Pegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_biodata' => Biodata::factory()->create(
                [
                    'gelar_depan' => fake()->randomElement(['Dr.', 'Drs.', 'Dra.', 'Ir.', 'H.', 'Hj.', 'Prof.', 'Drh.']),
                    'gelar_belakang' => fake()->randomElement(['S.K', 'S.E', 'S.T', 'S.Pd', 'S.Psi', 'S.Sos', 'S.H',]),
                ]
            )->id,
            'nip' => fake()->randomNumber(9),
            'id_unit_kerja' => UnitKerja::factory()->create()->id,
            'id_status_pegawai' => EmployeeStatus::factory()->create()->id,
            'id_hubungan_kerja' => WorkRelation::factory()->create()->id,
            'nip_pns' => fake()->randomNumber(9),
            'email_kampus' => strtolower(fake()->firstName()) . '@email.com',
            'akun_sidik_jari' => substr(fake()->uuid(), 0, 25),
            'id_jabatan_struktural_atasan' => StructuralPosition::factory()->create()->id,
            'id_jabatan_akademik' => JabatanAkademik::factory()->create()->id,
            'id_jabatan_fungsional' => FunctionalPosition::factory()->create()->id,
        ];
    }
}
