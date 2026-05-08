<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Broadcast;

class BroadcastPenerimaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\BroadcastPenerima::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_broadcast' => Broadcast::factory()->create()->id,
            'apakah_email_terkirim' => fake()->boolean(),
            'apakah_whatsapp_terkirim' => fake()->boolean(),
            'apakah_sms_terkirim' => fake()->boolean(),
        ];
    }
}
