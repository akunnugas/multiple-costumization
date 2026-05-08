<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class Sso
{
    public $address;
    public $apiAddress;
    public $clientId;
    public $clientSecret;
    public $redirectUri;

    /**
     * Membuat instance SSO baru.
     */
    public function __construct()
    {
        $this->address = config('services.sso.url');
        $this->apiAddress = config('services.sso.api_url_v1');
        $this->clientId = config('services.sso.client_id');
        $this->clientSecret = config('services.sso.client_secret');
        $this->redirectUri = config('services.sso.redirect_uri');
    }

    /**
     * Membuat URL authorize OAuth.
     *
     * @param null $token
     * @param null $redirectUri
     * @return string
     */
    public function getAuthorizeUrl($token = null, $redirectUri = null): string
    {
        return $this->getOAuthUrl(page: 'authorize', redirectUri: $redirectUri) . ($token ? '&token=' . $token : '');
    }

    /**
     * Membuat URL logout.
     *
     * @param null $redirectUri
     * @return string
     */
    public function getLogoutUrl($redirectUri = null): string
    {
        return $this->getOAuthUrl('logout', redirectUri: $redirectUri);
    }

    /**
     * Verifikasi authorization code dan mendapatkan data user.
     *
     * @param string $code
     *
     * @return mixed
     */
    public function verifyCode(string $code)
    {
        /**
         * array data:
         * - session_id
         * - user (data user)
         */
        return $this->postApi('sessions/verify', [
            'code' => $code,
        ]);
    }

    /**
     * Hit API Login sso utk mendapatkan code yang nantinya digunakan utk verifikasi..
     *
     * @param string $email
     * @param string $password
     * @return mixed|Error
     */
    public function login(string $email, string $password)
    {
        $response = $this->postApi('sessions/login', [
            'email' => $email,
            'password' => $password,
            'response_type' => 'code',
        ], true);

        if ($response instanceof Error) {
            $errorMessage = static::getLoginMessageFromErrorCode($response->message['error_code']);
            return new Error($errorMessage);
        }

        return $response;
    }

    /**
     * Mendapatkan payload dari authorization code.
     *
     * @param string $code
     *
     * @return array
     */
    public function getCodePayload(string $code): array
    {
        list(, $payload) = explode('.', $code);

        return json_decode(base64_decode($payload), true);
    }

    /**
     * Membuat state baru.
     *
     * @return string
     */
    public static function generateState(): string
    {
        $state = Str::random();
        Session::put('state', $state);

        return $state;
    }

    /**
     * Mendapatkan state.
     *
     * @return string|null
     */
    public static function getState(): string|null
    {
        return Session::get('state');
    }

    /**
     * Membuat URL OAuth.
     *
     * @param string $page
     * @param bool $noQuery
     * @param string $responseType
     * @param string|null $redirectUri
     *
     * @return string
     */
    private function getOAuthUrl(
        string $page,
        bool $noQuery = false,
        string $responseType = 'code',
        string $redirectUri = null
    ): string {
        $url = $this->address . '/sessions/' . $page;

        if (empty($noQuery)) {
            $query = [
                'client_id=' . $this->clientId,
                'response_type=' . $responseType,
                'redirect_uri=' . ($redirectUri ?? $this->redirectUri),
                'state=' . static::generateState()
            ];

            $url .= '?' . implode('&', $query);
        }

        return $url;
    }

    /**
     * Post ke API SSO.
     *
     * @param string $act
     * @param array $data
     * @param bool $withMessage
     * @return mixed|Error
     */
    private function postApi(string $act, array $data, bool $withMessage = false)
    {
        $url = $this->apiAddress . '/' . $act;
        $response = Http::post($url, $data + [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ]);
        $data = $response->json();

        if ($response->successful()) {
            return $data;
        }

        return new Error($withMessage ? $data : null);
    }


    private static function getLoginMessageFromErrorCode(string $code)
    {
        return match ($code) {
            'email_not_found' => 'Email atau password tidak sesuai',
            'password_invalid' => 'Email atau password tidak sesuai',
            'email_inactive' => __('validation.inactive', ['attribute' => 'email']),
            default => null,
        };
    }
}
