<?php

namespace Modules\Gate\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;

class Role extends IndonesianModel
{
    use ClearCache, SoftDeletes;

    const OPTION_COLUMN = 'nama_role';
    const OPTION_ORDER = 'kode_role';

    const RULES = [
        'nama_role' => ['required' => true, 'maxlength' => 255, 'unique' => true],
        'kode_role' => ['required' => true, 'maxlength' => 100, 'unique' => true],
    ];

    const ROLE_ADMINPT = 'admin_perguruan_tinggi';
    const ROLE_ADMINDMS = 'addms';
    const ROLE_DOSEN = 'user_dosen';
    const ROLE_DOSEN_EKSTERNAL = 'user_dosen_eksternal';
    const ROLE_DEKAN = 'user_dekan';
    const ROLE_REKTOR = 'user_rektor';
    const ROLE_KAPRODI = 'user_kepala_unit';
    const ROLE_WAKIL_REKTOR_1 = 'user_wakil_rektor_1';
    const ROLE_WAKIL_REKTOR_2 = 'user_wakil_rektor_2';
    const ROLE_WAKIL_REKTOR_3 = 'user_wakil_rektor_3';
    const ROLE_WAKIL_DEKAN_1 = 'user_wakil_dekan_1';
    const ROLE_WAKIL_DEKAN_2 = 'user_wakil_dekan_2';
    const ROLE_WAKIL_DEKAN_3 = 'user_wakil_dekan_3';

    // Role SPMI
    const ROLE_AUDITEE = 'user_auditee';
    const ROLE_AUDITOR = 'user_auditor';
    const ROLE_ADMIN_PENJAMINAN_MUTU = 'admin_penjaminan_mutu';
    const ROLE_TIM_PENGISI_DATA = 'user_tim_penjaminan_mutu';

    // Role Litabmas
    const ROLE_LITABMAS_ADMIN_LPPM = 'lm_admin_lppm';
    const ROLE_LITABMAS_KETUA_LPPM = 'lm_ketua_lppm';

    // Role Kerjasama
    const ROLE_ADMIN_KERJASAMA = 'admin_kerjasama';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gate.role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_role',
        'kode_role',
        'apakah_statis',
        'ref_key_siakad'
    ];

    public static function mapRoleInternalV1($role = null)
    {
        $map = [
            'admpt' => self::ROLE_ADMINPT,

            // Role auniv di v1 sama dengan admin pt di v2
            'auniv' => self::ROLE_ADMINPT,
            self::ROLE_ADMINDMS => self::ROLE_ADMINDMS,
            'admak' => 'admin_akademik',
            'admms' => 'admin_akademik_kemahasiswaan',
            'akred' => 'admin_akreditasi',
            'adedl' => 'admin_edlink',
            'oprpg' => 'admin_kepegawaian',
            'adkeu' => 'admin_keuangan_akademik',
            'oprtr' => 'admin_module',
            'adpmb' => 'admin_pmb',
            'profe' => 'admin_profeeder',
            'adkrl' => 'admin_pusat_karir',
            'adfak' => 'admin_unit_fakultas',
            'adpas' => 'admin_unit_pascasarjana',
            'adpsc' => 'admin_unit_pascasarjana',
            'adpro' => 'admin_unit_prodi',
            'PTU' => 'admin_unit_prodi_fakultas',
            'asrpl' => 'user_asesor_rpl',
            'dek' => 'user_dekan',
            'dekan' => 'user_dekan',
            'doeks' => 'user_dosen_eksternal',
            'dosen' => 'user_dosen',
            'kba' => 'user_kabag_akademik',
            'kbau' => 'user_kabag_bau',
            'kspmi' => 'user_kabag_spmi',
            'ksbau' => 'user_kasubag_bau',
            'sspmi' => 'user_kasubag_spmi',
            'KA' => 'user_kepala_unit',
            'mhs' => 'user_mahasiswa',
            'mbkmi' => 'user_mahasiswa_kampus_merdeka',
            'ortu' => 'user_orang_tua_mahasiswa',
            'peg' => 'user_pegawai',
            // Statik user rektor
            'rek' => 'user_rektor',
            'Rktor' => 'user_rektor',
            'vrps' => 'user_validator_rps',
            'wdek1' => 'user_wakil_dekan_1',
            'wdek2' => 'user_wakil_dekan_2',
            'wdek3' => 'user_wakil_dekan_3',
            'wrek1' => 'user_wakil_rektor_1',
            'wrek2' => 'user_wakil_rektor_2',
            'wrek3' => 'user_wakil_rektor_3',
            'ATR' => 'user_auditor',
            'TPD' => 'user_tim_penjaminan_mutu',
            'ADMPJ' => 'admin_penjaminan_mutu',
            'ADT' => self::ROLE_AUDITEE,
            // litabmas
            'LALPM' => self::ROLE_LITABMAS_ADMIN_LPPM,
            'LKLPM' => self::ROLE_LITABMAS_KETUA_LPPM,
            // kerjasama
            'ADMKS' => self::ROLE_ADMIN_KERJASAMA
        ];

        return empty($role) ? $map : ($map[$role] ?? null);
    }

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        RoleCache::destroy($model->id);
    }
}
