<?php

namespace Modules\Core\Extensions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Modules\Core\Helpers\Page;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\UnitKerjaCache;
use Modules\Core\Models\Shared\RoleInternalCache;
use Modules\Gate\Models\RoleCache;
use Modules\Gate\Services\AuthorizationService;

class SiakadUserProvider implements UserProvider
{
    /**
     * Create a new siakad user provider.
     *
     * @param  string  $model
     * @return void
     */
    public function __construct(private $model)
    {
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // user
        $user = $this->model::find($identifier);
        if (empty($user)) {
            return null;
        }

        // biodata jika User nya instance of gate
        if ($user instanceof \Modules\Gate\Models\User) {
            $biodata = Biodata::where('id_user', $user->id)
                ->orderBy('id')
                ->first();
        }

        // role
        $roleId = $user->id_role_internal;
        if (!empty($roleId)) {
            $internalRoleCache = RoleInternalCache::find($roleId);
            $roleName = $internalRoleCache['nama_role'] ?? null;
            $roleCode = $internalRoleCache['kode_role'] ?? null;
        } else {
            $roleId = session()->get('user.id_role');
        }

        if (empty($roleName)) {
            $roleCache = RoleCache::find($roleId);
            $roleName = $roleCache['nama_role'] ?? null;
            $roleCode = $roleCache['kode_role'] ?? null;
        }

        // organization
        $organizationId = $user->id_role_internal ? 0 : session()->get('user.id_unit'); // 0: sevima
        if (!empty($organizationId)) {
            $organizationName = UnitKerjaCache::find($organizationId)['nama_unit'] ?? null;
        } else if (isset($organizationId)) {
            $organizationName = 'SEVIMA';
        }

        // modul, pass user agar tidak mengambil auth user
        $param = [
            'id' => $user->id,
            'nama_user' => $user->nama_user,
            'email_user' => $user->email_user,
            'id_role' => $roleId,
            'nama_role' => $roleName ?? null,
            'kode_role' => $roleCode ?? null,
            'id_unit' => $organizationId,
            'nama_unit' => $organizationName ?? null,
            'is_internal' => session()->get('user.is_internal') ?? false,
            'apakah_role_internal' => (bool)$user->id_role_internal,
            'biodata' => $biodata ?? null,
        ];

        if (!session()->has('user.modul')) {
            session()->put('user.modul', AuthorizationService::showAuthorizedModules(new SiakadUser($param)));
        }

        $modules = session()->get('user.modul');
        $module = $modules[Page::showURLInfo('module')] ?? null;

        return new SiakadUser($param + [
            'id_modul' => $module['id'] ?? null,
            'kode_modul' => $module['kode_modul'] ?? null,
            'nama_modul' => $module['nama_modul'] ?? null,
            'modul' => $modules,
        ]);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }
}
