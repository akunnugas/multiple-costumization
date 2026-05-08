<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportPenilaianMatriksError implements WithEvents
{
    protected $uploadedFilePath;
    protected $errorData;
    protected $isDataDefault;
    protected $hasParentColumn;

    public function __construct($uploadedFilePath, array $errorData, bool $isDataDefault, bool $hasParentColumn = false)
    {
        $this->uploadedFilePath = $uploadedFilePath;
        $this->errorData = $errorData;
        $this->isDataDefault = $isDataDefault;
        $this->hasParentColumn = $hasParentColumn;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Load file yang diupload user
                $spreadsheet = IOFactory::load($this->uploadedFilePath);
                $uploadedSheet = $spreadsheet->getActiveSheet();

                // Tentukan kolom terakhir dengan data di header untuk menentukan posisi kolom Status dan Error
                $highestRow = $uploadedSheet->getHighestRow();
                $highestColumn = $uploadedSheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                // Cari kolom terakhir yang memiliki header (untuk menghindari kolom kosong di template dinamis)
                $realLastColumnIndex = 0;
                for ($i = 1; $i <= $highestColumnIndex; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $val = $uploadedSheet->getCell($col . '1')->getValue();
                    if (trim((string)$val) !== '') {
                        $realLastColumnIndex = $i;
                    }
                }

                $lastDataColumn = Coordinate::stringFromColumnIndex($realLastColumnIndex);
                $statusColumn = Coordinate::stringFromColumnIndex($realLastColumnIndex + 1);
                $errorColumn = Coordinate::stringFromColumnIndex($realLastColumnIndex + 2);

                // copy header
                foreach (range('A', $lastDataColumn) as $col) {
                    $cellValue = $uploadedSheet->getCell($col . '1')->getValue();
                    $sheet->setCellValue($col . '1', $cellValue);
                    $sheet->getStyle($col . '1')->applyFromArray(
                        $uploadedSheet->getStyle($col . '1')->exportArray()
                    );
                }

                // Tambahkan header untuk kolom Status dan Error
                $sheet->setCellValue("{$statusColumn}1", 'Status Validasi');
                $sheet->getStyle("{$statusColumn}1")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("{$statusColumn}1")->getAlignment()->setVertical('center');
                $sheet->getStyle("{$statusColumn}1")->getFont()->setBold(true);

                $sheet->setCellValue("{$errorColumn}1", 'Pesan Error');
                $sheet->getStyle("{$errorColumn}1")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("{$errorColumn}1")->getAlignment()->setVertical('center');
                $sheet->getStyle("{$errorColumn}1")->getFont()->setBold(true);

                // Styling header kolom baru
                $sheet->getStyle("{$statusColumn}1:{$errorColumn}1")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                        'bold' => true,
                    ],
                ]);

                // Copy data rows (row 2+ since dynamic template has single header)
                $dataRowIndex = 0;
                for ($row = 2; $row <= $highestRow; $row++) {
                    // Copy data asli
                    $empty = $jumlahKolom = 0;
                    foreach (range('A', $lastDataColumn) as $col) {
                        $cellValue = $uploadedSheet->getCell($col . $row)->getValue();
                        $sheet->setCellValue($col . $row, $cellValue);

                        if (empty($cellValue)) {
                            $empty++;
                        }
                        $jumlahKolom++;
                    }
                    if($empty == $jumlahKolom){
                        continue;
                    }

                    $status = 'Valid';
                    $pesanError = '';

                    // Cek status dan error dari errorData
                    if (isset($this->errorData[$dataRowIndex])) {
                        $errorRow = $this->errorData[$dataRowIndex];

                        // Buat pesan error
                        $messages = [];
                        foreach (range('A', $lastDataColumn) as $index => $col) {
                            $colName = $uploadedSheet->getCell($col . '1')->getValue();
                            if(isset($errorRow[$index])){
                                $status = 'Invalid';
                                array_push($messages, ...array_map(function ($item) use ($colName) {
                                    return "{$colName}: {$item}";
                                }, $errorRow[$index]));
                            }
                        }
                        $pesanError = implode(",\n", $messages);
                    }

                    $sheet->setCellValue("{$statusColumn}{$row}", $status);
                    $sheet->getStyle("{$errorColumn}{$row}")->getAlignment()->setWrapText(true);
                    $sheet->setCellValue("{$errorColumn}{$row}", $pesanError);

                    // Apply styling berdasarkan status
                    if ($status === 'Invalid') {
                        $sheet->getStyle("A{$row}:{$errorColumn}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFE6E6'],
                            ],
                        ])->getAlignment()->setVertical('top');

                        $sheet->getStyle("{$statusColumn}{$row}")->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => 'C00000'],
                                'bold' => true,
                            ],
                        ]);
                    } else {
                        $sheet->getStyle("{$statusColumn}{$row}")->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => '00B050'],
                                'bold' => true,
                            ],
                        ]);
                    }

                    $dataRowIndex++;
                }

                // Auto size columns
                foreach (range('A', $errorColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Set width khusus untuk kolom error
                $sheet->getColumnDimension($errorColumn)->setWidth(60);
            },
        ];
    }
}
