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
        SevimaSchema::create('log.tables', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias');
            $table->text('query')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('is_active');
            $table->logs();
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('log.tables');
    }
};
