<?php

namespace Modules\Core\Services\External;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Modules\Core\Models\UnitKerja;

class NavigationService
{
    public string $url;
    public string $appId;

    protected string $secret;

    public function __construct()
    {
        $this->url = env('NAVIGATION_SERVICE_URL');
        $this->appId = env('NAVIGATION_SERVICE_APP_ID');
        $this->secret = env('NAVIGATION_SERVICE_SECRET');
    }

    public function getSessionToken()
    {
        if (!auth()->check()) {
            return null;
        }

        $token = [];
        $lastSession = session()->get('navigation_service.token');
        if (!empty($lastSession) && $this->checkValidToken($lastSession)) {
            $token['token'] = $lastSession;
            $token['flush_cache'] = false;
            return $token;
        }

        $token['token'] = $this->generateToken();
        $token['flush_cache'] = true;

        return $token;
    }

    public static function flushToken()
    {
        session()->forget('navigation_service.token');
    }

    protected function checkValidToken($token)
    {
        try {
            JWT::decode($token, new Key($this->secret, 'HS256'));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function generateToken()
    {
        $client = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        $config = [
            'host' => request()->getSchemeAndHttpHost(),
            'user' => [
                'reference_code' => auth()->user()->id,
                'name' => auth()->user()->nama_user,
                'email' => auth()->user()->email_user,
                'role' => auth()->user()->kode_role,
                'role_name' => auth()->user()->nama_role,
                'timezone' => 'Asia/Jakarta'
            ],
            'client' => [
                'code' => $client->kode_unit,
                'name' => $client->nama_unit,
            ]
        ];

        $token = JWT::encode($config, $this->secret, 'HS256');

        // Set token di session
        session()->put('navigation_service.token', $token);

        return $token;
    }
}
