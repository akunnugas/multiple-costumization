<?php

namespace Modules\Gate\Models;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\CacheModel;

// clear cache ada pada service
class RoleAksesCache extends CacheModel
{
    const KEY = 'permission';

    /**
     * Get a record from database.
     */
    public static function findDefault($id)
    {
        // ambil hak akses utama
        $sql = "select r.id_modul, r.segmen_url, p.bisa_get, p.bisa_post, p.bisa_put, p.bisa_delete
                from gate.resource r
                join gate.role_akses p on p.id_resource = r.id and p.id_role = ?
                where r.waktu_dihapus IS NULL";
        $rows = DB::select($sql, [$id]);

        $data = [];
        foreach ($rows as $row) {
            $permission = empty($row->bisa_get) ? '0' : '1';
            $permission .= empty($row->bisa_post) ? '0' : '1';
            $permission .= empty($row->bisa_put) ? '0' : '1';
            $permission .= empty($row->bisa_delete) ? '0' : '1';

            if ($permission == '0000') {
                continue;
            }

            $data[$row->id_modul][$row->segmen_url]['_'] = $permission;
        }

        // ambil hak akses custom
        $sql = "select r.id_modul, r.segmen_url, c.kode_aksi
                from gate.resource r
                join gate.resource_aksi c on c.id_resource = r.id and c.waktu_dihapus is null
                join gate.role_akses_aksi cp on cp.id_resource_aksi = c.id and cp.id_role = ?
                where r.waktu_dihapus is null";
        $rows = DB::select($sql, [$id]);

        foreach ($rows as $row) {
            $data[$row->id_modul][$row->segmen_url][$row->kode_aksi] = true;
        }

        return $data;
    }
}
