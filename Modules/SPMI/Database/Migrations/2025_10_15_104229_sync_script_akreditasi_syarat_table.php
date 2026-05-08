<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\AccreditationSyncManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        list($err, $msg) = AccreditationSyncManagementService::syncAkreditasiSyarat();

        $akreditasi_peringkat = AkreditasiPeringkat::all();
        $penilaian_panduan = PenilaianPanduan::select('id')->get();

        try {
            DB::beginTransaction();
            // Flag all data akreditasi peringkat
            foreach ($penilaian_panduan as $panduan) {
                $payload = [];
                foreach ($akreditasi_peringkat as $peringkat) {
                    $payload[] = [
                        'kode_peringkat' => $peringkat->kode_peringkat,
                        'nama_peringkat_akreditasi' => $peringkat->nama_peringkat_akreditasi,
                        'nilai_minimal' => $peringkat->nilai_minimal,
                        'nilai_maksimal' => $peringkat->nilai_maksimal,
                        'deskripsi' => $peringkat->deskripsi,
                        'id_penilaian_panduan' => $panduan->id,
                    ];
                }

                AkreditasiPeringkat::insert($payload);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // Force ID
        $new_akreditasi_peringkat = AkreditasiPeringkat::whereNotNull('id_penilaian_panduan')->get();
        $akreditasi_syarat = AkreditasiSyarat::all();

        foreach ($akreditasi_syarat as $syarat) {
            $old = $akreditasi_peringkat->where('id', $syarat->id_akreditasi_peringkat)->first();
            $new = $new_akreditasi_peringkat
                ->where('id_penilaian_panduan', $syarat->id_penilaian_panduan)
                ->where('kode_peringkat', $old->kode_peringkat)
                ->first();

            if ($new) {
                AkreditasiSyarat::where('id', $syarat->id)->update([
                    'id_akreditasi_peringkat' => $new->id,
                ]);
            }
        }

        // AkreditasiPeringkat::whereNotNull('id_penilaian_panduan')->delete();
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
