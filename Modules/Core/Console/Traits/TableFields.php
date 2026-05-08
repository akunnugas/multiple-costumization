<?php

namespace Modules\Core\Console\Traits;

use Modules\Core\Extensions\SevimaSchema;

trait TableFields
{
    /**
     * Table fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Get table fields.
     * @return array
     */
    protected function initTableFields()
    {
        $skip = [
            'id',
            'created_at', 'created_by', 'updated_at', 'updated_by', 'waktu_dihapus', 'deleted_by', // log
            'depth', 'info_left', 'info_right', // tree structure
        ];

        foreach (SevimaSchema::getConnection()->getDoctrineSchemaManager()->listTableColumns($this->argument('table')) as $field) {
            // ambil field
            $col = $field->getName();

            // cek skip
            if (in_array($col, $skip)) {
                continue;
            }

            $this->fields[$col] = $field;
        }
    }
}
