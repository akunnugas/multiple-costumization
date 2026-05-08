<?php

namespace Modules\Core\Models\Shared;

use Modules\Core\Extensions\Models\Abstracts\CacheModel;

class KlienCache extends CacheModel
{
    const KEY = 'klien';
    const SHARED = true;

    /**
     * Get a record from database.
     */
    public static function findDefault($id)
    {
        $clientURL = KlienURL::where('url', $id)->first(['id_klien', 'url_siakad']);
        if (empty($clientURL)) {
            return false;
        }

        $client = Klien::select([
            'klien.id', 'klien.nama_klien', 'klien.kode_klien', 'klien.kode_dikti', 'klien_config.timezone', 'klien_config.nama_db', 'klien_config.username_db', 'klien_config.password_db',
            'klien_config.nama_db_v1', 'klien_config.username_db_v1', 'klien_config.password_db_v1',
        ])
            ->join('klien_config', 'klien_config.id_klien', '=', 'klien.id')
            ->find($clientURL->id_klien);
        if (empty($client)) {
            return false;
        }

        return $client->toArray() + ['url_siakad' => $clientURL->url_siakad];
    }

    /**
     * Clear cache by client.
     */
    public static function destroyByClient($client)
    {
        $urls = KlienURL::where('id_klien', $client->id)->pluck('url');
        foreach ($urls as $url) {
            static::destroy($url);
        }
    }
}
