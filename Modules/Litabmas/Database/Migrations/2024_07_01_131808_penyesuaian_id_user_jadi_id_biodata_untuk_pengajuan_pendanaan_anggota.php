<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        // STEP 1: prepare
        // get current data anggota
        $data = DB::table('litabmas.pengajuan_pendanaan_anggota')->get();
        // tampung id_user
        $userIds = [];
        foreach ($data as $item) {
            $userIds[$item->id_user] = $item->id_user;
        }
        // get biodata by id_user
        $biodata = DB::table('core.biodata')->select('id', 'id_user')->whereIn('id_user', $userIds)->get();

        // STEP 2: add new column
        // tambahkan kolom id_biodata
        SevimaSchema::table('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->foreignIdTo(Biodata::class, 'id_biodata', nullable: true);
        });

        // STEP 3: update old data
        // update id_biodata
        foreach ($data as $item) {
            $biodataId = $biodata->where('id_user', $item->id_user)->first()?->id;
            DB::table('litabmas.pengajuan_pendanaan_anggota')->where('id_user', $item->id_user)->update(['id_biodata' => $biodataId]);
        }

        // STEP 4: after update
        // remove column id_user dari litabmas.pengajuan_pendanaan_anggota
        SevimaSchema::table('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->dropColumn('id_user');
        });
        // update column id_biodata menjadi not nullable
        DB::statement('ALTER TABLE litabmas.pengajuan_pendanaan_anggota ALTER COLUMN id_biodata SET NOT NULL');

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // reverse!
        DB::beginTransaction();

        // STEP 1: prepare
        // get current data anggota
        $data = DB::table('litabmas.pengajuan_pendanaan_anggota')->get();
        // tampung id_biodata
        $biodataIds = [];
        foreach ($data as $item) {
            $biodataIds[$item->id_biodata] = $item->id_biodata;
        }
        // get id_user by id_biodata
        $biodata = DB::table('core.biodata')->select('id', 'id_user')->whereIn('id', $biodataIds)->get();

        // STEP 2: add new column
        // tambahkan id_user
        SevimaSchema::table('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->foreignIdTo(User::class, 'id_user', nullable: true);
        });

        // STEP 3: update old data
        // update id_user
        foreach ($data as $item) {
            $userId = $biodata->where('id', $item->id_biodata)->first()?->id_user;
            DB::table('litabmas.pengajuan_pendanaan_anggota')->where('id_biodata', $item->id_biodata)->update(['id_user' => $userId]);
        }

        // STEP 4: after update
        // remove column id_biodata dari litabmas.pengajuan_pendanaan_anggota
        SevimaSchema::table('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->dropColumn('id_biodata');
        });
        // update column id_user menjadi not nullable
        DB::statement('ALTER TABLE litabmas.pengajuan_pendanaan_anggota ALTER COLUMN id_user SET NOT NULL');

        DB::commit();
    }
};
