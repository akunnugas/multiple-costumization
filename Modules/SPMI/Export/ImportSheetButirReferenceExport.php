<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;

class ImportSheetButirReferenceExport implements FromArray, WithTitle, WithHeadings
{
    private $type;
    public function __construct(string $type = 'lk')
    {
        $this->type = $type;
    }

    public function title(): string
    {
        return 'Referensi';
    }

    public function headings(): array
    {
        return [
            'Periode',
            '',
            'Kode Unit',
            'Nama Unit',
            '',
            'Panduan Pengisian',
        ];
    }

    public function array(): array
    {
        $auditPeriods = AuditPeriode::select('tahun_audit')
            ->orderBy('tahun_audit', 'asc')
            ->get()
            ->pluck('tahun_audit')
            ->toArray();
            
        $units = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);
        $units = UnitKerja::select('kode_unit', 'nama_unit', 'id_jenjang_pendidikan')
            ->whereIn('id', array_keys($units))
            ->orderBy('kode_unit', 'asc')
            ->get();

        $unit_jenjang_pendidikan_ids = $units->pluck('id_jenjang_pendidikan')->unique()->toArray();
        $jenjang_pendidikan = JenjangPendidikan::select('id', 'kode_jenjang')
            ->whereIn('id', $unit_jenjang_pendidikan_ids)
            ->pluck('kode_jenjang', 'id')
            ->toArray();

        $final_units = $units->map(function ($unit) use ($jenjang_pendidikan) {
            $name = $unit->nama_unit;
            if ($unit->id_jenjang_pendidikan && isset($jenjang_pendidikan[$unit->id_jenjang_pendidikan])) {
                $name = $jenjang_pendidikan[$unit->id_jenjang_pendidikan] . ' - ' . $name;
            }

            return [
                'kode_unit' => $unit->kode_unit,
                'nama_unit' => $name,
            ];
        })->toArray();

        $panduan = [];
        if ($this->type === 'lk') {
            $panduan = PengisianPanduan::getListIndicatorPerformanceReport();
        }
        if ($this->type === 'ed') {
            $panduan = PengisianPanduan::getListSelfEvaluation();
        }
        $panduan = array_values($panduan);

        // Tentukan max baris
        $maxRows = max(count($auditPeriods), count($final_units), count($panduan));

        $rows = [];
        for ($i = 0; $i < $maxRows; $i++) {
            $rows[] = [
                $auditPeriods[$i] ?? '',           // Periode
                '',                                // Separator
                $final_units[$i]['kode_unit'] ?? '',
                $final_units[$i]['nama_unit'] ?? '',
                '',
                $panduan[$i] ?? '',
            ];
        }

        return $rows;
    }
}
