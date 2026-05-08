<?php

namespace Modules\SPMI\Export;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportPenilaianSheet implements WithMultipleSheets
{
    protected $idPenilaianPanduan;
    protected $isDefault;

    public function __construct($idPenilaianPanduan, bool $isDefault = false)
    {
        $this->idPenilaianPanduan = $idPenilaianPanduan;
        $this->isDefault = $isDefault;
    }
    public function sheets(): array
    {
        $arr = [];
        $arr[] = new ExportPenilaianMatriks($this->idPenilaianPanduan, $this->isDefault);
        $arr[] = new ExportPenilaianReference($this->idPenilaianPanduan);
        return $arr;
    }
}
