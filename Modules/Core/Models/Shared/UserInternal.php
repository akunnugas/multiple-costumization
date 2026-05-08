<?php

namespace Modules\Core\Models\Shared;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\AuthModel;

class UserInternal extends AuthModel
{
    use SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'shared';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_internal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_user',
        'email_user',
        'waktu_verifikasi_email',
        'telepon_user',
        'id_user_sso',
        'id_role_internal',
    ];

    /**
     * Get the role of the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleInternal::class);
    }
}
