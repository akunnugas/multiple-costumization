<?php

namespace Modules\SPMI\Helpers;

class TSAudit
{
    public $TS0;
    public $TS1;
    public $TS2;
    public $TS3;
    public $TS4;
    public $TS5;
    public $TS6;

    public function __construct($tahunAudit)
    {
        $this->TS0 = ($tahunAudit - 1) . '/' . $tahunAudit;
        $this->TS1 = ($tahunAudit - 2) . '/' . ($tahunAudit - 1);
        $this->TS2 = ($tahunAudit - 3) . '/' . ($tahunAudit - 2);
        $this->TS3 = ($tahunAudit - 4) . '/' . ($tahunAudit - 3);
        $this->TS4 = ($tahunAudit - 5) . '/' . ($tahunAudit - 4);
        $this->TS5 = ($tahunAudit - 6) . '/' . ($tahunAudit - 5);
        $this->TS6 = ($tahunAudit - 7) . '/' . ($tahunAudit - 6);
    }
}
