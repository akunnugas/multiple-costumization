<?php

namespace Modules\SPMI\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class AuditPeriode extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'tahun_audit desc';
    const OPTION_COLUMN = 'tahun_audit';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.audit_periode';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tahun_audit',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'tahun_audit' => ['required' => true, 'maxlength' => 4, 'unique' => true], // Tahun Audit
        'tanggal_mulai' => ['type' => 'date'], // Tanggal Mulai
        'tanggal_selesai' => ['type' => 'date'], // Tanggal Selesai
    ];

    public function indikatorBobot()
    {
        return $this->hasMany(IndikatorBobot::class, 'id_audit_periode', 'id');
    }

    public static function countIndikatorBobot($idPeriodeAudit, $idUnitKerja, $idPenilaianPanduan)
    {
        if ($idPenilaianPanduan == 'null_filter') {
            return 0;
        }
        $apakahDataDefault = PenilaianPanduan::where('id', $idPenilaianPanduan)
            ->value('apakah_data_default');
        return IndikatorBobot::where('id_audit_periode', $idPeriodeAudit)
            ->where('id_unit', $idUnitKerja)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->when(!$apakahDataDefault, function ($query) {
                $query->where('jenis_indikator_bobot', IndikatorBobot::TYPE_IKT);
            })
            ->sum('persentase');
    }

    public static function findNowYearPeriod()
    {
        return self::where('tahun_audit', now()->year)->first();
    }

    public static function findByYear(int $year)
    {
        return self::where('tahun_audit', $year)->first();
    }

    /**
     * Attribut untuk text periode
     */
    protected function periodText(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!isset($this->tanggal_mulai, $this->tanggal_selesai)) {
                    return 'Periode belum diatur';
                }

                $startDate = Carbon::parse($this->tanggal_mulai);
                $endDate = Carbon::parse($this->tanggal_selesai);

                if ($startDate->year == $endDate->year) {
                    return $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M Y');
                }

                return $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y');
            }
        );
    }
}
