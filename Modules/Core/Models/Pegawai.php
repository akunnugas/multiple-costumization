<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\HR\Models\JabatanAkademik;
use Modules\HR\Models\EmployeeStatus;
use Modules\HR\Models\FunctionalPosition;
use Modules\HR\Models\StructuralPosition;
use Modules\HR\Models\WorkRelation;

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
        'id_status_pegawai',
        'id_hubungan_kerja',
        'nip_pns',
        'email_kampus',
        'akun_sidik_jari',
        'id_jabatan_struktural_atasan',
        'id_jabatan_akademik',
        'id_jabatan_fungsional',
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
        'id_status_pegawai' => ['required' => false, 'options' => EmployeeStatus::class], // Status Pegawai
        'id_hubungan_kerja' => ['required' => false, 'options' => WorkRelation::class], // Hubungan Kerja
        'nip_pns' => ['maxlength' => 25], // NIP PNS
        // 'email_kampus' => ['type' => 'email'], // Email Kampus //NOTE: DRAX-4119: Validasi Bypass karena data pegawai siakad v1 beragam
        'akun_sidik_jari' => ['maxlength' => 25], // NO. Akun Sidik Jari
        'id_jabatan_struktural_atasan' => ['required' => false, 'options' => StructuralPosition::class], // Jabatan Struktural Atasan
        'id_jabatan_akademik' => ['required' => false, 'options' => JabatanAkademik::class],             // Jabatan Akademik
        'id_jabatan_fungsional' => ['required' => false, 'options' => FunctionalPosition::class],        // Jabatan Fungsional
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
                JOIN hr.employee_statuses s ON s.id = p.id_status_pegawai
                    AND s.waktu_dihapus IS NULL
                WHERE b.waktu_dihapus IS NULL
                    AND s.is_active = true
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

    /**
     * Daftar dosen yang berstatus aktif.
     *
     * @param string $orderBy
     * @return array
     */
    public static function optionDosenAktif(string $orderBy = self::OPTION_ORDER)
    {
        $sql = "SELECT b.id as id_biodata, p.nip, b.nama
            FROM core.biodata b
            JOIN core.pegawai p ON p.id_biodata = b.id AND p.waktu_dihapus IS NULL
            JOIN hr.jabatan_akademik ja ON ja.id = p.id_jabatan_akademik AND ja.waktu_dihapus IS NULL
                AND ja.jenis_jabatan_akademik = :jenis_jabatan_akademik
            JOIN hr.employee_statuses es ON es.id = p.id_status_pegawai AND es.waktu_dihapus IS NULL
                AND es.is_active = :is_active_employee
            WHERE b.waktu_dihapus IS NULL
                AND p.id IS NOT NULL
            ORDER BY p." . $orderBy;

        $bindingsInternal = [
            'jenis_jabatan_akademik' => JabatanAkademik::DOSEN_AKADEMIK,
            'is_active_employee' => true,
        ];
        $dataInternal = DB::select($sql, $bindingsInternal);

        $result = [];
        foreach ($dataInternal as $value) {
            $result[$value->id_biodata] = $value->nip . ' - ' . $value->nama;
        }

        return $result;
    }

    // person
    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }
}
