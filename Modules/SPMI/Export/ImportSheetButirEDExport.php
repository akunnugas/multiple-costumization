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

class ImportSheetButirEDExport implements FromArray, WithTitle, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Panduan Pengisian *', // Validasi ('LED PS 9 Kriteria')
            'No. Indikator *', // Validasi (Wajib diisi)
            'Nama Indikator *',
            'Deskripsi',
            'Apakah Aktif ?', // Validasi (Checkbox)
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
            'LED PS 9 Kriteria',
            'a.1.sample',
            'Sample Indikator',
            '<p>Velit Lorem ad magna voluptate dolore elit consequat velit proident ex. Ipsum reprehenderit ullamco sunt cupidatat consectetur. Velit reprehenderit dolor cillum veniam enim. Sint ullamco consectetur reprehenderit non ea ex consequat duis sunt. Pariatur fugiat proident ex deserunt voluptate ad anim ut reprehenderit eu duis officia. Minim amet adipisicing voluptate tempor.</p>',
            'TRUE',
            '',
            '',
        ]];
    }

    public function registerEvents(): array
    {
        $panduan = PengisianPanduan::getListSelfEvaluation();
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
                $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(30);
                $sheet->getColumnDimension('G')->setWidth(30);

                $startRow = 2;
                $endRow   = 1000;

                for ($row = $startRow; $row <= $endRow; $row++) {
                    // Column E, F, G : Checkbox ("Apakah Komentar ?", "Apakah Key Point ?", "Apakah Aktif ?")
                    $validation = $sheet->getCell("E$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"TRUE,FALSE"');
                    // foreach (['E', 'F', 'G'] as $col) {
                    // }

                    // Column H : Periode (ambil dari sheet "Referensi" kolom A2:A100)
                    $validation = $sheet->getCell("F$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . implode(',', $auditPeriods) . '"');

                    // Column I : Unit (ambil dari sheet "Referensi" kolom C2:C200)
                    $validation = $sheet->getCell("G$row")->getDataValidation();
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
