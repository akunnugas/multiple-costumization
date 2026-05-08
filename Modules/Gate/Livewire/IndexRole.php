<?php

namespace Modules\Gate\Livewire;

use Modules\Core\Livewire\ReferenceComponent;
use Modules\Gate\Models\Role;
use Modules\Gate\Services\RoleManagementService;

class IndexRole extends ReferenceComponent
{
    use GateViewData;

    protected $model = Role::class;

    protected function loadService()
    {
        $this->service = new RoleManagementService();
    }

    protected function defineHeader()
    {
        return [
            ['field' => 'code'],
            ['field' => 'name'],
            ['field' => 'is_static', 'component' => true, 'readonly' => true, 'sortable' => false, 'searchable' => false],
        ];
    }
}
