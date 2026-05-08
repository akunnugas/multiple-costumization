<?php

namespace Modules\Gate\Models\Api;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class UserSso extends JsonApiModel
{
    /**
     * Get user sso by email.
     *
     * @param string $email
     * @return array|null
     * @throws RequestException
     */
    public static function findUserByEmail(string $email)
    {
        $url = config('services.sso.api_url_v1') . '/users?filter[email]=' . $email;
        $headers = static::headers();

        $response = static::getRequest($url, $headers)->throw()->json();

        return $response['data'][0] ?? null;
    }

    /**
     * Get data invitations by email.
     *
     * @param string $email
     * @return RequestException|PromiseInterface|Response|array
     * @throws RequestException
     */
    public static function findInvitationByEmail(string $email): RequestException|PromiseInterface|Response|array
    {
        $url = config('services.sso.api_url_v1') . '/invitations?filter[email]=' . $email;
        $headers = static::headers();

        return static::getRequest($url, $headers)->throw()->json();
    }

    /**
     * Invite user to join cbt via email.
     *
     * @param $data
     * @param null $redirectUri
     * @return RequestException|PromiseInterface|Response|array
     * @throws RequestException
     */
    public static function inviteEmailUser($data, $redirectUri = null): RequestException|PromiseInterface|Response|array
    {
        $url = config('services.sso.api_url_v1') . '/invitations';
        $headers = static::headers();
        $headers['Redirect-Uri'] = $redirectUri ?? config('services.sso.redirect_uri');

        return static::postRequest($url, $headers, $data)->throw()->json();
    }

    /**
     * Re-invite user to join cbt via email using id invitation.
     *
     * @param $id
     * @param $data
     * @param null $redirectUri
     * @return RequestException|PromiseInterface|Response|array
     * @throws RequestException
     */
    public static function reInviteEmailUser($id, $data, $redirectUri = null): RequestException|PromiseInterface|Response|array
    {
        $url = config('services.sso.api_url_v1') . '/invitations/' . $id;
        $headers = static::headers();
        $headers['Redirect-Uri'] = $redirectUri ?? config('services.sso.redirect_uri');

        return static::patchRequest($url, $headers, $data, $id)->throw()->json();
    }

    /**
     * Headers for API request.
     *
     * @return array
     */
    private static function headers(): array
    {
        return [
            'Client-Id' => config('services.sso.client_id'),
            'Client-Secret' => config('services.sso.client_secret'),
            'Content-Type' => 'application/json',
        ];
    }
}
