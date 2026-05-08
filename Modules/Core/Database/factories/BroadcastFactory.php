<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Broadcast;

class BroadcastFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Broadcast::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'judul' => fake()->text(255),
            'isi' => fake()->text(),
            'apakah_email' => fake()->boolean(),
            'apakah_sms' => fake()->boolean(),
            'apakah_whatsapp' => fake()->boolean(),
            'jumlah_penerima' => fake()->randomNumber(2),
            'jumlah_email_terkirim' => fake()->randomNumber(2),
            'jumlah_whatsapp_terkirim' => fake()->randomNumber(2),
            'jumlah_sms_terkirim' => fake()->randomNumber(2),
            'apakah_terkirim' => fake()->boolean(),
            'jenis_modul' => fake()->randomElement(Broadcast::MODULE_TYPES),
        ];
    }
}
