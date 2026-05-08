<?php

namespace Modules\Core\Database\factories\Shared;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Shared\RoleInternal;

class UserInternalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Shared\UserInternal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('id_ID');

        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $email = strtolower($firstName . '.' . $lastName) . '@sevima.com';

        $roleId = RoleInternal::where('kode_role', RoleInternal::CODE_SUPERADMIN)->first(['id'])->id;

        return [
            'nama_user' => $firstName . ' ' . $lastName,
            'id_role_internal' => $roleId,
            'email_user' => $email,
            'waktu_verifikasi_email' => now(),
        ];
    }
}
