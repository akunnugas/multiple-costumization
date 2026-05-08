<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\PenilaianPanduan;

class ExportPenilaianReference implements WithTitle, WithEvents
{

    protected $idPenilaianPanduan;
    protected $akreditasiStandart;

    public function __construct($idPenilaianPanduan)
    {
        $this->idPenilaianPanduan = $idPenilaianPanduan;
        $penilaianPanduan = PenilaianPanduan::find($idPenilaianPanduan);
        $this->akreditasiStandart = AkreditasiStandar::where('id_jenis_standar', $penilaianPanduan->id_jenis_standar)
            ->get();
    }

    public function title(): string
    {
        return 'Daftar Standar Akreditasi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Header
                $sheet->setCellValue('A1', 'Daftar Standar Akreditasi');
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getStyle("A1")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A1")->getAlignment()->setVertical('center');
                $sheet->getStyle("A1")->getFont()->setBold(true);
                // Data
                $row = 2;
                $standarOptions = $this->akreditasiStandart->map(function ($item) {
                    $item->nama_standar = str_replace(',', '', $item->nama_standar);
                    return (string) "{$item->kode_standar} - {$item->nama_standar}";
                })->toArray();
                foreach ($standarOptions as $index => $standar) {
                    $sheet->setCellValue('A' . $row, $standar);
                    $row++;
                }
            }
        ];
    }
}
