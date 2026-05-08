<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Wilayah;

class Mitra extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'nama_mitra asc';
    const OPTION_COLUMN = 'nama_mitra';


    const LEVEL_LOKAL = 'L';
    const LEVEL_REGIONAL = 'R';
    const LEVEL_NASIONAL = 'N';
    const LEVEL_INTERNASIONAL = 'I';
    const LEVELS = [
        self::LEVEL_LOKAL => 'Lokal',
        self::LEVEL_REGIONAL => 'Regional',
        self::LEVEL_NASIONAL => 'Nasional',
        self::LEVEL_INTERNASIONAL => 'Internasional',
    ];

    const MITRA_PERGURUAN_TINGGI = 'PT';
    const MITRA_NON_PERGURUAN_TINGGI = 'INS';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.mitra';

    protected $fillable = [
        'nama_mitra',
        'jenis_mitra',
        'tingkat_mitra',
        'id_kriteria_mitra',
        'website',
        'id_negara',
        'id_provinsi',
        'id_kota',
        'id_kecamatan',
        'alamat',
        'telepon',
        'npwp_mitra',
        'kode_mitra',
        'kode_pos',
        'email'
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_mitra' => ['required' => true, 'maxlength' => 255],
        'jenis_mitra' => ['required' => true, 'control' => 'radio'],
        'kode_mitra' => ['maxlength' => 255],
        'npwp_mitra' => ['maxlength' => 255],
        'id_kriteria_mitra' => ['required' => true, 'options' => KriteriaMitra::class, 'variant' => 'search'],
        'tingkat_mitra' => ['required' => true, 'control' => 'radio', 'inline' => false, 'options' => self::LEVELS],
        'id_negara' => ['options' => Wilayah::class, 'variant' => 'search'],
        'id_provinsi' => ['options' => Wilayah::class, 'variant' => 'search'],
        'id_kota' => ['options' => Wilayah::class, 'variant' => 'search'],
        'id_kecamatan' => ['options' => Wilayah::class, 'variant' => 'search'],
        'kode_pos' => ['validation' => 'numeric|digits_between:1,10'],
        'alamat' => ['control' => 'textarea'],
        'telepon' => ['maxlength' => 20, 'numeric' => true],
        'website' => ['maxlength' => 255],
        'email' => ['maxlength' => 255, 'type' => 'email'],
        'link_googlemap' => []
    ];

    public function kontak(): HasMany
    {
        return $this->hasMany(Kontak::class);
    }


    /**
     * Count mitra 
     *
     * @return array
     */

    public static function countByLingkupMitra(): array
    {
        $results = self::query()
            ->select('tingkat_mitra', DB::raw('COUNT(DISTINCT mitra.id) as jumlah_mitra'))
            ->join('kerjasama.kerjasama', 'kerjasama.id_mitra', '=', 'mitra.id')
            ->whereIn('tingkat_mitra', [self::LEVEL_LOKAL, self::LEVEL_REGIONAL, self::LEVEL_NASIONAL, self::LEVEL_INTERNASIONAL])
            ->whereNull('mitra.waktu_dihapus')
            ->whereNull('kerjasama.waktu_dihapus')
            ->groupBy('tingkat_mitra')
            ->get()
            ->toArray();

        $mappedResults = [];
        foreach ($results as $result) {
            $label = self::LEVELS[$result['tingkat_mitra']] ?? 'Unknown';
            $mappedResults[] = [
                'tingkat_mitra' => $label,
                'jumlah_mitra' => $result['jumlah_mitra']
            ];
        }

        return $mappedResults;
    }
    public static function countByJenisMitra(): array
    {
        $results = self::query()
            ->select('jenis_mitra', DB::raw('COUNT(DISTINCT mitra.id) as jumlah_mitra'))
            ->join('kerjasama.kerjasama', 'kerjasama.id_mitra', '=', 'mitra.id')
            ->whereIn('jenis_mitra', [self::MITRA_PERGURUAN_TINGGI, self::MITRA_NON_PERGURUAN_TINGGI])
            ->whereNull('mitra.waktu_dihapus')
            ->whereNull('kerjasama.waktu_dihapus')
            ->groupBy('jenis_mitra')
            ->get()
            ->toArray();

        $mappedResults = [];
        foreach ($results as $result) {
            $label = $result['jenis_mitra'] === self::MITRA_PERGURUAN_TINGGI ? 'Perguruan Tinggi' : 'Non Perguruan Tinggi';
            $mappedResults[] = [
                'jenis_mitra' => $label,
                'jumlah_mitra' => $result['jumlah_mitra']
            ];
        }
        return $mappedResults;
    }


    public static function countByKriteriaMitra(): array
    {
        $results = DB::select('
            SELECT
                km.klasifikasi_mitra,
                COUNT(m.id) as jumlah_mitra
            FROM
                kerjasama.kriteria_mitra km
            LEFT JOIN
                kerjasama.mitra m ON km.id = m.id_kriteria_mitra
            WHERE
                km.waktu_dihapus IS NULL 
                AND (m.waktu_dihapus IS NULL OR m.id IS NULL)
            GROUP BY
                km.klasifikasi_mitra
            HAVING
                COUNT(m.id) > 0 
            ORDER BY
                jumlah_mitra DESC
            LIMIT 5;
        ');

        $mappedResults = [];
        foreach ($results as $result) {
            $mappedResults[] = [
                'klasifikasi_mitra' => $result->klasifikasi_mitra,
                'jumlah_mitra' => $result->jumlah_mitra
            ];
        }

        return $mappedResults;
    }


    public static function countByProvinsi(): array
    {
        $sql = "
            SELECT 
                w.nama_wilayah AS provinsi, 
                COUNT(m.id) AS jumlah_mitra
            FROM 
                kerjasama.mitra m
            JOIN 
                core.wilayah w ON m.id_provinsi = w.id
            WHERE 
                w.level_wilayah = '1' -- No parameter binding needed here
            GROUP BY 
                w.nama_wilayah
            ORDER BY 
                jumlah_mitra DESC           
            LIMIT 5     
        ";

        $results = DB::select($sql);

        $mappedResults = [];
        foreach ($results as $result) {
            $mappedResults[] = [
                'provinsi' => $result->provinsi,
                'jumlah_mitra' => $result->jumlah_mitra
            ];
        }

        return $mappedResults;
    }
}
