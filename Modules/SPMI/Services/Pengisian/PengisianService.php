<?php

namespace Modules\SPMI\Services\Pengisian;

use Modules\SPMI\Helpers\TSAudit;

use Modules\Core\Services\Service;

class PengisianService extends Service
{
    protected $tsAudit;

    protected $isHasNumber = true;

    protected $isHasAction = true;

    protected $updateIndicator = [];

    public function __construct($tahunAudit)
    {
        $this->tsAudit = new TSAudit($tahunAudit);
    }

    /**
     * Build Table
     * @param $table
     * @param $row
     * @param $footer
     * @param $data
     * return array
     */
    public function buildTable($table, $row, $footer, $data)
    {
        return [$table, $row, $footer, $data, $this->isHasNumber, $this->isHasAction, $this->updateIndicator];
    }
}
