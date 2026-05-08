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
        SevimaSchema::create('hr.field_studies', function (SevimaBlueprint $table) {
            $table->id();
            $table->bigInteger("parent_id")->nullable()->comment('Parent Bidang Ilmu');
            $table->foreign('parent_id')->references('id')->on('hr.field_studies');
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Bidang Ilmu');
            $table->integer('depth')->nullable()->comment('Level');
            $table->integer('info_left')->nullable()->comment('Info Left');
            $table->integer('info_right')->nullable()->comment('Info Right');
            $table->logs(true);

            // create index
            $table->index(['parent_id','info_left','info_right']);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.field_studies (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.field_studies');
    }
};
