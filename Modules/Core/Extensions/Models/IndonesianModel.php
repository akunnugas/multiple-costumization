<?php

namespace Modules\Core\Extensions\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\Traits\SevimaModel;

class IndonesianModel extends Model
{
    use SevimaModel;

    const CREATED_AT = 'waktu_dibuat';
    const UPDATED_AT = 'waktu_diubah';
    const DELETED_AT = 'waktu_dihapus';
}
