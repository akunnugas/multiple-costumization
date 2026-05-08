<?php

namespace Modules\Kerjasama\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateStatusKerjasamaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kerjasama:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status kerjasama berdasarkan tanggal mulai dan akhir berlaku';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $now = Carbon::now()->format('Y-m-d');

            // Get status Kedaluwarsa ID
            $statusKedaluwarsa = DB::table('kerjasama.status_kerjasama')
                ->where('status_kerjasama', 'Kedaluwarsa')
                ->where('waktu_dihapus', null)
                ->value('id');

            // status aktif
            $statusAktif = DB::table('kerjasama.status_kerjasama')
                ->where('status_kerjasama', 'Aktif')
                ->where('waktu_dihapus', null)  
                ->value('id');


            if (!$statusKedaluwarsa) {
                $this->error('Status Kedaluwarsa tidak ditemukan di database');
                Log::channel('scheduler')->error('Kerjasama - Status Kedaluwarsa tidak ditemukan');
                return 1;
            }

            if (!$statusAktif) {
                $this->error('Status Aktif tidak ditemukan di database');
                Log::channel('scheduler')->error('Kerjasama - Status Aktif tidak ditemukan');
                return 1;
            }

            // Update status
            $expiredCount = DB::table('kerjasama.kerjasama')
                ->whereNotNull('tanggal_akhir_berlaku')
                ->where('id_status_kerjasama', '=', $statusAktif)
                ->where('tanggal_akhir_berlaku', '<', $now)
                ->whereNull('waktu_dihapus')
                ->update([
                    'id_status_kerjasama' => $statusKedaluwarsa,
                    'waktu_diubah' => Carbon::now(),
                ]);

            Log::channel('scheduler')->info("Kerjasama - Status updated: {$expiredCount} expired");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::channel('scheduler')->error('Kerjasama - Update status error: ' . $e->getMessage());
            return 1;
        }
    }
}
