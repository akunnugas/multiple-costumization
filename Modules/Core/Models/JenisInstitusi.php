<?php

namespace Modules\Core\Models;

use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisInstitusi extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jenis_institusi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_jenis_institusi',
        'nama_jenis_institusi',
        'id_jenjang_pendidikan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_jenis_institusi' => ['required' => true, 'maxlength' => 255], // Kode Jenis Institusi
        'nama_jenis_institusi' => ['required' => true, 'maxlength' => 255], // Nama Jenis Institusi
        'id_jenjang_pendidikan' => ['required' => true, 'options' => JenjangPendidikan::class], // Jenjang Pendidikan
    ];

    /**
     * Get jenjang yang non perguruan tinggi.
     *
     * @return array
     * @old: comboNonPerguruanTinggi() in models/m_jenisinstitusi.php
     */
    public static function optionNonUniversity(int $degreeId = null)
    {
        $query = "select it.id as key, it.nama_jenis_institusi as value
            from core.jenis_institusi it
            join core.jenjang_pendidikan d on d.id = it.id_jenjang_pendidikan
            where d.apakah_pt = false";

        if ($degreeId) {
            $query .= " and it.id_jenjang_pendidikan = :degree_id";
            $binds['degree_id'] = $degreeId;
        }

        $query .= " order by it.nama_jenis_institusi";

        $result = DB::select($query, $binds ?? []);
        return array_column($result, 'value', 'key');
    }
}
