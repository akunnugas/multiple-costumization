<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::table('spmi.jadwal_audit', function (Blueprint $table) {
            $table->string('nama_jadwal_audit')->nullable()->after('id_audit_periode');
        });

        // Migrate default data: set nama_jadwal_audit based on audit periode year + penilaian mandiri status
        DB::statement("
            UPDATE spmi.jadwal_audit ja
            SET nama_jadwal_audit = CONCAT(
                'Jadwal AMI ',
                ap.tahun_audit
            )
            FROM spmi.audit_periode ap
            WHERE ap.id = ja.id_audit_periode
                AND ja.nama_jadwal_audit IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::table('spmi.jadwal_audit', function (Blueprint $table) {
            $table->dropColumn('nama_jadwal_audit');
        });
    }
};
