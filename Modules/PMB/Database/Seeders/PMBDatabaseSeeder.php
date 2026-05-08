<?php

namespace Modules\PMB\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Seeders\BroadcastRecipientsTableSeeder;

class PMBDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // FIXME: Sementara di comment dulu
        // referensi
        // $this->call(AssessmentTypesTableSeeder::class); // Jenis Seleksi
        // $this->call(AssessmentCompositionsTableSeeder::class); // Komposisi Seleksi
        // $this->call(AssessmentRequirementsTableSeeder::class); // Syarat Seleksi
        // $this->call(SubjectsTableSeeder::class); // Mata Pelajaran
        // $this->call(BroadcastRecipientsTableSeeder::class); // Penerima Broadcast dan Broadcast

        // periode pendaftaran
        // $this->call(ProgramDistributionsTableSeeder::class); // Sebaran Program Studi dan Periode Pendaftarannya
    }
}
