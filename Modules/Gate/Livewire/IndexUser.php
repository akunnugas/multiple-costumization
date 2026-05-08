<?php

namespace Modules\Gate\Livewire;

use Modules\Core\Livewire\ListComponent;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Services\UserManagementService;

class IndexUser extends ListComponent
{
    use GateViewData;

    protected $model = User::class;

    protected function loadService()
    {
        $this->service = new UserManagementService();
    }

    protected function defineHeader()
    {
        return [
            ['field' => 'nama_user'],
            ['field' => 'email_user'],
            ['field' => 'id_role', 'sortable' => false, 'searchable' => false],
            ['field' => 'email_terverifikasi', 'component' => true, 'searchable' => false],
        ];
    }

    protected function defineFilter()
    {
        return [
            'roles' => ['options' => Role::class],
        ];
    }
}
