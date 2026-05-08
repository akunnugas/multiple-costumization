<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Biodata;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $cleanBiodata = Biodata::withTrashed()
            ->whereNull('ref_key_pegawai')
            ->whereNull('ref_key_mahasiswa')
            ->get();

        foreach ($cleanBiodata as $biodata) {
            try {
                DB::transaction(function () use ($biodata) {
                    $biodata->forceDelete();
                });
            } catch (\Throwable $th) {
                Log::channel('sync')->error('Migration Siakad:', [
                    'message' => $th->getMessage(),
                    'data' => $biodata->toArray(),
                ]);
            }
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
