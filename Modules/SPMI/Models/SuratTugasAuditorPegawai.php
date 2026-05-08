<?php

namespace Modules\SPMI\Models;

use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\Role;

class SuratTugasAuditorPegawai extends IndonesianModel
{
    const POSITION_LEAD = 'L';
    const POSITION_MEMBER = 'M';
    const POSITION_AUDITEE = 'A';
    const POSITION_MEMBER_AUDITEE = 'B';
    const POSITIONS = [
        self::POSITION_LEAD => 'Ketua Auditor',
        self::POSITION_MEMBER => 'Anggota Auditor',
        self::POSITION_AUDITEE => 'Ketua Auditee',
        self::POSITION_MEMBER_AUDITEE => 'Anggota Auditee',
    ];

    const URUTAN_POSISI = [self::POSITION_AUDITEE, self::POSITION_MEMBER_AUDITEE, self::POSITION_LEAD, self::POSITION_MEMBER];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.surat_tugas_auditor_pegawai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_surat_tugas_auditor',
        'id_personil',
        'id_unit',
        'posisi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_surat_tugas_auditor' => ['required' => true, 'options' => SuratTugasAuditor::class], // ST Auditor
        'id_personil' => ['required' => true, 'options' => Biodata::class], // Data Petugas Auditor
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], // Program Studi
        'posisi' => ['required' => true, 'maxlength' => 1], // Posisi Petugas Auditor (L: Ketua, M: Anggota)
    ];

    public function stAuditor()
    {
        return $this->belongsTo(SuratTugasAuditor::class, 'id_surat_tugas_auditor', 'id');
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_personil');
    }

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'id_unit');
    }

    public static function optionProdiBySuratTugas($idUser, $withDefaultUnit = true)
    {
        $biodata = Biodata::where('id_user', $idUser)->first();

        $userRole = auth()->user()->kode_role;

        if (!$biodata && !SessionManager::isInternalRole() && in_array($userRole, [Role::ROLE_AUDITOR, Role::ROLE_KAPRODI])) {
            return [];
        }

        $sql =
            "SELECT
                u.id,
                CONCAT(j.kode_jenjang, ' - ', u.nama_unit) nama_unit,
                u.info_left
            FROM core.unit_kerja u
            JOIN core.jenjang_pendidikan j ON j.id = u.id_jenjang_pendidikan
                AND j.waktu_dihapus IS NULL
            JOIN spmi.surat_tugas_auditor_pegawai stp ON stp.id_unit = u.id
                AND stp.id_personil = :id_personil
            WHERE u.waktu_dihapus IS NULL
                AND u.apakah_aktif = true
            GROUP BY stp.id_unit, u.id, j.id
            ORDER BY u.info_left ASC";

        if (in_array($userRole, [Role::ROLE_AUDITOR, Role::ROLE_KAPRODI])) {
            $data = DB::select($sql, ['id_personil' => $biodata->id]);
        }

        $result = [];
        if ($withDefaultUnit) {
            $unitAsal = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isRawData: true, isOnlyActive: true);
            $merged = array_merge($unitAsal, $data ?? []);

            // Order by info_left
            usort($merged, function ($a, $b) {
                $a = (array) $a;
                $b = (array) $b;
                return $a['info_left'] <=> $b['info_left'];
            });

            foreach ($merged as $item) {
                $item = (array) $item;
                $result[$item['id']] = $item['nama_unit'];
            }
        }

        return $result;
    }
}
