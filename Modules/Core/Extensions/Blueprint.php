<?php

namespace Modules\Core\Extensions;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{
    /**
     * Create log columns on the table.
     *
     * @param  bool  $isSoftDelete
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function logs($isSoftDelete = true)
    {
        // timestamp
        $this->timestampsTz();
        if ($isSoftDelete) {
            $this->softDeletesTz();
        }

        // user log
        $this->unsignedBigInteger('created_by')->nullable();
        $this->unsignedBigInteger('updated_by')->nullable();
        if ($isSoftDelete) {
            $this->unsignedBigInteger('deleted_by')->nullable();
        }
    }
}
