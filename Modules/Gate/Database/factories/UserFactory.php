<?php

namespace Modules\Gate\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Gate\Models\User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = fake('id_ID');

        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $email = strtolower($firstName . '.' . $lastName) . '@email.com';

        return [
            'nama_user' => $firstName . ' ' . $lastName,
            'email_user' => $email,
            'waktu_verifikasi_email' => now(),
        ];
    }
}

