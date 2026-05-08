<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE kerjasama.mitra DROP CONSTRAINT IF EXISTS mitra_tingkat_mitra_check");

        DB::statement("
            ALTER TABLE kerjasama.mitra 
            ADD CONSTRAINT mitra_tingkat_mitra_check 
            CHECK ((tingkat_mitra::text = ANY (ARRAY['L','R','N','I']::text[])))
        ");
    }

    public function down()
    {
        DB::statement("ALTER TABLE kerjasama.mitra DROP CONSTRAINT IF EXISTS mitra_tingkat_mitra_check");
        DB::statement("
            ALTER TABLE kerjasama.mitra 
            ADD CONSTRAINT mitra_tingkat_mitra_check 
            CHECK ((tingkat_mitra::text = ANY (ARRAY['R','N','I']::text[])))
        ");
    }
};
