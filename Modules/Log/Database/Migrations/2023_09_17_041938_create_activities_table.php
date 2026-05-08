<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('log.activities', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id');
            $table->unsignedBigInteger('record_id');
            $table->char('activity', 1);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('table_id')->references('id')->on('log.tables');
            $table->index('table_id');
            $table->index('record_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('log.activities');
    }
};
