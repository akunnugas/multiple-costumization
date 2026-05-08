<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SheetPenilaianCore implements FromArray, WithTitle, WithHeadings, WithEvents
{
    public function title(): string
    {
        return 'Template Penilaian IKT';
    }

    public function headings(): array
    {
        return [
            'No Penilaian',
            'Indikator',
            'Butir',
            'Elemen',
            'Panduan',
            'Unit Kerja',
            'Periode',
            'Nilai',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        return [
            [],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:A2');
            },
        ];
    }
}
