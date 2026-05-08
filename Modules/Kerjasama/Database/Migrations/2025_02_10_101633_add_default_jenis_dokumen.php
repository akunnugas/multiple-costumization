<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            [
                'jenis_dokumen' => 'Memorandum of Understanding (MoU)',
                'isian_default' => true
            ],
            [
                'jenis_dokumen' => 'Memorandum of Agreement (MoA)',
                'isian_default' => true
            ],
            [
                'jenis_dokumen' => 'Implementation of Arrangement (IA)',
                'isian_default' => true
            ],
        ];

        foreach ($data as $item) {
            DB::table('kerjasama.jenis_dokumen')
                ->insert($item);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('kerjasama.jenis_dokumen')
            ->where('isian_default', true)
            ->delete();
    }
};
