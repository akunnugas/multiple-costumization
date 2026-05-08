<?php

namespace Modules\Gate\Http\Controllers\Web;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Sso;
use Modules\Gate\Services\AuthenticationService;

class SessionController extends Controller
{
    /**
     * Authenticate user.
     */
    public function authenticate(Request $request)
    {
        // FIXME: login manual dan ada error
        if ($request->login) {
            return redirect()->route('login', ['login' => 1]);
        }

        if ($request->error) {
            $callback = $request->client['url_siakad_callback'];
            return redirect()->away($callback . '?error=' . $request?->error);
        }

        // logout sso
        if ($request->off && $request->code) {
            return static::destroyByCode($request->code);
        }

        // login sso
        if ($request->code) {
            return static::storeByCode($request->code);
        }
    }

    /**
     * Login with token.
     */
    public function login(Request $request)
    {
        $url = AuthenticationService::authenticateByToken($request->token);

        if (Error::isError($url)) {
            return redirect()->route('login');
        }

        return redirect()->away($url);
    }

    /**
     * Single sign out ke SSO.
     */
    public function destroy()
    {
        $sso = new Sso;

        $redirectUri = url('/gate/sessions/auth');

        return redirect()->away($sso->getLogoutUrl($redirectUri));
    }

    /**
     * Redirect to siAkad login page.
     */
    public function index(Request $request)
    {
        $url = session('token.url') ?? $request->client['siakad_url'] ?? null;
        if (empty($url)) {
            $url = 'https://siakadcloud.com';
        } else {
            $url .= '/gate/login';
        }

        return redirect()->away($url);
    }

    /**
     * Show home page.
     */
    public function home()
    {
        // FIXME: tampilan sementara
        return view('core::pages.temp.home');
    }

    /**
     * Sign user in.
     *
     * @param string $code
     * @return RedirectResponse
     */
    private static function storeByCode(string $code)
    {
        $auth = Auth::check();

        if ($auth) {
            $auth = AuthenticationService::authenticateByCode($code, true);
        } else {
            $auth = AuthenticationService::authenticateByCode($code);
        }

        if (Error::isError($auth)) {
            if (!empty($auth->callback)) {
                return redirect()->away($auth->callback . '?error=' . $auth->code);
            }

            $siakadV1Url = request()->client['url_siakad'] ?? env('URL_SIAKADV1');
            $urlV1 = $siakadV1Url . '/gate';
            return redirect()->away($urlV1);
        }

        // FIXME: intended dipisah per modul
        return redirect()->intended(session()->pull('page.home', RouteServiceProvider::HOME));
    }

    /**
     * Sign user out.
     *
     * @param string $code
     */
    private static function destroyByCode(string $code)
    {
        // tidak dicek apakah login karena logout bisa dari backend
        $url = AuthenticationService::logoutByCode($code);

        if (Error::isError($url)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if ($url === false) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        if ($url === true) {
            return redirect()->route('login');
        }

        // NOTE: Untuk sementara langsung redirect endpoint logout dari siakad v1
        $siakadV1Url = request()->client['url_siakad'] ?? env('URL_SIAKADV1');
        $urlV1 = $siakadV1Url;
        $url = $urlV1 . '/gate/logout';

        return redirect()->away($url);
    }

    /**
     * Change Role
     *
     * return array
     */
    public function switchRole(Request $request)
    {
        $previousModule = $request->module;

        if (!$previousModule) {
            return back()->withErrors('Error');
        }

        $role = $request->role;
        $organization = $request->organization;

        if (!$role) {
            return back()->withErrors('Error');
        }

        $user = AuthenticationService::switchRole(role: $role, idOrganization: (int) $organization, isV2: true);
        if (Error::isError($user)) {
            return back()->withErrors($user);
        }

        return redirect()->intended(session()->pull('page.home', $previousModule));
    }
}
