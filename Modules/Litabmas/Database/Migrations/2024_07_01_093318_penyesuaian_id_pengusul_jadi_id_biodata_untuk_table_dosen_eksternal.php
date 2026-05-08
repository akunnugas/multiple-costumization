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

        // get current data dosen_eksternal
        $data = DB::table('litabmas.dosen_eksternal')->get();
        // tampung id_pengusul
        $pengusulUserIds = [];
        foreach ($data as $item) {
            $pengusulUserIds[$item->id_pengusul] = $item->id_pengusul;
        }
        // get biodata by id_pengusul
        $biodata = DB::table('core.biodata')
            ->select('id', 'id_user')
            ->whereIn('id_user', $pengusulUserIds)
            ->get();

        // tambahkan kolom baru id_biodata_pengusul
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->foreignIdTo(Biodata::class, 'id_biodata_pengusul', nullable: true);
        });

        // update id_biodata_pengusul
        foreach ($data as $item) {
            $biodataId = $biodata->where('id_user', $item->id_pengusul)->first()?->id;
            DB::table('litabmas.dosen_eksternal')
                ->where('id_pengusul', $item->id_pengusul)
                ->update(['id_biodata_pengusul' => $biodataId]);
        }

        // remove column id_pengusul dari litabmas.dosen_eksternal
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->dropColumn('id_pengusul');
        });

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
        // get current data dosen_eksternal
        $data = DB::table('litabmas.dosen_eksternal')->get();
        // tampung id_biodata_pengusul
        $biodataIds = [];
        foreach ($data as $item) {
            $biodataIds[$item->id_biodata_pengusul] = $item->id_biodata_pengusul;
        }
        // get id+user by id_biodata_pengusul
        $biodata = DB::table('core.biodata')
            ->select('id', 'id_user')
            ->whereIn('id', $biodataIds)
            ->get();

        // STEP 2: add new column
        // tambahkan id_pengusul
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->foreignIdTo(User::class, 'id_pengusul', nullable: true);
        });

        // STEP 3: update old data
        // update id_pengusul
        foreach ($data as $item) {
            $userId = $biodata->where('id', $item->id_biodata_pengusul)->first()?->id_user;
            DB::table('litabmas.dosen_eksternal')
                ->where('id_biodata_pengusul', $item->id_biodata_pengusul)
                ->update(['id_pengusul' => $userId]);
        }

        // STEP 4: after update
        // remove column id_biodata_pengusul dari litabmas.dosen_eksternal
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->dropColumn('id_biodata_pengusul');
        });

        DB::commit();
    }
};
