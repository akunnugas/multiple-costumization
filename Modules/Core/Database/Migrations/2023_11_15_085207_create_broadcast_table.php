<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Broadcast;
use Modules\PMB\Models\Pendaftar;
use Modules\PMB\Models\PeriodePendaftaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.broadcast', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi')->nullable();
            $table->boolean('apakah_email')->default(false);
            $table->boolean('apakah_sms')->default(false);
            $table->boolean('apakah_whatsapp')->default(false);
            $table->integer('jumlah_penerima')->nullable();
            $table->integer('jumlah_email_terkirim')->nullable();
            $table->integer('jumlah_whatsapp_terkirim')->nullable();
            $table->integer('jumlah_sms_terkirim')->nullable();
            $table->boolean('apakah_terkirim')->default(false);
            $table->char('jenis_modul', 1)->nullable();
            $table->logs(true);
        });

        // penerima brodcast
        SevimaSchema::create('core.broadcast_penerima', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Broadcast::class);
            $table->boolean('apakah_email_terkirim')->default(false);
            $table->boolean('apakah_whatsapp_terkirim')->default(false);
            $table->boolean('apakah_sms_terkirim')->default(false);
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.broadcast_penerima');
        SevimaSchema::dropIfExists('core.broadcast');
    }
};
