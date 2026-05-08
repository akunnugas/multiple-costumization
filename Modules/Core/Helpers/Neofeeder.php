<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class Neofeeder
{
    public $url;
    public $username;
    public $password;
    public $token;
    public $tokenExpired;
    public $action;
    public $filter;
    public $order;
    public $limit;
    public $offset;

    /**
     * Membuat instance Neofeeder baru.
     */
    public function __construct() 
    {
        #FIXME: harusnya ambil dari db
        $this->url = '';
        $this->username = '';
        $this->password = '';
        $this->limit = 10;
    }

    public function setFilter($filter) : Neofeeder
    {
        $this->filter = $filter;
        return $this;
    }

    public function setOrder($order) : Neofeeder
    {
        $this->order = $order;
        return $this;
    }

    public function setLimit($limit) : Neofeeder
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset) : Neofeeder
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get the token from Neofeeder API.
     *
     * @return string|Error The token string or an Error object if there is an error.
     */
    public function getToken(): mixed{

        if ($this->tokenExpired > now() && $this->token != null) {
            return $this->token;
        }

        $url = $this->url;
        $action = 'GetToken';

        $data = [
            'act' => $action,
            'username' => $this->username,
            'password' => $this->password,
        ];

        $response = $this->postJson($url, $data);

        $this->token = $response['data']['token'];
        $this->tokenExpired = now()->addMinutes(29);

        return $this->token;
    }

    public function isValidAction($action){
        $data = [
            'act' => 'GetDictionary',
            'token' => $this->getToken(),
            'fungsi' => $action,
        ];

        $response = $this->postJson($this->url, $data);

        if ($response instanceof Error) {
            return $response;
        }

        return true;
    }

    public function exec($action): mixed{
        
        $this->action = $action;

        $isValidAction = $this->isValidAction($this->action);
        if ($isValidAction instanceof Error) {
            return $isValidAction;
        }

        $data = [
            'act' => $this->action,
            'token' => $this->getToken(),
            'filter' => $this->filter,
            'order' => $this->order,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];

        $response = $this->postJson($this->url, $data);

        if ($response instanceof Error) {
            return $response;
        }

        return $response;
    }

    /**
     * Post ke API Neofeeder.
     *
     * @param string $url
     * @param array $data
     *
     * @return mixed
     */
    private function postJson(string $url, array $data)
    {
        try {

            $response = Http::post($url, $data);
            $data = $response->json();

            if ($data['error_code'] != 0) {
                return new Error($data['error_desc'], $data['error_code']);
            }
            
            return $data;

        } catch (\Exception $th) {

            return new Error($th->getMessage());
        
        }
    }
}
