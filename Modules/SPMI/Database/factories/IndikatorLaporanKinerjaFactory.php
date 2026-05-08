<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndikatorLaporanKinerjaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorLaporanKinerja::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_pengisian_panduan' => \Modules\SPMI\Models\PengisianPanduan::factory(),
            'nomor_indikator' => substr(fake()->word(), 0, 3),
            'name' => fake()->word(),
            'description' => fake()->paragraph(),
            'information' => fake()->paragraph(),
            'jenis_form' => 'FR',
            'is_layout_fixed' => true,
            'apakah_menggunakan_kategori' => true,
            'is_category_manual_input' => true,
            'is_using_ts' => true,
            'layout_type' => fake()->randomElement(['L','P']),
            'data_source' => fake()->randomElement(['MI']),
            'deskripsi_sumber_data' => fake()->paragraph(),
            'is_import_excel' => true,
            'is_view_in_report' => true,
            'is_view_name_in_report' => true,
            'is_active' => true,
        ];
    }
}
