<?php

namespace Modules\Gate\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\FakeOrganizationsTableSeeder;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Models\UserRole;

class FakeUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // hanya jika tidak ada user
        if (User::exists()) {
            exit;
        }

        // organization
        $organizations = UnitKerja::get()->toArray();
        if (empty($organizations)) {
            $this->call(FakeOrganizationsTableSeeder::class);
            $organizations = UnitKerja::get()->toArray();
        }

        // role
        $roles = [];
        foreach (Role::get()->toArray() as $role) {
            $roles[$role['code']] = $role['id'];
        }

        // per organization
        foreach($organizations as $organization) {
            if (empty($organization['depth'])) {
                $roleId = $roles['admpt'];
                $userNumber = 1;
            } elseif ($organization['info_right'] == $organization['info_left'] + 1) {
                $roleId = $roles['mhs'];
                $userNumber = 3;
            } else {
                $roleId = $roles['dosen'];
                $userNumber = 2;
            }

            $users = User::factory($userNumber)->create()->toArray();

            foreach($users as $user) {
                UserRole::create([
                    'user_id' => $user['id'],
                    'role_id' => $roleId,
                    'organization_id' => $organization['id']
                ]);
            }
        }
    }
}
