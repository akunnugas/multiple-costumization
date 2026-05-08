<?php

namespace Modules\Kerjasama\Helpers;

use DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Core\Helpers\Page;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportFormat implements FromArray, WithHeadings, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    use Exportable;
    protected string $module;
    protected string $resource;

    protected array $dropdowns = [];

    public function __construct(
        protected array $data,
        protected array $header,
        protected string|null $connection = null,
        array $dropdowns = []
    ) {
        $info = Page::showURLInfo();

        $this->module = $info['module'];
        $this->resource = $info['resource'];
        $this->dropdowns = $dropdowns;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $columnCount = count($this->header);
                $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
                $cellRange = "A1:{$highestColumn}1";
                
                // Style  header row
                $sheet->getStyle(cellCoordinate: $cellRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D3D3D3');
                $sheet->getStyle($cellRange)->getFont()->setBold(true);
                
                $dataRows = max(1000, count($this->data) + 50);

                // Apply date formating
                foreach ($this->header as $index => $field) {
                                        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);

                    if (isset($field['format']) && $field['format'] === 'dd/mm/yyyy') {
                        $dateRange = $column . '2:' . $column . $dataRows;
                        $sheet->getStyle($dateRange)->getNumberFormat()->setFormatCode('[$-421]dd/mm/yyyy');
                        $validation = $sheet->getCell($column . '2')->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DATE);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setPromptTitle('Input Tanggal');
                        $validation->setPrompt('Format: DD/MM/YYYY');

                        $sheet->setDataValidation($dateRange, $validation);
                    }
                    elseif (isset($field['format']) && $field['format'] === 'phone') {
                        $phoneRange = $column . '2:' . $column . $dataRows;
                        $sheet->getStyle($phoneRange)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    }
                }

                $optionsColumn = 'CA';
                $startRow = 300;

                foreach ($this->dropdowns as $column => $config) {
                    $i = 0;
                    foreach ($config['options'] as $key => $value) {
                        $sheet->setCellValue($optionsColumn . ($startRow + $i), $value);
                        $i++;
                    }
                    $listRange = '\'' . $sheet->getTitle() . '\'!$' . $optionsColumn . '$' . $startRow . ':$' . $optionsColumn . '$' . ($startRow + count($config['options']) - 1);
                    
                    $this->setDropDown(
                        $column,
                        $sheet,
                        2,
                        $dataRows,
                        $listRange
                    );
                    $startRow += count($config['options']) + 1;
                }
                $sheet->getColumnDimension($optionsColumn)->setVisible(false);
            },
        ];
    }

    public function headings(): array
    {
        $headerLabel = [];
        $info = Page::showURLInfo();
        foreach ($this->header as $item) {
            $headerLabel[] = Page::translateResource(
                $info['resource'],
                field: $item['field'],
                module: $info['module']
            );
        }

        return $headerLabel;
    }

    private function setDropDown(
        string $column,
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $startRow,
        int $endRow,
        string $listRange
    ): void {
        $cellRange = $column . $startRow . ':' . $column . $endRow;
        $validation = $sheet->getCell($column . $startRow)->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setPromptTitle('Pilih Input');
        $validation->setPrompt('Pilih Data');
        $validation->setFormula1($listRange);
        $sheet->setDataValidation($cellRange, $validation);
    }

    public function array(): array
    {
        return [];
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->header as $index => $field) {
            if (isset($field['format'])) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                if ($field['format'] === 'dd/mm/yyyy') {
                    $formats[$column] = '[$-421]dd/mm/yyyy';
                } elseif ($field['format'] === 'phone') {
                    $formats[$column] = NumberFormat::FORMAT_TEXT;
                }
            }
        }

        return $formats;
    }
}