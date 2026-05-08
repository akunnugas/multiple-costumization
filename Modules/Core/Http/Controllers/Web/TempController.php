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
        return redirect()->to('/');
    }

    public function redirectToActiveModule(Request $request) {
        return redirect()->to('/');
    }

    // login
    public function index()
    {
        return view('core::pages.temp.index', [
            'ssoURL' => '#',
            'admin' => null,
            'sample' => null
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
