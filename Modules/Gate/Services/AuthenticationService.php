<?php

namespace Modules\Gate\Services;

use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Sso;
use Modules\Core\Models\Shared\RoleInternal;
use Modules\Core\Models\Shared\UserInternal;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\External\NavigationService;
use Modules\Core\Services\PegawaiManagementService;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\RoleAksesCache;
use Modules\Gate\Models\User;

class AuthenticationService
{
    const TOKEN_ALGORITHM = 'HS256';

    /**
     * Login ke aplikasi menggunakan authorization code SSO.
     *
     * @param string $code
     */
    public static function authenticateByCode(string $code, $reLogIn = false)
    {
        // dapatkan data dari authorization code
        $sso = new Sso;
        $data = $sso->verifyCode($code);
        if (Error::isError($data)) {
            return $data;
        }

        // cari user
        $role = session('token.role');
        $kodeUnit = session('token.unit');
        $callback = request()->client['url_siakad_callback'];
        $idPegawai = session('token.idpegawai');
        $activeModul = session('token.modul');
        $loginAs = session('token.login_as') ?? $data['user'];

        // cek organization
        $organization = static::checkingOrganization($kodeUnit);
        if (Error::isError($organization)) {
            return new Error(code: $organization->code ?? 'unit_not_found', callback: $callback);
        }

        // cek role
        $checkingRole = static::checkingRole($role);
        if (Error::isError($checkingRole)) {
            return new Error(code: $checkingRole->code ?? 'role_not_found', callback: $callback);
        }

        list($rawRole, $typeRole) = $checkingRole;
        $role = $rawRole?->kode_role;

        // cek jika role internal maka cek user internal jika tidak cek user external
        if (empty($loginAs['id']))
            $user = null;
        else {
            if ($typeRole == 'internal') {
                $user = UserInternal::where('id_user_sso', $loginAs['id'])->first();
            } else {
                $user = User::where('id_user_sso', $loginAs['id'])->first();
            }

            if (empty($user)) { // cek by email
                $user = User::where('email_user', $loginAs['email'])->first();

                // update id_user_sso
                if (!empty($user)) {
                    $user->id_user_sso = $loginAs['id'];
                    $user->save();
                }
            }
        }

        // kalau dua dua tidak ditemukan maka return error
        if (empty($user) && empty($role)) {
            return new Error(code: 'user_not_found', callback: $callback);
        }

        // jika ada role dan organization bisa create user dan atau user role
        $user = static::createAuthenticatedUser($user, $loginAs, $role, $organization, $typeRole);

        if (Error::isError($user)) {
            return new Error(code: $user->code ?? 'error_create_user', callback: $callback);
        }

        // sync userrole dari siakad 1 jika tidak ada role
        if (!empty($activeModul)) {
            [$err, $message] = (new RoleManagementService)->syncUserRoleFromSiakadV1($user['user'], $activeModul);
            if ($err) {
                return new Error(code: 'error_sync', message: $message, callback: $callback);
            }
        }

        if (Error::isError($user)) {
            return new Error(code: $user->code ?? 'user_not_found', callback: $callback);
        }

        // Jika pegawai maka update biodata pegawai
        if (!empty($idPegawai)) {
            (new PegawaiManagementService)->setUserIdPegawai($idPegawai, $user['user']);
        }

        // Assign Unit Kerja
        if (empty($user['id_unit']) && $typeRole === 'internal') {
            $unitKerja = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        } else {
            $unitKerja = UnitKerja::find($user['id_unit'] ?? null);
        }

        // jika ada role dan organization
        session()->put('user.id_role', $user['id_role'] ?? null);
        session()->put('user.id_unit', $user['id_unit'] ?? null);
        session()->put('user.unit_kerja', $unitKerja);

        // jika user internal
        $user = $user['user'];
        if (!empty($user->id_role_internal)) {
            session()->put('user.is_internal', true);
            Auth::setDefaultDriver('web_internal');
        }

        // Flush session navigasi menu
        NavigationService::flushToken();

        if ($reLogIn) {
            // jika user internal
            if (!empty($user->id_role_internal)) {
                session()->put('user.is_internal', true);
                Auth::setDefaultDriver('web_internal');
            } else {
                session()->put('user.is_internal', false);
                Auth::setDefaultDriver('web');
            }

            session()->forget('user.modul');

            // loginkan user
            Auth::login($user);

            session()->regenerate();

            Session::setId(self::createSessionId($data['session_id']));
            return $user;
        }

        // loginkan user
        Auth::login($user);

        // set session id
        session()->regenerate();

        Session::setId(self::createSessionId($data['session_id']));

        return $user;
    }

    /**
     * Login ke aplikasi menggunakan token.
     *
     * @param string $token
     */
    public static function authenticateByToken(string $token)
    {
        // ambil payload token aplikasi
        try {
            $payload = JWT::decode($token, new Key(config('app.key'), static::TOKEN_ALGORITHM));
        } catch (Exception) {
            return new Error('Token invalid');
        }

        // redirect jika ada kode pt
        if (!empty($payload->tenant)) {
            $param = [$payload->tenant];
            $sql = "select u.url
                    from klien c
                    join klien_url u on u.id_klien = c.id
                    where c.kode_klien = ? and c.waktu_dihapus is null and u.waktu_dihapus is null
                    order by ";
        }
        if (!empty($sql) && !empty($payload->url)) {
            $param[] = $payload->url . '%';
            $sql .= "case when u.url_siakad like ? then 0 else 1 end, ";
        }
        if (!empty($sql)) {
            $sql .= "u.id limit 1";
            $url = DB::connection('shared')->select($sql, $param)[0]->url ?? null;
            $asFolderPath = env('APP_ROOT_AS_PATH_FOLDER', null);

            if (!empty($asFolderPath)) {
                $url =  $url . '/' . $asFolderPath;
            }
        }

        $now = Carbon::now()->timestamp;
        if (!empty($url)) {
            $payload = (array)$payload;
            $payload['iat'] = $now;
            $payload['exp'] = $now + 60;
            unset($payload['kodept']);
            unset($payload['tenant']);

            $newToken = JWT::encode($payload, config('app.key'), static::TOKEN_ALGORITHM);
            return $url . '/gate/sessions/login?token=' . $newToken;
        }

        $loginAs = null;
        if (!empty($payload->login_as)) {
            $loginAs = (array)$payload->login_as;
        }

        // simpan data token ke session
        session()->put('token.role', $payload->role ?? null);
        session()->put('token.unit', $payload->unit ?? null);
        session()->put('token.login_as', $loginAs);
        session()->put('token.idpegawai', $payload->idpegawai ?? null);
        session()->put('token.url', $payload->url ?? null);
        session()->put('token.modul', $payload->modul ?? null);
        session()->put('token.id_univ', $payload->id_univ ?? null);
        session()->put('token.logo_univ', $payload->university_logo ?? null);
        session()->put('token.kode_univ', $payload->kode_univ ?? null);
        session()->put('token.nama_univ', $payload->nama_univ ?? null);
        session()->put('token.id_livechat', $payload->id_livechat ?? null);
        session()->put('token.restore_id', $payload->freshchat_restore_id ?? null);

        // redirect setelah login
        if (!session()->has('page.home') && !empty($payload->modul)) {
            session()->put('page.home', $payload->modul);
        }

        // FIXME: buat token sso untuk meloginkan (harusnya tidak boleh)
        $tokenSSO = JWT::encode([
            'aud' => config('services.sso.client_id'),
            'sub' => $payload->id,
            'sessid' => null, // untuk sekarang harus dicantumkan
            'iat' => $now,
            'exp' => $now + 60,
        ], config('services.sso.key'), static::TOKEN_ALGORITHM);

        // url auth sso
        $sso = new Sso;
        $redirectUri = url('/gate/sessions/auth');

        return $sso->getAuthorizeURL($tokenSSO, $redirectUri);
    }

    /**
     * Logout dari aplikasi menggunakan authorization code SSO.
     *
     * @param string $code
     */
    public static function logoutByCode(string $code)
    {
        // dapatkan data dari authorization code
        $sso = new Sso;
        $data = $sso->verifyCode($code);

        if (Error::isError($data)) {
            return $data;
        }

        // cek apakah session aktif
        if (Auth::check()) {
            // cek url token
            $url = session()->get('token.url');

            // logout user
            Auth::logout();

            // reset session
            session()->invalidate();
            session()->regenerateToken();

            // user aktif, akan di-redirect
            return $url ?? true;
        }

        // jika bukan session aktif delete recordnya
        $handler = Session::getHandler();
        if ($handler->read(self::createSessionId($data['session_id']))) {
            $handler->destroy(self::createSessionId($data['session_id']));
        }

        NavigationService::flushToken();

        // bukan user aktif, asumsi dari backend
        return false;
    }

    /**
     * Checking Organization or sync organization from siakadv1.
     *
     * @param string|null $kodeUnit
     * @return string
     */
    public static function checkingOrganization(string $kodeUnit = null)
    {
        $kodeUnitUser = self::getActiveUnit($kodeUnit);
        if (empty($kodeUnitUser)) {
            // sync v1 ke v2
            $syncV1 = new UnitKerjaManagementService;
            // sync organization
            list($err, $msg) = $syncV1->syncFromSiakadv1();

            if ($err) {
                return new Error(code: 'error_sync', message: $msg);
            }

            $kodeUnitUser = self::getActiveUnit($kodeUnit);
        }

        return $kodeUnitUser;
    }

    /**
     * Checking Role Internal / External. or sync role from siakadv1.
     *
     * string $token
     */
    public static function checkingRole($role, $isV2 = false)
    {
        $roleUser = null;
        list($roleUser, $typeRole) = self::getRoleAndType($role, $isV2);
        if (empty($roleUser)) {
            // sync v1 ke v2
            $syncV1 = new RoleManagementService;

            // sync external role
            $syncV1->syncFromSiakadv1();

            // get role
            list($roleUser, $typeRole) = self::getRoleAndType($role, $isV2);
        }

        if (!empty($roleUser) && $typeRole == 'external') {
            // cek permission role external
            $rolePermission = RoleAksesCache::findDefault($roleUser->id);
            if (empty($rolePermission)) {
                return new Error(code: 'role_not_have_permission');
            }
        }

        return [$roleUser, $typeRole];
    }

    public static function getRoleAndType($role, $isV2 = false)
    {
        $typeRole = 'internal';
        $internalRole = ($isV2 ? $role : RoleInternal::mapRoleInternalV1($role));
        $roleUser = RoleInternal::where('kode_role', $internalRole)->first();
        if (empty($roleUser)) {
            $typeRole = 'external';
            $externalRole = ($isV2 ? $role : Role::mapRoleInternalV1($role));
            $roleUser = Role::where('kode_role', $externalRole)->first();
        }

        return [$roleUser, $typeRole];
    }

    /**
     * Tambah user dari siAkad.
     *
     * @param mixed $user
     * @param array $data
     * @param string|null $role
     * @param string|null $organization
     * @param string|null $roleType (internal / external)
     */
    public static function createAuthenticatedUser($user, array $data, $role, $organization, $roleType = 'external')
    {
        // cek apakah ada user internal
        if ($roleType == 'internal') {
            $user = UserInternal::where('id_user_sso', $data['id'])->first();
        }

        // skip jika user internal dan role tipe internal
        if (!empty($user) && !empty($user->id_role_internal) && $roleType == 'internal') {
            $currentLevelRole = $user->role->level_cp;
            $requestRole = RoleInternal::where('kode_role', $role)->first(['level_cp', 'id']);

            // update data user
            if ($user->email_user != $data['email']) {
                $user->email_user = $data['email'];
                $user->save();
            }

            if ($user->nama_user != $data['name']) {
                $user->nama_user = $data['name'];
                $user->save();
            }

            // cek level role
            if ($currentLevelRole > $requestRole?->level_cp) {
                // update role
                $user->id_role_internal = $requestRole->id;
                $user->save();
            }

            return ['user' => $user];
        }

        if (empty($user) && (empty($data['email']) || empty($data['name']))) {
            return new Error(code: 'email_name_not_set');
        }

        // data user baru
        if (empty($user)) {
            $data = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'id_user_sso' => $data['id'],
            ];
        }

        // tambah user internal
        if ($roleType == 'internal') {
            $roleInternalId = RoleInternal::where('kode_role', $role)->first(['id'])?->id;

            $user = UserInternal::updateOrCreate(
            ['id_user_sso' => $data['id_user_sso']],
            [
                'nama_user' => $data['name'],
                'email_user' => $data['email'],
                'id_role_internal' => $roleInternalId
            ]
            );

            return ['user' => $user];
        }

        // cek role
        $roleId = Role::where('kode_role', $role)->first(['id'])?->id;
        if (empty($roleId)) {
            return new Error(code: 'role_not_found');
        }

        // cek organization
        $organizationId = UnitKerja::where('kode_unit', $organization)->first(['id'])?->id;
        if (empty($organizationId)) {
            return new Error(code: 'unit_not_found');
        }

        // tambah user
        if (empty($user)) {
            try {
                if (empty($data['id_user_sso'])) {
                    $checkColumn = [
                        'email_user' => $data['email']
                    ];
                } else {
                    $checkColumn = [
                        'id_user_sso' => $data['id_user_sso'],
                        'email_user' => $data['email']
                    ];
                }

                $user = User::updateOrCreate($checkColumn, [
                    'id_user_sso' => $data['id_user_sso'],
                    'nama_user' => $data['name'],
                    'email_user' => $data['email']
                ], $data);
            } catch (\Exception $e) {
                return new Error(code: 'error_create_user');
            }
        }

        // Tidak dipakai karena sudah dilakukan sync sebelum user login
        // tambah biodata jika tidak ada
        // Biodata::firstOrCreate([
        //     'id_user' => $user->id,
        //     'nama' => $data['name'],
        //     'email' => $data['email'],
        //     'telepon' => $data['phone'] ?? null,
        // ]);

        return ['user' => $user, 'id_role' => $roleId, 'id_unit' => $organizationId];
    }

    /**
     * Refresh List Role User And Permission each role
     *
     * @param UserInternal|User $user
     */
    public static function getListRole($user)
    {
        $email = $user->email;
        $userInternalRole = UserInternal::where('email_user', $email)->first()?->role;
        $userExternalRole = User::where('email_user', $email)->first()?->roles;

        $userRole = [];
        if ($userInternalRole) {
            $userRole[$userInternalRole->kode_role] = $userInternalRole->name;
        }
        if ($userExternalRole) {
            foreach ($userExternalRole as $role) {
                $userRole[$role->kode_role] = $role->name;
            }
        }

        return $userRole;
    }

    /**
     * Switch Role
     *
     * @param string $role
     * @param int $idOrganization
     * @param string|null $callback
     * @param bool $isV2
     * return array
     */
    public static function switchRole($role, $idOrganization, $callback = null, $isV2 = false)
    {
        // user
        $user = Auth::user();

        // cek role
        $checkingRole = static::checkingRole($role, $isV2);
        if (Error::isError($checkingRole)) {
            return new Error(code: $checkingRole->code ?? 'role_not_found', callback: $callback);
        }

        list($rawRole, $typeRole) = $checkingRole;
        $role = $rawRole?->kode_role;

        // cek organization
        $kodeUnit = UnitKerja::select('kode_unit')->find($idOrganization)?->kode_unit;
        if (empty($kodeUnit) && $typeRole !== 'internal') {
            return new Error(code: 'unit_not_found', callback: $callback);
        }

        $organization = static::checkingOrganization($kodeUnit);
        if (Error::isError($organization) && $typeRole !== 'internal') {
            return new Error(code: $kodeUnit->code ?? 'unit_not_found', callback: $callback);
        }

        // cek jika role internal maka cek user internal jika tidak cek user external
        if ($typeRole == 'internal') {
            $user = UserInternal::where('email_user', $user->email_user)->first();
        } else {
            $user = User::where('email_user', $user->email_user)->first();
        }

        // kalau dua dua tidak ditemukan maka return error
        if (empty($user) && empty($role)) {
            return new Error('User tidak ditemukan');
        }

        if ($idOrganization == 0) {
            $unitKerja = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        } else {
            $unitKerja = UnitKerja::find($idOrganization ?? null);
        }

        // jika ada role dan organization
        session()->put('user.id_role', $rawRole?->id);
        session()->put('user.id_unit', $idOrganization);
        session()->put('user.unit_kerja', $unitKerja);

        // jika user internal
        if (!empty($user->id_role_internal)) {
            session()->put('user.is_internal', true);
            Auth::setDefaultDriver('web_internal');
        } else {
            session()->put('user.is_internal', false);
            Auth::setDefaultDriver('web');
        }

        // delete session modules
        session()->forget('user.modul');

        // Flush session navigasi menu
        NavigationService::flushToken();

        // loginkan user
        Auth::login($user);

        return $user;
    }

    protected static function getActiveUnit($kodeUnit)
    {
        // get organization after sync
        $kodeUnitUser = UnitKerja::where('ref_key_satker', $kodeUnit)->first(['kode_unit'])?->kode_unit;
        if (empty($kodeUnitUser)) {
            $kodeUnitUser = UnitKerja::where('kode_unit', $kodeUnit)->first(['kode_unit'])?->kode_unit;
        }

        return $kodeUnitUser;
    }

    protected static function createSessionId($sessionId)
    {
        $newSessionId = request()->client['kode_klien'] ?? 'web';
        $newSessionId .= $sessionId;

        return substr($newSessionId, 0, 40);
    }
}
