<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    private $jenisKegiatan = [
        "Penelitian",
        "Pengabdian",
        "Lain-lain"
    ];

    private $table = 'kerjasama.jenis_kegiatan';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->jenisKegiatan as $jenis) {
            $isExists = DB::table($this->table)
                ->where('nama_jenis_kegiatan', $jenis)
                ->exists();

            if ($isExists) {
                continue;
            }

            DB::table($this->table)->insert([
                'nama_jenis_kegiatan' => $jenis,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table($this->table)
            ->whereIn('nama_jenis_kegiatan', $this->jenisKegiatan)
            ->delete();
    }
};
