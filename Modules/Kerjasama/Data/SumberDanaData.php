<?php

namespace Modules\Kerjasama\Data;

use DB;
use Modules\Kerjasama\Models\KriteriaMitra;
use Modules\Kerjasama\Models\SumberDana;

class SumberDanaData
{

    /**
     * Migrate data sumber dana default dengan metode updateOrCreate
     *
     * @return void
     */
    public function migrate()
    {
            foreach ($this->getData() as $sumberDana) {
                DB::beginTransaction();
                try {
                    $exist = DB::table('kerjasama.sumber_dana')
                            ->where('sumber_dana', $sumberDana['sumber_dana'])
                            ->first();

                    if (!$exist) {
                        SumberDana::updateOrCreate([
                            ...$sumberDana,
                            'isian_default' => true
                        ]);
                    }
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                }
            }
    }

    /**
     * Rollback data dengan cara hapus semua data sumber dana
     * jika isian_default => true
     *
     * @return void
     */
    public function rollback()
    {
        DB::beginTransaction();
        SumberDana::where('isian_default', true)->delete();
        DB::commit();
    }

    protected function getData(): array
    {
        return [
            ['sumber_dana' => 'Direktorat PSD'],
            ['sumber_dana' => 'Direktorat PSMP'],
            ['sumber_dana' => 'Direktorat PSMA'],
            ['sumber_dana' => 'Direktorat PSMK'],
            ['sumber_dana' => 'Direktorat PKLK Dikdas'],
            ['sumber_dana' => 'Direktorat PKLK Dikmen'],
            ['sumber_dana' => 'Direktorat P2TK Dikdas'],
            ['sumber_dana' => 'Direktorat P2TK Dikmen'],
            ['sumber_dana' => 'Sekretariat Dikdas'],
            ['sumber_dana' => 'Sekretariat Dikmen'],
            ['sumber_dana' => 'Biro PKLN'],
            ['sumber_dana' => 'Pustekkom'],
            ['sumber_dana' => 'Puskurbuk'],
            ['sumber_dana' => 'Puspendik'],
            ['sumber_dana' => 'Balitbang'],
            ['sumber_dana' => 'Badan PSDMPK dan PMP'],
            ['sumber_dana' => 'Dikti'],
            ['sumber_dana' => 'Dinas Propinsi'],
            ['sumber_dana' => 'Dinas Kabupaten'],
            ['sumber_dana' => 'DIPA PTN'],
            ['sumber_dana' => 'DP2M Ristekdikti'],
            ['sumber_dana' => 'Insinas Ristekdikti'],
            ['sumber_dana' => 'Lembaga donor dalam negeri'],
            ['sumber_dana' => 'Lembaga donor luar negeri'],
            ['sumber_dana' => 'Dana mandiri'],
            ['sumber_dana' => 'Bantuan Swasta'],
            ['sumber_dana' => 'Bantuan Asing'],
        ];
    }
}
