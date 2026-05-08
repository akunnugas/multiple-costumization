<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Models\Tag;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('dms.dokumen_tag', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Dokumen::class, 'id_dokumen');
            $table->foreignIdTo(Tag::class, 'id_tag');
            $table->logs(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('dms.dokumen_tag');
    }
};
