<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ImportSheetButirLKExport implements FromArray, WithTitle, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Panduan Pengisian *', // Validasi ('IAPS Versi 4.0', 'IAPS 5.0')
            'No. Indikator *', // Validasi (Wajib diisi)
            'Nama Indikator *',
            'Deskripsi',
            'Informasi',
            'Aktif', // Validasi (Checkbox)
            'Periode', // Validasi (Ambil dari sheet Referensi)
            'Unit', // Validasi (Ambil dari sheet Referensi)
        ];
    }

    public function title(): string
    {
        return 'Tabel Butir';
    }

    public function array(): array
    {
        return [[
            'IAPS Versi 4.0', // Panduan Pengisian
            'LK-1.1',         // No. Indikator
            'Ketersediaan Dokumen', // Nama Indikator
            'Deskripsi indikator contoh panjang...', // Deskripsi
            'Informasi tambahan contoh',             // Informasi
            'TRUE',           // Aktif
            '', // Periode (isi via dropdown)
            '', // Unit (isi via dropdown)
        ]];
    }

    public function registerEvents(): array
    {
        $panduan = PengisianPanduan::getListIndicatorPerformanceReport();
        $panduan = implode(',', array_values($panduan));

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
        $final_units = implode(',', array_column($final_units, 'nama_unit'));

        return [
            AfterSheet::class => function (AfterSheet $event) use ($auditPeriods, $panduan, $final_units) {
                $sheet = $event->sheet->getDelegate();

                // Styling header
                $lastCol = 'J'; // karena 20 kolom (A..J)
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
                $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Atur lebar kolom secukupnya
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $startRow = 2;
                $endRow   = 1000;

                for ($row = $startRow; $row <= $endRow; $row++) {
                    // Column F (Apakah Row sampai Aktif) → Checkbox TRUE/FALSE
                    $validation = $sheet->getCell("F$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"TRUE,FALSE"');

                    // Column I : Periode (ambil dari sheet Referensi kolom A)
                    $validation = $sheet->getCell("G$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . implode(',', $auditPeriods) . '"');

                    // Column J : Unit (ambil dari sheet Referensi kolom D)
                    $validation = $sheet->getCell("H$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . $final_units . '"');

                    // Column A : Panduan Pengisian (ambil dari sheet Referensi kolom F)
                    $validation = $sheet->getCell("A$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . $panduan . '"');
                }
            }
        ];
    }
}
