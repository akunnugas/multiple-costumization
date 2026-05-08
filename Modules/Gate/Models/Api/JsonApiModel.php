<?php

namespace Modules\Gate\Models\Api;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class JsonApiModel extends Model
{
    const CONTENT_TYPE_JSON = 'application/json';

    public static function getRequest($url, $headers = [], $body = []):PromiseInterface|Response
    {
        $request =  Http::withHeaders($headers);
        if (!empty($body)) {
            $request->withBody($body, self::CONTENT_TYPE_JSON);
        }

        return $request->get($url);
    }

    public static function postRequest($url, $headers = [], $data = []): PromiseInterface|Response
    {
        $data = json_encode(['data' => ['attributes' => $data]]);
        return Http::withHeaders($headers)
            ->withBody($data, self::CONTENT_TYPE_JSON)
            ->post($url);
    }

    public static function patchRequest($url, $headers = [], $data = [], $id = null): PromiseInterface|Response
    {
        $data = ['data' => ['attributes' => $data]];
        if (!empty($id)) {
            $data['data']['id'] = $id;
        }

        $data = json_encode($data);
        return Http::withHeaders($headers)
            ->withBody($data, self::CONTENT_TYPE_JSON)
            ->patch($url);
    }

    public static function deleteRequest($url, $headers = []): PromiseInterface|Response
    {
        return Http::withHeaders($headers)
            ->delete($url);
    }
}
