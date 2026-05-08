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
        DB::table('kerjasama.status_kerjasama')
            ->where('status_kerjasama', 'Kadaluwarsa')
            ->update(['status_kerjasama' => 'Kedaluwarsa']);
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('kerjasama.status_kerjasama')
            ->where('status_kerjasama', 'Kedaluwarsa')
            ->update(['status_kerjasama' => 'Kadaluwarsa']);
    }
};
