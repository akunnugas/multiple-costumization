<?php

namespace Modules\Core\Models\Shared;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\ClearCache;

class RoleInternal extends IndonesianModel
{
    use ClearCache, SoftDeletes;

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
    protected $table = 'role_internal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_role',
        'kode_role',
        'level_cp',
        'ref_key_siakad'
    ];

    const RULES = [
        'nama_role' => ['required' => true, 'maxlength' => 255, 'unique' => true],
        'kode_role' => ['required' => true, 'maxlength' => 100, 'unique' => true],
    ];

    const ROLE_INTERNAL = [
        self::CODE_SUPERADMIN => 'sa',
        self::CODE_SUPPORT => 'admin_support_sevima',
        self::CODE_ADFIX => 'admin_perbaikan_data',
    ];

    const CODE_SUPERADMIN = 'admin';
    const CODE_SUPPORT = 'assvm';
    const CODE_ADFIX = 'adfix';

    const CODE_V2_SUPERADMIN = 'sa';

    public static function internallRolesV1()
    {
        return ['admin', 'assvm', 'adfix'];
    }

    public static function getRoles()
    {
        return array_values(self::ROLE_INTERNAL);
    }

    public static function mapRoleInternalV1($role = null)
    {
        // [old] => [new]
        $map = self::ROLE_INTERNAL;

        return empty($role) ? $map : ($map[$role] ?? null);
    }

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        RoleInternalCache::destroy($model->id);
    }
}
