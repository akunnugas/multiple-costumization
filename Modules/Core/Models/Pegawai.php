<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;

class Pegawai extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.pegawai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_biodata',
        'nip',
        'nidn',
        'id_unit_kerja',
        'nip_pns',
        'email_kampus',
        'akun_sidik_jari',
        'ref_key_siakad',
        'id_homebase_dosen',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // Data Pengguna
        'nip' => ['required' => true, 'maxlength' => 25, 'unique' => true], // Kode Pegawai
        'id_unit_kerja' => ['options' => UnitKerja::class], // Organisasi
        'nip_pns' => ['maxlength' => 25], // NIP PNS
        // 'email_kampus' => ['type' => 'email'], // Email Kampus //NOTE: DRAX-4119: Validasi Bypass karena data pegawai siakad v1 beragam
        'akun_sidik_jari' => ['maxlength' => 25], // NO. Akun Sidik Jari
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'id_biodata asc';
    const OPTION_COLUMN = 'id_biodata';

    public static function optionWithPersons($isDefaultOpt = false, $order = self::OPTION_ORDER) {
        $sql = "SELECT b.id, b.id_user, p.id as id_pegawai, p.nip, coalesce(b.gelar_depan, '') || ' ' || b.nama || ', ' || coalesce(b.gelar_belakang, '') nama
                FROM core.biodata b
                JOIN core.pegawai p ON p.ref_key_siakad = b.ref_key_pegawai AND p.waktu_dihapus IS NULL
                WHERE b.waktu_dihapus IS NULL
                ORDER BY p." . $order;

        $data = DB::select($sql);
        if ($isDefaultOpt) {
            $optDefault = [];
            foreach ($data as $key => $value) {
                $optDefault[$value->id] = $value->nip . ' - ' . $value->nama;
            }
            return $optDefault;
        }

        return (array) $data;
    }

    // person
    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }
}
