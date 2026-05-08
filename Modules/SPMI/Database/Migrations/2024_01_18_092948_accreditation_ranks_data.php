<?php

use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\AkreditasiPeringkat;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        AkreditasiPeringkat::create([
            'kode_peringkat' => 'U',
            'nama_peringkat_akreditasi' => 'Unggul',
            'nilai_minimal' => 361,
            'nilai_maksimal' => 500,
        ]);

        AkreditasiPeringkat::create([
            'kode_peringkat' => 'S',
            'nama_peringkat_akreditasi' => 'Baik Sekali',
            'nilai_minimal' => 301,
            'nilai_maksimal' => 360,
        ]);

        AkreditasiPeringkat::create([
            'kode_peringkat' => 'G',
            'nama_peringkat_akreditasi' => 'Baik',
            'nilai_minimal' => 201,
            'nilai_maksimal' => 300,
        ]);

        AkreditasiPeringkat::create([
            'kode_peringkat' => 'M',
            'nama_peringkat_akreditasi' => 'Tidak Terakreditasi / Kadaluarsa',
            'nilai_minimal' => 0,
            'nilai_maksimal' => 200,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
