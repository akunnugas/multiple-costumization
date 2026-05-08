<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportPenilaianMatriks implements FromArray, WithEvents, WithTitle, WithHeadings
{
    protected $idPenilaianPanduan;
    protected $isDefault;
    protected $hasParentColumn;
    protected $akreditasiStandart;
    protected $skorMatriks;

    public function __construct($idPenilaianPanduan, bool $isDefault = false)
    {
        $this->idPenilaianPanduan = $idPenilaianPanduan;
        $this->isDefault = $isDefault;
        $penilaianPanduan = PenilaianPanduan::find($idPenilaianPanduan);

        $this->hasParentColumn = !$isDefault;

        $this->akreditasiStandart = AkreditasiStandar::where('id_jenis_standar', $penilaianPanduan->id_jenis_standar)
            ->get();
        $this->skorMatriks = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $idPenilaianPanduan)
            ->orderBy('nilai', 'asc')
            ->get();
    }

    public function title(): string
    {
        return 'Matriks Penilaian';
    }

    public function headings(): array
    {
        $headings = ['No Penilaian *'];

        if ($this->hasParentColumn) {
            $headings[] = 'Parent Indikator';
        }

        $headings[] = 'Pertanyaan Penilaian *';
        $headings[] = 'Standar Akreditasi *';
        $headings[] = 'Status Penilaian (Aktif/Tidak Aktif) *';
        $headings[] = 'Bobot Penilaian';

        if (!$this->isDefault) {
            $headings[] = 'Butir Indikator SPME (Ya/Tidak)';
            $headings[] = 'Hitung dalam Peringkat SPME / IKU (Ya/Tidak)';
        }

        $headings[] = 'Tampilkan Hasil Akhir (Ya/Tidak)';

        foreach ($this->skorMatriks as $skor) {
            $headings[] = "Skor Penilaian {$skor->nilai} ({$skor->deskripsi})";
        }

        return $headings;
    }

    public function array(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headings = $this->headings();
                $lastColIndex = count($headings);
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

                // Styling header
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
                $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Build column index map
                $colIdx = 1; // A
                $colIdx++; // skip No Penilaian (A)

                if ($this->hasParentColumn) {
                    $colIdx++; // skip Parent Indikator
                }

                $colIdx++; // skip Pertanyaan Penilaian
                $standarCol = Coordinate::stringFromColumnIndex($colIdx); $colIdx++;
                $statusCol = Coordinate::stringFromColumnIndex($colIdx); $colIdx++;
                $colIdx++; // skip Bobot Penilaian

                $spmeCol = $ikuCol = null;
                if (!$this->isDefault) {
                    $spmeCol = Coordinate::stringFromColumnIndex($colIdx); $colIdx++;
                    $ikuCol = Coordinate::stringFromColumnIndex($colIdx); $colIdx++;
                }

                $tampilkanCol = Coordinate::stringFromColumnIndex($colIdx); $colIdx++;

                $startRow = 2;
                $endRow = 1000;

                // Standar Akreditasi dropdown (reference the hidden sheet to avoid 255-char limit)
                $standarCount = $this->akreditasiStandart->count();
                $lastRefRow = $standarCount + 1; // row 1 is header in reference sheet
                $refFormula = "'Daftar Standar Akreditasi'!\$A\$2:\$A\${$lastRefRow}";
                $validation = $sheet->getCell("{$standarCol}{$startRow}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setShowDropDown(true);
                $validation->setFormula1($refFormula);
                for ($row = $startRow; $row <= $endRow; $row++) {
                    $sheet->getCell("{$standarCol}{$row}")->setDataValidation(clone $validation);
                }

                // Status Aktif dropdown
                $validation = $sheet->getCell("{$statusCol}{$startRow}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"Aktif,Tidak Aktif"');
                for ($row = $startRow; $row <= $endRow; $row++) {
                    $sheet->getCell("{$statusCol}{$row}")->setDataValidation(clone $validation);
                }

                // Ya/Tidak dropdowns
                $yaColumns = [$tampilkanCol];
                if ($spmeCol) $yaColumns[] = $spmeCol;
                if ($ikuCol) $yaColumns[] = $ikuCol;

                foreach ($yaColumns as $yaCol) {
                    $validation = $sheet->getCell("{$yaCol}{$startRow}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"Ya,Tidak"');
                    for ($row = $startRow; $row <= $endRow; $row++) {
                        $sheet->getCell("{$yaCol}{$row}")->setDataValidation(clone $validation);
                    }
                }
            },
        ];
    }
}
