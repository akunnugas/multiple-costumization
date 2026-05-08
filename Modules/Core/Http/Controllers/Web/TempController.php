<?php

namespace Modules\Core\Http\Controllers\Web;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Sso;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Shared\UserInternal;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Services\AuthenticationService;

class TempController extends Controller
{
    public function redirectGateV1(Request $request)
    {
        $siakadV1Url = $request->client['url_siakad'] ?? env('URL_SIAKADV1');
        $urlV1 = $siakadV1Url . '/gate';
        return redirect()->away($urlV1);
    }

    public function redirectToActiveModule(Request $request) {
        $siakadV1Url = $request->client['url_siakad'] ?? env('URL_SIAKADV1');
        $urlV1 = $siakadV1Url . '/gate';
        $authUser = auth()->user();

        if (empty($authUser)) {
            return redirect()->away($urlV1);
        }

        $userModul = session()->get('user.modul');
        if (empty($userModul)) {
            return redirect()->away(session()->get('token.url') . '?error=permission_denied');
        }

        return redirect()->away($urlV1);
    }

    // login
    public function index()
    {
        $sso = new Sso;

        // client dan url
        $sql = "select c.kode_dikti, u.url_siakad, c.kode_klien
                from klien c
                join klien_url u on u.id_klien = c.id
                where c.waktu_dihapus is null and u.waktu_dihapus is null
                order by c.id limit 1";
        $client = DB::connection('shared')->select($sql)[0] ?? null;

        // modul admin menggunakan user internal
        $module = Modul::where('kode_modul', Modul::CODE_ADMIN)->first();
        if (!empty($module)) {
            $admin = UserInternal::with('role')->whereNotNull('id_user_sso')->orderBy('id')->first()?->toArray();
        }

        if (!empty($admin)) {
            $admin = [
                'name' => $module->nama_modul,
                'user' => $admin,
                'url' => $this->loginURL([
                    'id' => $admin['id_user_sso'],
                    'modul' => $module->kode_modul,
                    'kodept' => $client->kode_dikti ?? null,
                    'tenant' => $client->kode_klien ?? null,
                    'url' => $client->url_siakad ?? null,
                ])
            ];
        }

        // cek modul contoh
        $modules = Modul::whereIn('kode_modul', [Modul::CODE_SAMPLE, Modul::CODE_DMS, Modul::CODE_LITABMAS])->get();
        foreach ($modules as $module) {
            if (!empty($module)) {
                $sql = "select distinct p.id_role
                        from gate.role_akses p
                        join gate.resource r on r.id = p.id_resource and r.id_modul = ? and r.waktu_dihapus is null
                        where p.bisa_get = true";
                $rows = DB::select($sql, [$module->id]);

                $sample = [
                    'name' => $module->nama_modul,
                    'users' => []
                ];

                if (!empty($admin)) {
                    $sample['users'][] = [
                        'role_name' => $admin['user']['role']['nama_role'],
                        'url' => $this->loginURL([
                            'id' => $admin['user']['id_user_sso'],
                            'modul' => $module->kode_modul,
                            'kodept' => $client->kode_dikti ?? null,
                            'tenant' => $client->kode_klien ?? null,
                            'url' => $client->url_siakad ?? null,
                        ])
                    ] + $admin;
                }

                foreach ($rows as $row) {
                    $sql = "select r.id_user, r.id_unit_kerja
                            from gate.user_role r
                            join gate.user u on u.id = r.id_user and u.id_user_sso is not null and u.waktu_dihapus is null
                            where r.id_role = ? and r.waktu_dihapus is null
                            order by r.id limit 1";
                    $userRole = DB::select($sql, [$row->id_role]);

                    if (empty($userRole)) {
                        continue;
                    }

                    $userRole = $userRole[0];
                    $role = Role::find($row->id_role);
                    $organization = UnitKerja::find($userRole->id_unit_kerja);

                    $user = User::find($userRole->id_user)->toArray();
                    $user['role_name'] = $role->nama_role;
                    $user['url'] = $this->loginURL([
                        'id' => $user['id_user_sso'],
                        'modul' => $module->kode_modul,
                        'role' => $role->kode_role,
                        'unit' => $organization->kode_unit,
                        'kodept' => $client->kode_dikti ?? null,
                        'tenant' => $client->kode_klien ?? null,
                        'url' => $client->url_siakad ?? null,
                    ]);

                    $sample['users'][] = $user;
                }

                if (empty($sample['users'])) {
                    $sample = null;
                }
            }
        }

        return view('core::pages.temp.index', [
            'ssoURL' => $sso->getAuthorizeURL(),
            'admin' => $admin ?? null,
            'sample' => $sample ?? null
        ]);
    }

    // home
    public function home()
    {
        // cek akses modul
        $modules = Auth::user()->modul;

        return view('core::pages.temp.menu', [
            'modules' => Error::returnValue($modules)
        ]);
    }

    // migrasi user ke sso
    public function migrate()
    {
        set_time_limit(0);

        $users = User::whereNull('id_user_sso')->get();
        $sso = new Sso;

        foreach ($users as $user) {
            $response = Http::withHeaders([
                'Client-Id' => $sso->clientId,
                'Client-Secret' => $sso->clientSecret,
                'Content-Type' => 'application/json'
            ])->post($sso->address . '/api/v1/users', [
                'data' => [
                    'type' => 'users',
                    'attributes' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => 'sembarang',
                        'is_active' => true,
                    ],
                ],
            ]);

            if ($response->failed()) {
                continue;
            }

            $response = $response->json();

            $user->id_user_sso = $response['data']['id'];
            $user->save();
        }

        return back();
    }

    // url login dengan token
    public function loginURL($data)
    {
        $iat = Carbon::now()->timestamp;

        return url('gate/sessions/login?token=' . JWT::encode([
            'iat' => $iat,
            'exp' => $iat + 60,
        ] + $data, config('app.key'), AuthenticationService::TOKEN_ALGORITHM));
    }
}
