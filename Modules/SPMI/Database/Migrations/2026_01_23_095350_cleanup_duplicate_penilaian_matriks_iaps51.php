<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianMatriksReferensi;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Get the penilaian panduan for IAPS5.1-S1-Akre
            $penilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS5.1-S1-Akre')->first();

            if (!$penilaianPanduan) {
                info("Penilaian panduan IAPS5.1-S1-Akre not found");
                return;
            }

            // Find duplicate records in penilaian_matriks
            $duplicates = DB::select("
                SELECT
                    id_penilaian_panduan,
                    nomor_penilaian,
                    MIN(id) as keep_id,
                    array_agg(id ORDER BY id) as all_ids,
                    COUNT(*) as duplicate_count
                FROM spmi.penilaian_matriks
                WHERE id_penilaian_panduan = ?
                    AND apakah_data_default = true
                    AND waktu_dihapus IS NULL
                GROUP BY id_penilaian_panduan, nomor_penilaian
                HAVING COUNT(*) > 1
            ", [$penilaianPanduan->id]);

            if (empty($duplicates)) {
                info("No duplicates found for IAPS5.1-S1-Akre");
                return;
            }

            info("Found " . count($duplicates) . " sets of duplicate records");

            foreach ($duplicates as $duplicate) {
                $allIds = explode(',', trim($duplicate->all_ids, '{}'));
                $keepId = $duplicate->keep_id;
                $deleteIds = array_filter($allIds, fn($id) => $id != $keepId);

                if (!empty($deleteIds)) {
                    // Delete related penilaian_matriks_predikat records
                    PenilaianMatriksPredikat::whereIn('id_penilaian_matriks', $deleteIds)->delete();

                    // Delete related penilaian_matriks_referensi records
                    PenilaianMatriksReferensi::whereIn('id_penilaian_matriks', $deleteIds)->delete();

                    // Delete duplicate penilaian_matriks records
                    PenilaianMatriks::whereIn('id', $deleteIds)->delete();

                    info("Deleted duplicates for nomor_penilaian: {$duplicate->nomor_penilaian}, kept ID: {$keepId}, deleted IDs: " . implode(', ', $deleteIds));
                }
            }

            // Resync tree structure after cleanup
            PenilaianMatriks::resyncTreeStructure($penilaianPanduan->id);

            info("Successfully cleaned up duplicate records for IAPS5.1-S1-Akre");
        } catch (\Exception $e) {
            info("Migration error: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse deletion of duplicates
    }
};
