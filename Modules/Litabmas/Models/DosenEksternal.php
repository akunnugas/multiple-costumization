<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\PerguruanTinggi;
use Modules\HR\Models\JabatanAkademik;

class DosenEksternal extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.dosen_eksternal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_biodata',
        'nip',
        'id_biodata_pengusul',
        'status_usulan',
        'id_perguruan_tinggi_luar'
    ];

    const STATUS_MENUNGGU_PERSETUJUAN = 'MP';
    const STATUS_BERHASIL_DIBUAT = 'BD';
    const STATUS = [
        self::STATUS_MENUNGGU_PERSETUJUAN => 'Menunggu Persetujuan',
        self::STATUS_BERHASIL_DIBUAT => 'Berhasil Dibuatkan Akun',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_biodata' => ['required' => true, 'options' => Biodata::class],
        'nip' => ['required' => true, 'maxlength' => 25],
        'id_biodata_pengusul' => ['options' => Biodata::class],
        'status_usulan' => ['required' => true, 'maxlength' => 2, 'options' => self::STATUS],
        'id_perguruan_tinggi_luar' => ['options' => PerguruanTinggi::class],
    ];

    /**
     * Relasi ke biodata.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }

    /**
     * Relasi ke biodata pengusul.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function biodataPengusul()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata_pengusul');
    }

    /**
     * Get info dosen internal & eksternal.
     * utk eksternal hanya get yg sudah berhasil dibuatkan akunnya.
     *
     * @param bool $isDefaultOpt
     * @param bool $showInternalExternal
     * @param array $exceptIdDosenEksternals
     * @param array $exceptIdBiodatas
     * @return array
     */
    public static function optionWithBiodata(
        bool $isDefaultOpt = false,
        bool $showInternalExternal = false,
        array $exceptIdDosenEksternals = [],
        array $exceptIdBiodatas = [],
        array $onlyIdBiodatas = []
    ) {
        // Convert $onlyIdBiodatas to a string for SQL IN clause
        $onlyIdBiodatasStr = implode(',', array_map('intval', $onlyIdBiodatas));

        // query dosen internal
        $sqlInternal = "SELECT b.id as id_biodata, concat(p.nip, '') as nip, b.nama,
                null as id_dosen_eksternal, null as id_perguruan_tinggi_luar, null as status_usulan
            FROM core.biodata b
            JOIN core.pegawai p ON p.id_biodata = b.id AND p.waktu_dihapus IS NULL
            JOIN hr.jabatan_akademik ja ON ja.id = p.id_jabatan_akademik AND ja.waktu_dihapus IS NULL
                AND ja.jenis_jabatan_akademik = :jenis_jabatan_akademik
            JOIN hr.employee_statuses es ON es.id = p.id_status_pegawai AND es.waktu_dihapus IS NULL
                AND es.is_active = :is_active_employee
            WHERE b.waktu_dihapus IS NULL
                AND p.id IS NOT NULL"
            . (!empty($onlyIdBiodatas) ? " AND b.id IN ($onlyIdBiodatasStr)" : "") . "
            ORDER BY p.nip";

        // query dosen eksternal
        $sqlExternal = "SELECT b.id as id_biodata, concat('', de.nip) as nip, b.nama,
                de.id as id_dosen_eksternal, de.id_perguruan_tinggi_luar, de.status_usulan
            FROM core.biodata b
            JOIN litabmas.dosen_eksternal de ON de.id_biodata = b.id AND de.waktu_dihapus IS NULL
            WHERE b.waktu_dihapus IS NULL AND de.id IS NOT NULL
                AND de.status_usulan = :status_usulan"
            . (!empty($onlyIdBiodatas) ? " AND b.id IN ($onlyIdBiodatasStr)" : "") . "
            ORDER BY de.nip";

        $bindingsInternal = [
            'jenis_jabatan_akademik' => JabatanAkademik::DOSEN_AKADEMIK,
            'is_active_employee' => true,
        ];
        $bindingsExternal = [
            'status_usulan' => self::STATUS_BERHASIL_DIBUAT,
        ];
        $dataInternal = DB::select($sqlInternal, $bindingsInternal);
        $dataExternal = DB::select($sqlExternal, $bindingsExternal);

        // Gabungkan hasil query
        $data = array_merge($dataInternal, $dataExternal);

        if (!$isDefaultOpt) {
            return $data;
        }

        // key = id, value = nip - nama
        $optDefault = [];
        foreach ($data as $item) {
            if (in_array($item->id_dosen_eksternal, $exceptIdDosenEksternals) || in_array($item->id_biodata, $exceptIdBiodatas)) {
                continue;
            }

            $label = $item->nip . ' - ' . $item->nama;
            if ($showInternalExternal) {
                $isInternal = $item->id_perguruan_tinggi_luar === null;
                $label .= $isInternal ? ' (Internal)' : ' (Eksternal)';
            }

            $optDefault[$item->id_biodata] = $label;
        }

        return $optDefault;
    }

    public static function optionDosen(
        $searchTerm = '',
        $limitTerm = 0,
        $idUnit = null,
        $excludeId = []
    ) {
        $showInternal = true;
        //if IdUnit not present then exteral
        if ($idUnit == null) {
            $showInternal = false;
        }

        if ($showInternal) {
            //Init Query
            $query = DB::table('core.biodata as b')
                ->select(
                    'b.id as id_biodata',
                    DB::raw("p.nip as nip"),
                    'b.nama'
                )
                ->join('core.pegawai as p', function ($join) {
                    $join->on('p.id_biodata', '=', 'b.id')
                        ->whereNull('p.waktu_dihapus');
                })
                ->join('hr.jabatan_akademik as ja', function ($join) {
                    $join->on('ja.id', '=', 'p.id_jabatan_akademik')
                        ->whereNull('ja.waktu_dihapus')
                        ->where('ja.jenis_jabatan_akademik', JabatanAkademik::DOSEN_AKADEMIK);
                })
                ->join('hr.employee_statuses as es', function ($join) {
                    $join->on('es.id', '=', 'p.id_status_pegawai')
                        ->whereNull('es.waktu_dihapus')
                        ->where('es.is_active', true);
                })
                ->whereNull('b.waktu_dihapus')
                ->whereNotNull('p.id')
                ->where(function ($q) use ($idUnit) {
                    $q->where('p.id_homebase_dosen', $idUnit)
                        ->orWhere('p.id_unit_kerja', $idUnit);
                }); // Add idUnit filter here using coalesce

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('b.nama', 'ILIKE', '%' . $searchTerm . '%')
                        ->orWhere('p.nip', 'ILIKE', '%' . $searchTerm . '%');
                });
            }
        } else {
            $query = DB::table('core.biodata as b')
                ->select(
                    'b.id as id_biodata',
                    DB::raw("de.nip as nip"),
                    'b.nama',
                    'pt.nama_pt'
                )
                ->join('litabmas.dosen_eksternal as de', function ($join) {
                    $join->on('de.id_biodata', '=', 'b.id')
                        ->whereNull('de.waktu_dihapus')
                        ->where('de.status_usulan', self::STATUS_BERHASIL_DIBUAT);
                })
                ->join('core.perguruan_tinggi as pt', function ($join) {
                    $join->on('pt.id', '=', 'de.id_perguruan_tinggi_luar')
                        ->whereNull('pt.waktu_dihapus');
                })
                ->whereNull('b.waktu_dihapus')
                ->whereNotNull('de.id');


            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('b.nama', 'ILIKE', '%' . $searchTerm . '%')
                        ->orWhere('de.nip', 'ILIKE', '%' . $searchTerm . '%');
                });
            }
        }

        // Exclude ID
        if (!empty($excludeId)) {
            $query->whereNotIn('b.id', $excludeId);
        }

        // Set limits if specified
        if ($limitTerm > 0) {
            $query->limit($limitTerm);
        }

        // order by nip
        $query->orderBy('nip');

        $data = $query->get();

        $mapping = [];
        foreach ($data as $item) {
            $label = $item->nip . ' - ' . $item->nama;
            if (!$showInternal) {
                $label .= ' (' . $item->nama_pt . ')';
            }

            // $mapping[] = [
            //     'value' => $item->id_biodata,
            //     'label' => $label
            // ];
            $mapping[$item->id_biodata] = $label;
        }

        return $mapping;
    }

}
