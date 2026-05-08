<?php
namespace Modules\Core\Helpers;

use DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportFromArray implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected string $module;
    protected string $resource;
    
    public function __construct(
        protected array $data,
        protected array $header,
        protected string|null $connection = null,
    )
    {
        $info = Page::showURLInfo();

        $this->module = $info['module'];
        $this->resource = $info['resource'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
    
                $columnCount = count($this->header);
                $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
    
                $cellRange = "A1:{$highestColumn}1";
    
                $sheet->getStyle($cellRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D3D3D3');
    
                $sheet->getStyle($cellRange)->getFont()->setBold(true);
    
                $highestRow = $sheet->getHighestRow();
                $dataRange = "A1:{$highestColumn}{$highestRow}";
    
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
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

    public function array(): array
    {
        // auto assign data berdasarkan header
        $data = [];
        foreach ($this->header as $itemHeader) {
            $field = $itemHeader['field'];
            foreach ($this->data as $keyData => $valueData) {
                $data[$keyData][$field] = Cstr::unescapeDeep($valueData[$field]);
            }
        }

        return $data;
    }
}