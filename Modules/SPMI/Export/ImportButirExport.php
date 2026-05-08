<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportButirExport implements WithMultipleSheets
{
    protected static $allowedJenisIndikator = ['lk', 'ed'];
    protected static string $jenisIndikator;

    public function __construct(string $jenis_indikator)
    {
        if (!in_array($jenis_indikator, self::$allowedJenisIndikator)) {
            throw new \Exception("Jenis indikator $jenis_indikator tidak valid");
        }

        self::$jenisIndikator = $jenis_indikator;
    }
    public function sheets(): array
    {
        $arr = [];
        if (self::$jenisIndikator === 'ed') {
            $arr[] = new ImportSheetButirEDExport();
        } else {
            $arr[] = new ImportSheetButirLKExport();
        }

        $arr[] = new ImportSheetButirReferenceExport(self::$jenisIndikator);
        return $arr;
    }
}