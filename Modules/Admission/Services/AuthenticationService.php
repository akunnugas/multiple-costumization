<?php

namespace Modules\Admission\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Sso;
use Modules\Gate\Models\User;

class AuthenticationService
{
    public static function loginAdmission($email, $password)
    {
        // DO: cari user by email
        $user = User::where('email', $email)->first();
        if (empty($user)) {
            return new Error('User tidak ditemukan');
        }

        // jika akun user belum konek sso
        if (empty($user['sso_user_id'])) {
            // DO: loginkan sso
            $sso = new Sso;
            $ssoResponse = $sso->login($email, $password);
            // jika gagal sso
            if (Error::isError($ssoResponse)) {
                // DO: cek by email dan password (tgl lahir)
                // karena pmb front/admission defaultnya password itu tgl lahir
                $userBirthDate = Carbon::parse($user->person->birth_date)->format('dmY');
                if ($userBirthDate !== $password) {
                    return new Error('Email atau password tidak sesuai');
                }

                // FIXME: kalo sso user id belum ada tapi username dan password sesuai mau diapain?
                // error message dari sso
//                return new Error($ssoResponse->message);
            }
        } else {
            // DO: loginkan sso
            $sso = new Sso;
            $ssoResponse = $sso->login($email, $password);
            // jika gagal sso
            if (Error::isError($ssoResponse)) {
                return new Error($ssoResponse->message);
            }
        }

        // jika berhasil dapet token sso
        $redirectUri = request()->getSchemeAndHttpHost() . '/admission/auth';
        return $sso->getAuthorizeUrl(redirectUri: $redirectUri) . '&token=' . $ssoResponse['code'];
    }

    /**
     * Login ke aplikasi menggunakan authorization code SSO.
     *
     * @param string $code
     */
    public static function authenticateByCode(string $code)
    {
        // dapatkan data dari authorization code
        $sso = new Sso;
        $data = $sso->verifyCode($code);

        if (Error::isError($data)) {
            return $data;
        }

        // DO: Cek expired token
        $payload = $sso->getCodePayload($code);
        if (time() > $payload['exp']) {
            return new Error('Token sudah kadaluarsa, silakan login kembali.');
        }

        // DO: get user by sso_user_id
        $userSso = Arr::only($data['user'], ['id', 'name', 'email', 'email_verified_at', 'phone']);
        $user = User::where('sso_user_id', $userSso['id'])->first();
        if (empty($user)) { // user belum terdaftar di sso.
            // DO: get by email
            $user = User::where('email', $userSso['email'])->first();
            if (empty($user)) {
                return new Error('User SSO tidak ditemukan');
            }

            // update data from usersso, except id
            $user->update([
                'sso_user_id' => $userSso['id'],
                'email_verified_at' => $userSso['email_verified_at'],
            ]);
        }

        // todo: melengkapi data user kalo belum lengkap.
        // DO: get person by user id
        $person = $user->person;
        if (empty($person)) {
            return new Error('Data user tidak ditemukan');
        }

        // DO: get peserta/pendaftar by person id
        $registrant = $person->registrant;
        if (empty($registrant)) {
            return new Error('Anda belum memiliki akun pendaftar, silakan daftar terlebih dahulu.');
        }

        // TODO: seharusnya ada pengecekan step pendaftar lagi dimana dan nanti rediret ke situ

        // set default guard
        Auth::setDefaultDriver('admission');

        // Do: set session
        // FIXME: session sesuai kebutuhan
//        session()->put('sso_data', $data);
//        session()->put('user', $user);
        session()->put('person', $person);
        session()->put('registrant', $registrant);

        // Do: loginkan user
        Auth::login($user);

        // Do: set session id
        session()->regenerate();
        Session::setId($data['session_id']);

        return $user;
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
            // logout user
            Auth::logout();

            // reset session
            session()->invalidate();
            session()->regenerateToken();

            // user aktif, akan di-redirect
            return true;
        }

        // jika bukan session aktif delete recordnya
        $handler = Session::getHandler();
        if ($handler->read($data['session_id'])) {
            $handler->destroy($data['session_id']);
        }

        // bukan user aktif, asumsi dari backend
        return false;
    }
}
