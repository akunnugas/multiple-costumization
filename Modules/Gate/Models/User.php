<?php

namespace Modules\Gate\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\AuthModel;
use Modules\Core\Models\Biodata;

class User extends AuthModel
{
    use SoftDeletes;

    const RULES = [
        'nama_user' => ['required' => true, 'maxlength' => 255],
        'email_user' => ['type' => 'email', 'maxlength' => 255, 'unique' => true],
        'waktu_verifikasi_email' => ['type' => 'timestamp'],
        'telepon_user' => ['maxlength' => 20],
        'id_user_sso' => ['type' => 'integer'],
        'id_undangan_sso' => ['type' => 'integer'],
        'waktu_undangan_sso' => ['type' => 'timestamp'],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gate.user';

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_user';
    const OPTION_COLUMN = 'nama_user';

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
        'id_undangan_sso',
        'waktu_undangan_sso',
        'ref_key_siakad',
    ];

    public function biodata()
    {
        return $this->hasOne(Biodata::class);
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'gate.user_role', 'id_user', 'id_role');
    }
}
