<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;

class AgendaKegiatanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = \Modules\Litabmas\Models\AgendaKegiatan::steps();

        foreach ($steps as $code => $name) {
            // add delay
            sleep(0.5);

            \Modules\Litabmas\Models\AgendaKegiatan::updateOrCreate([
                'code' => $code,
            ], [
                'name' => $name,
            ]);
        }
    }
}
