<?php

namespace Modules\Core\Extensions\Models;

use Illuminate\Foundation\Auth\User;
use Modules\Core\Extensions\Models\Traits\SevimaModel;

class AuthModel extends User
{
    use SevimaModel;

    const CREATED_AT = 'waktu_dibuat';
    const UPDATED_AT = 'waktu_diubah';
    const DELETED_AT = 'waktu_dihapus';
}
