<?php

namespace Modules\Admission\Http\Controllers\Web;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Admission\Providers\RouteServiceProvider;
use Modules\Admission\Services\AuthenticationService;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Sso;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SessionController extends Controller
{
    /**
     * Authenticate user yang menerima code/token dari SSO.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authenticate(Request $request)
    {
        if ($request->error) { // handle error dari sso
            $defaultError = 'Terjadi kesalahan saat login. Silahkan coba lagi.';
            return redirect()->route('admission.login')->with('error', $defaultError);
        }

        if ($request->off && $request->code) {
            return $this->destroyByCode($request->code);
        }

        if ($request->code) {
            return $this->storeByCode($request->code);
        }

        return redirect()->route('admission.login');
    }

    /**
     * Login with email and password.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function login(Request $request)
    {
        // validation email and password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $url = AuthenticationService::loginAdmission($request->email, $request->password);

        if (Error::isError($url)) {
            return redirect()->route('admission.login')->with('error', $url->message);
        }

        return redirect()->away($url);
    }

    public function forgotPassword(Request $request)
    {
        // validation email and password
        $request->validate([
            'email' => 'required|email',
        ]);

        // FIXME: fitur forgot password yg hit API belum ada di SSO

//        $url = AuthenticationService::forgotPassword($request->email);
//
//        if (Error::isError($url)) {
//            return redirect()->route('admission.login')->with('error', $url->message);
//        }

//        return redirect()->away($url);
    }

    /**
     * Single sign out ke SSO.
     *
     * @return RedirectResponse
     */
    public function destroy()
    {
        $sso = new Sso;

        $redirectUri = request()->getSchemeAndHttpHost() . '/admission/auth';
        return redirect()->away($sso->getLogoutUrl($redirectUri));
    }

    /**
     * Sign user in.
     *
     * @param string $code
     * @return RedirectResponse
     */
    private function storeByCode(string $code)
    {
        $auth = Auth::guard('admission')->check();

        if (!$auth) {
            $auth = AuthenticationService::authenticateByCode($code);
        }

        // jika ada error selama proses meloginkan user ke aplikasi
        if (Error::isError($auth)) {
            return redirect()->route('admission.login')->with('error', $auth->message);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Sign user out.
     *
     * @param string $code
     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|RedirectResponse|Response
     */
    private static function destroyByCode(string $code)
    {
        // tidak dicek apakah login karena logout bisa dari backend
        $url = AuthenticationService::logoutByCode($code);

        if (Error::isError($url)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if ($url === false) {
            return response(null, ResponseAlias::HTTP_NO_CONTENT);
        }

        if ($url === true) {
            return redirect()->route('admission.home');
        }

        return redirect()->away($url);
    }
}
