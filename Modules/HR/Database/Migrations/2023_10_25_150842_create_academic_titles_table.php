<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('hr.academic_titles', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama Gelar');
            $table->string('abbreviation', 30)->nullable()->comment('Singkatan Gelar');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.academic_titles (abbreviation) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.academic_titles');
    }
};
