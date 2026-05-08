<?php

namespace Modules\Gate\Services\SiakadV1;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\SiakadV1;

class UserService
{
    private string $connection = 'siakadv1';
    private string $table = 'gate.sc_user';

    public function findByEmail($email)
    {
        $conn = DB::connection($this->connection);
        $result = $conn->select("SELECT userid, username, email FROM $this->table WHERE email = :email", [
            'email' => $email
        ]);

        // get first key if exists
        return $result[0] ?? null;
    }

    /**
     * Membuat user di siakad v1.
     *
     * @param array $data
     * @return array|Error
     * @throws ValidationException
     */
    public function store(array $data)
    {
        $conn = DB::connection($this->connection);

        // allowed record
        $data = Arr::only($data, [
            'username', 'userdesc', 'isactive', 'expired', 'email', 'generatesandi', 'hints'
        ]);

        if (!empty($data['generatesandi'])) {
            $data['hints'] = $this->generateHints();
        }

        // validasi
        Validator::make($data, [
            'username' => 'required|max:50', 'userdesc' => 'required', 'email' => 'required|email'
        ], attributes: [
            'username' => 'Username', 'userdesc' => 'User Description', 'email' => 'Email',
        ])->validate();

        // validasi unique
        $result = $this->checkUniqueUser($conn, $data);
        if ($result instanceof Error) {
            return $result;
        }

        // tambahkan detail info act
        $data = SiakadV1::setLogAction($data);
        unset($data['generatesandi']);

        // insert
        try {
            $conn->insert("INSERT INTO $this->table (username, userdesc, isactive, expired, email, hints,
                    t_updateuser, t_updatetime, t_updateip, t_updateact)
                VALUES (:username, :userdesc, :isactive, :expired, :email, :hints,
                    :t_updateuser, :t_updatetime, :t_updateip, :t_updateact)", $data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $data;
    }

    /**
     * Menghapus user di siakad v1.
     * Utk role otomatis ikut kehapus karena delete cascade.
     *
     * @param $id
     * @return Error|true
     */
    public function destroy($id)
    {
        try {
            $conn = DB::connection($this->connection);
            $conn->delete("DELETE FROM $this->table WHERE userid = :userid", [
                'userid' => $id
            ]);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return true;
    }

    /**
     * Cek unique user berdsaarkan email atau username di siakad v1.
     *
     * @param $conn
     * @param array $data
     * @return Error|void
     */
    public function checkUniqueUser($conn, array $data)
    {
        $isEmailExists = $conn->select("SELECT 1 FROM $this->table WHERE email = :email", [
            'email' => $data['email']
        ]);
        if (!empty($isEmailExists)) {
            return new Error('Email sudah terdaftar', 422);
        }

        $isUsernameExists = $conn->select("SELECT 1 FROM $this->table WHERE username = :username", [
            'username' => $data['username']
        ]);
        if (!empty($isUsernameExists)) {
            return new Error('Username sudah terdaftar', 422);
        }
    }

    /**
     * Membuat random string
     * sama dengan method getRandomString() di CSTR class di siakad v1
     *
     * @param $len
     * @param $isnum
     * @return string
     */
    public function generateHints($len = 8, $isnum = false)
    {
        $pool = '0123456789';
        if (!$isnum)
            $pool .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $str = '';
        $poolLength = strlen($pool);
        for ($i = 0; $i < $len; $i++) {
            $randomIndex = random_int(0, $poolLength - 1);
            $str .= substr($pool, $randomIndex, 1);
        }

        return strtolower($str);
    }
}
