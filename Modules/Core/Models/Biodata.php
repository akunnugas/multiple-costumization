<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Suku;
use Modules\Core\Models\Pekerjaan;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\Agama;
use Modules\Gate\Models\User;
use Modules\HR\Models\JabatanAkademik;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\PMB\Models\Pendaftar;

class Biodata extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.biodata';

    const OPTION_ORDER = 'nama';
    const OPTION_COLUMN = 'nama';

    protected $fillable = [
        'id_user',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'id_agama',
        'id_suku',
        'id_negara',
        'id_provinsi',
        'id_kota',
        'id_kecamatan',
        'desa',
        'dusun',
        'alamat',
        'rt',
        'rw',
        'kode_pos',
        'nik',
        'no_kk',
        'email',
        'telepon',
        'npsn',
        'no_kps',
        'waktu_validasi',
        'berat',
        'tinggi',
        'no_paspor',
        'id_pekerjaan',
        'nama_instansi',
        'ukuran_seragam',
        'nama_ponpes',
        'alamat_ponpes',
        'lama_ponpes',
        'ref_key_pegawai',
        'ref_key_mahasiswa'
    ];

    const UKURAN_SERAGAM = [
        'XXS' => 'XXS',
        'XS' => 'XS',
        'S' => 'S',
        'M' => 'M',
        'L' => 'L',
        'XL' => 'XL',
        'XXL' => 'XXL',
        '3XL' => '3XL',
        '4XL' => '4XL',
        '5XL' => '5XL',
    ];

    const JENIS_KELAMIN = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_user' => ['options' => User::class],
        'nama' => ['required' => true, 'maxlength' => 100],
        'gelar_depan' => ['maxlength' => 100],
        'gelar_belakang' => ['maxlength' => 100],
        'tanggal_lahir' => ['type' => 'date', 'validation' => 'date_format:Y-m-d'],
        'tempat_lahir' => ['maxlength' => 100],
        'jenis_kelamin' => ['maxlength' => 1, 'options' => self::JENIS_KELAMIN],
        'id_agama' => ['required' => false, 'options' => Agama::class],
        'id_suku' => ['options' => Suku::class],
        'id_negara' => ['options' => Wilayah::class],
        'id_provinsi' => ['options' => Wilayah::class],
        'id_kota' => ['options' => Wilayah::class],
        'id_kecamatan' => ['options' => Wilayah::class],
        'desa' => ['maxlength' => 100],
        'dusun' => ['maxlength' => 100],
        'alamat' => ['maxlength' => 255],
        'rt' => ['required' => false, 'maxlength' => 3],
        'rw' => ['required' => false, 'maxlength' => 3],
        'kode_pos' => ['maxlength' => 20],
        'nik' => ['maxlength' => 30],
        'no_kk' => ['maxlength' => 20],
        'email' => ['type' => 'email', 'maxlength' => 100],
        'telepon' => ['maxlength' => 20],
        'npsn' => ['maxlength' => 20],
        'no_kps' => ['maxlength' => 50],
        'waktu_validasi' => ['validation' => 'date_format:Y-m-d H:i:sO'],
        'berat' => ['type' => 'numeric', 'maxlength' => 1000],
        'tinggi' => ['type' => 'numeric', 'maxlength' => 1000],
        'no_paspor' => ['maxlength' => 50],
        'id_pekerjaan' => ['options' => Pekerjaan::class],
        'nama_instansi' => ['maxlength' => 100],
        'ukuran_seragam' => ['maxlength' => 10, 'options' => self::UKURAN_SERAGAM],
        'nama_ponpes' => ['maxlength' => 100],
        'alamat_ponpes' => ['maxlength' => 255],
        'lama_ponpes' => ['type' => 'numeric', 'maxlength' => 50],
    ];

    public function pendaftar()
    {
        return $this->hasOne(Pendaftar::class);
    }

    public static function searchPengisianDataDosen($search = '', $limit = 20)
    {
        $sql = "SELECT b.id, b.nama, b.gelar_depan, b.gelar_belakang
                FROM core.biodata b
                JOIN core.pegawai p ON p.ref_key_siakad = b.ref_key_pegawai AND p.waktu_dihapus IS NULL
                JOIN hr.employee_statuses es ON es.id = p.id_status_pegawai AND es.waktu_dihapus IS NULL
                WHERE b.waktu_dihapus IS NULL
                    AND p.id IS NOT NULL";

        if (!empty($search)) {
            $sql .= " AND (b.nama ILIKE :search OR b.gelar_depan ILIKE :search OR b.gelar_belakang ILIKE :search OR p.nip ILIKE :search)";
        }

        $sql .= " ORDER BY b.nama ASC LIMIT :limit";

        $bindings = ['limit' => $limit];
        if (!empty($search)) {
            $bindings['search'] = '%' . $search . '%';
        }

        $data = DB::select($sql, $bindings);

        $list = [];
        foreach ($data as $biodata) {
            $nama = ($biodata->gelar_depan ? $biodata->gelar_depan . ' ' : '') . $biodata->nama . ($biodata->gelar_belakang ? ', ' . $biodata->gelar_belakang : '');
            $nama = preg_replace('/\s+/', ' ', $nama);
            $nama = trim($nama);
            $list[] = [
                'value' => $biodata->id,
                'label' => $nama
            ];
        }

        return $list;
    }

    public static function getPengisianDataDosenNameById($id)
    {
        if (empty($id)) {
            return null;
        }

        // HANDLING DATA LAMA
        if (!is_numeric($id)) {
            return $id;
        }

        $sql = "SELECT b.id, b.nama, b.gelar_depan, b.gelar_belakang
                FROM core.biodata b
                JOIN core.pegawai p ON p.ref_key_siakad = b.ref_key_pegawai AND p.waktu_dihapus IS NULL
                JOIN hr.employee_statuses es ON es.id = p.id_status_pegawai AND es.waktu_dihapus IS NULL
                WHERE b.waktu_dihapus IS NULL
                    AND p.id IS NOT NULL
                    AND b.id = :id";

        $data = DB::select($sql, ['id' => $id]);

        if (!empty($data)) {
            $biodata = $data[0];
            $nama = ($biodata->gelar_depan ? $biodata->gelar_depan . ' ' : '') . $biodata->nama . ($biodata->gelar_belakang ? ', ' . $biodata->gelar_belakang : '');
            $nama = preg_replace('/\s+/', ' ', $nama);
            return trim($nama);
        }

        return $id; // Return ID if name not found
    }

    public static function getPengisianDataDosenNamesByIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        // Filter only numeric IDs
        $numericIds = array_filter($ids, 'is_numeric');

        if (empty($numericIds)) {
            return [];
        }

        $sql = "SELECT b.id, b.nama, b.gelar_depan, b.gelar_belakang
                FROM core.biodata b
                JOIN core.pegawai p ON p.ref_key_siakad = b.ref_key_pegawai AND p.waktu_dihapus IS NULL
                JOIN hr.employee_statuses es ON es.id = p.id_status_pegawai AND es.waktu_dihapus IS NULL
                WHERE b.waktu_dihapus IS NULL
                    AND p.id IS NOT NULL
                    AND b.id = ANY(:ids)";

        $data = DB::select($sql, ['ids' => '{' . implode(',', $numericIds) . '}']);

        $result = [];
        foreach ($data as $biodata) {
            $nama = ($biodata->gelar_depan ? $biodata->gelar_depan . ' ' : '') . $biodata->nama . ($biodata->gelar_belakang ? ', ' . $biodata->gelar_belakang : '');
            $nama = preg_replace('/\s+/', ' ', $nama);
            $result[$biodata->id] = trim($nama);
        }

        return $result;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public static function getDosenInternal($id_unit = null, $is_option = false, $ignoredIdBiodata = [])
    {
        $sql = "SELECT b.id as id_biodata, concat(p.nip, '') as nip, b.nama
            FROM core.biodata b
            JOIN core.pegawai p ON p.ref_key_siakad = b.ref_key_pegawai AND p.waktu_dihapus IS NULL
            JOIN hr.jabatan_akademik ja ON ja.id = p.id_jabatan_akademik AND ja.waktu_dihapus IS NULL
                AND ja.jenis_jabatan_akademik = :jenis_jabatan_akademik
            JOIN hr.employee_statuses es ON es.id = p.id_status_pegawai AND es.waktu_dihapus IS NULL
                AND es.is_active = :is_active_employee
            WHERE b.waktu_dihapus IS NULL
                AND p.id IS NOT NULL"
            . ($id_unit ? " AND coalesce(p.id_homebase_dosen, p.id_unit_kerja) = :id_unit" : '') . ($ignoredIdBiodata ? " AND b.id NOT IN (" . implode(',', $ignoredIdBiodata) . ")" : '') . "
            ORDER BY p.nip";

        $bindings = [
            'jenis_jabatan_akademik' => JabatanAkademik::DOSEN_AKADEMIK,
            'is_active_employee' => true,
        ];

        if ($id_unit) {
            $bindings['id_unit'] = $id_unit;
        }

        $data = DB::select($sql, $bindings);

        if (!$is_option) {
            return $data;
        }

        $options = [];
        foreach ($data as $d) {
            $options[$d->id_biodata] = $d->nip . ' - ' . $d->nama;
        }

        return $options;
    }

    public static function getDosenEksternal($is_option = false, $ignoredIdBiodata = [])
    {
        $sql = "SELECT b.id as id_biodata, concat('', de.nip) as nip, b.nama,
                de.id as id_dosen_eksternal, de.id_perguruan_tinggi_luar, de.status_usulan
            FROM core.biodata b
            JOIN litabmas.dosen_eksternal de ON de.id_biodata = b.id AND de.waktu_dihapus IS NULL
            WHERE b.waktu_dihapus IS NULL AND de.id IS NOT NULL
                AND de.status_usulan = :status_usulan
                " . ($ignoredIdBiodata ? " AND b.id NOT IN (" . implode(',', $ignoredIdBiodata) . ")" : '') . "
            ORDER BY de.nip";

        $bindings = [
            'status_usulan' => DosenEksternal::STATUS_BERHASIL_DIBUAT,
        ];

        $data = DB::select($sql, $bindings);

        if (!$is_option) {
            return $data;
        }

        $options = [];
        foreach ($data as $d) {
            $options[$d->id_biodata] = $d->nip . ' - ' . $d->nama;
        }

        return $options;
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'id_biodata');
    }
}
