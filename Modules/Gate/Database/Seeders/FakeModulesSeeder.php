<?php

namespace Modules\Gate\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\ResourceAksi;

class FakeModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Modul::where('code', Modul::CODE_SAMPLE)->exists()) {
            exit;
        }

        // module
        $module = Modul::create([
            'name' => 'Contoh',
            'code' => Modul::CODE_SAMPLE,
            'is_active' => true
        ]);

        // resource
        $res1 = Resource::create([
            'module_id' => $module->id,
            'name' => 'List',
            'segment' => 'list',
        ]);
        $res2 = Resource::create([
            'module_id' => $module->id,
            'name' => 'Create',
            'segment' => 'create',
        ]);
        $res3 = Resource::create([
            'module_id' => $module->id,
            'name' => 'Detail',
            'segment' => 'detail',
        ]);

        // resource custom
        $cus1 = ResourceAksi::create([
            'resource_id' => $res1->id,
            'name' => 'List with Sidebar',
            'code' => 'sidebar'
        ]);
        $cus2 = ResourceAksi::create([
            'resource_id' => $res2->id,
            'name' => 'Advanced Create',
            'code' => 'advanced'
        ]);

        // permission
        $sql = "insert into gate.role_permissions (role_id, resource_id, can_get, can_post, can_put, can_delete)
                select r.id, rs.id::int, true, r.code <> 'mhs', r.code <> 'mhs', r.code <> 'mhs'
                from gate.roles r
                join (select ? as id union all select ? union all select ?) rs
                on (r.code = ? or r.code = ? or r.code = ?)";
        DB::statement($sql, [$res1->id, $res2->id, $res3->id, 'admpt', 'dosen', 'mhs']);

        $sql = "insert into gate.role_custom_permissions (role_id, resource_custom_id)
                select r.id, rs.id::int
                from gate.roles r
                join (select ? as id union all select ?) rs on r.code = ?";
        DB::statement($sql, [$cus1->id, $cus2->id, 'admpt']);
    }
}
