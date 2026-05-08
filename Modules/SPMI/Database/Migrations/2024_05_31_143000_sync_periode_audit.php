<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Services\AuditPeriodeManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get connection name
        $connection = config('database.connections.pgsql');

        // Buat periode 5 tahun kebelakang terlebih dahulu
        $now = now();
        $year = $now->year;
        $years = range($year - 5, $year);

        $periods = [];
        foreach ($years as $year) {
            $periods[] = AuditPeriode::firstOrCreate([
                'tahun_audit' => $year,
            ]);
        }

        // Pembuatan 2 indikator untuk setiap periode audit
        // foreach ($periods as $period) {
        //     $period->indikatorBobot()->firstOrCreate([
        //         'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT
        //     ], [
        //         'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKU,
        //         'nama_kategori_indikator' => 'Indikator Kinerja Utama',
        //         'persentase' => 0.00,
        //     ]);
        //     $period->indikatorBobot()->firstOrCreate([
        //         'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT
        //     ], [
        //         'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT,
        //         'nama_kategori_indikator' => 'Indikator Kinerja Tambahan',
        //         'persentase' => 0.00,
        //     ]);
        // }

        // Jalankan sync periode audit dari Siakad V1
        $service = new AuditPeriodeManagementService();

        [$err, $message] = $service->syncFromSiakadv1();

        if ($err) {
            logger($message);
            return;
        }
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
