<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Traits\ClearCache;
use Modules\PMB\Models\Cache\SebaranProdiCache;

class SebaranProdi extends IndonesianModel
{
    use ClearCache, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.sebaran_prodi';

    protected $fillable = [
        'id_periode_pendaftaran',
        'id_unit_kerja',
        'daya_tampung',
        'nilai_minimal',
        'prefix_nim',
        'digit_nim_maksimal',
    ];

    const OPTION_COLUMN = 'id';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_periode_pendaftaran' => ['required' => true, 'options' => PeriodePendaftaran::class], // Peruide Pendaftaran
        'id_unit_kerja' =>  ['required' => true, 'options' => UnitKerja::class], // Program Studi
        'daya_tampung' => ['type' => 'numeric', 'maxlength' => 1000], // Kuota Mahasiswa Diterima
        'nilai_minimal' => ['type' => 'numeric', 'maxlength' => 1000], // Nilai Minimum Kelulusan Seleksi Pendaftaran
        'prefix_nim' => ['maxlength' => 100], // Prefix NIM
        'digit_nim_maksimal' => ['type' => 'numeric', 'maxlength' => 100], // Digit Maksimal NIM
    ];

    /**
     * Relation to registration period.
     */
    public function periodePendaftaran()
    {
        return $this->belongsTo(PeriodePendaftaran::class);
    }

    /**
     * Relation to program option mapping
     */
    public function sebaranPilihan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SebaranPilihan::class, 'id_sebaran_prodi', 'id');
    }

    /**
     * Relation to program institution mapping
     */
    public function sebaranAsalPendaftar(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SebaranAsalPendaftar::class, 'id_sebaran_prodi', 'id');
    }

    private static function clearCache($model)
    {
        SebaranProdiCache::destroy();
    }

    /**
     * Get pilihan program studi berdasarkan periode pendaftaran untuk select option.
     *
     * @param int $registrationPeriodId
     * @param int|null $optionNumber
     * @param int|null $institutionTypeId
     * @return array
     * @old: getProdi() in spmb/models/m_sebaranprodi.php
     */
    public static function optionStudyPrograms(
        int $registrationPeriodId,
        int $optionNumber = null,
        int $institutionTypeId = null,
        bool $rawResult = false
    ) {
        $sql = "select distinct pd.id as key, d.kode_jenjang||' - '||o.nama_unit as value, pom.pilihan as option_number
            from pmb.program_distributions pd
            join core.unit_kerja o on o.id = pd.organization_id and o.waktu_dihapus is null
            join core.jenjang_pendidikan d on d.id = o.id_jenjang_pendidikan and d.waktu_dihapus is null
            left join pmb.sebaran_asal_pendaftar pom on pom.id_sebaran_prodi = pd.id
                and pom.waktu_dihapus is null
            left join pmb.sebaran_pilihan pim on pim.id_sebaran_prodi = pd.id
                and pim.waktu_dihapus is null
            where pd.waktu_dihapus is null
                and o.apakah_aktif_pmb = true
                and pd.id_periode_pendaftaran = :registrationPeriodId";

        $bindings = ['registrationPeriodId' => $registrationPeriodId];

        if (!empty($optionNumber)) {
            $sql .= " and pom.pilihan = :optionNumber";
            $bindings['optionNumber'] = $optionNumber;
        }

        if (!empty($institutionTypeId)) {
            $sql .= " and pim.id_jenis_institusi = :institutionTypeId";
            $bindings['institutionTypeId'] = $institutionTypeId;
        }

        $sql .= " order by value";
        $resultQuery = DB::connection()->select($sql, $bindings);

        if ($rawResult) {
            return $resultQuery;
        }

        // make to array option
        $options = [];
        foreach ($resultQuery as $val) {
            $options[$val->key] = $val->value;
        }

        return $options;
    }
}
