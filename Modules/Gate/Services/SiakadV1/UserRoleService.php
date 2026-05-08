<?php

namespace Modules\Gate\Services\SiakadV1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\SiakadV1;

class UserRoleService
{
    private string $connection = 'siakadv1';
    private string $table = 'gate.sc_userrole';

    /**
     * Membuat user role di siakad v1.
     *
     * @param array $data
     * @return array|Error
     */
    public function store(array $data)
    {
        $conn = DB::connection($this->connection);

        // allowed record
        $data = Arr::only($data, [
            'idrole', 'idsatker', 'userid'
        ]);

        // validasi unique
        $result = $this->checkUniqueUserRole($conn, $data);
        if ($result instanceof Error) {
            return $result;
        }

        // tambahkan detail info act
        $data = SiakadV1::setLogAction($data);

        // insert
        $conn->insert("INSERT INTO $this->table (idrole, idsatker, userid,
                t_updateuser, t_updatetime, t_updateip, t_updateact)
            VALUES (:idrole, :idsatker, :userid,
                :t_updateuser, :t_updatetime, :t_updateip, :t_updateact)", $data);

        return $data;
    }

    public function checkUniqueUserRole($conn, $data)
    {
        // cek unique by idrole, idsatker, userid
        $isExists = $conn->select("SELECT 1 FROM $this->table WHERE idrole = :idrole AND idsatker = :idsatker AND userid = :userid", [
            'idrole' => $data['idrole'],
            'idsatker' => $data['idsatker'],
            'userid' => $data['userid']
        ]);
        if (!empty($isExists)) {
            return new Error('User Role sudah terdaftar', 422);
        }
    }
}
