<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\ClearCache;
use Modules\Core\Extensions\Models\Traits\TreeStructure;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Format;
use Modules\Core\Helpers\Query;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Services\UnitKerjaManagementService;

class UnitKerja extends IndonesianModel
{
    use ClearCache, SoftDeletes, TreeStructure;

    const UNIVERSITY = 'U';
    const STUDY_PROGRAM = 'P';
    const FACULTY = 'F';
    const MAJOR = 'J';
    const UNIT_NON_PRODI = 'N';
    const TYPES = [
        self::UNIVERSITY => 'Universitas',
        self::STUDY_PROGRAM => 'Program Studi',
        self::FACULTY => 'Fakultas',
        self::MAJOR => 'Jurusan',
        self::UNIT_NON_PRODI => 'Unit Non Akademik'
    ];

    // Akreditasi
    const ACCREDITATION_A = 'A';
    const ACCREDITATION_B = 'B';
    const ACCREDITATION_C = 'C';
    const ACCREDITATION_EXCELLENT = 'U';
    const ACCREDITATION_VERY_WELL = 'S';
    const ACCREDITATION_GOOD = 'G';
    const ACCREDITATION_MINIMUM = 'M';
    const ACCREDITATION_EXPIRED = 'K';
    const ACCREDITATION_LIST = [
        self::ACCREDITATION_A => 'A',
        self::ACCREDITATION_B => 'B',
        self::ACCREDITATION_C => 'C',
        self::ACCREDITATION_EXCELLENT => 'Unggul',
        self::ACCREDITATION_VERY_WELL => 'Baik Sekali',
        self::ACCREDITATION_GOOD => 'Baik',
        self::ACCREDITATION_MINIMUM => 'Minimum',
        self::ACCREDITATION_EXPIRED => 'Tidak Terakreditasi / Kedaluwarsa',
    ];

    const GRADUATE_REQUIREMENT_HIGH = 'HI';
    const GRADUATE_REQUIREMENT_LOW = 'LW';
    const GRADUATE_REQUIREMENT_LIST = [
        self::GRADUATE_REQUIREMENT_HIGH => 'Tinggi',
        self::GRADUATE_REQUIREMENT_LOW => 'Rendah',
    ];

    const KEBUTUHAN_LULUSAN_MAP_FROM_SIAKAD = [
        'T' => self::GRADUATE_REQUIREMENT_HIGH,
        'R' => self::GRADUATE_REQUIREMENT_LOW
    ];

    const MAJOR_GROUP_SOCIAL = 'SH';
    const MAJOR_GROUP_SCIENCE = 'ST';
    const MAJOR_GROUP_LIST = [
        self::MAJOR_GROUP_SOCIAL => 'Sosial Humaniora',
        self::MAJOR_GROUP_SCIENCE => 'Sains Teknologi',
    ];

    protected $fillable = [
        'nama_unit',
        'kode_unit',
        'kode_dikti',
        'jenis_unit',
        'id_jenjang_pendidikan',
        'id_parent',
        'info_level',
        'info_left',
        'info_right',
        'apakah_akademik',
        'apakah_satker',
        'apakah_aktif',
        'apakah_aktif_pmb',
        'id_lembaga_akreditasi',
        'kebutuhan_lulusan',
        'kelompok_prodi',
        'id_pimpinan',
        'ref_key_siakad',
        'ref_key_satker',
        'visi',
        'misi',
        'alamat',
        'telepon',
        'website',
        'email',
        'gelar',
        'gelar_en',
        'gelar_singkat',
        'gelar_singkat_en',
        'pmb_deskripsi_unit',
        'pmb_prospek_karir',
        'pmb_bidang_ilmu',
        'pmb_biaya_kuliah',
        'tanggal_berdiri',
        'apakah_data_default',
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'info_left';
    const OPTION_COLUMN = 'nama_unit';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_unit' => ['required' => true, 'maxlength' => 255],
        'kode_unit' => ['required' => true, 'maxlength' => 10, 'unique' => true],
        'kode_dikti' => ['required' => false, 'maxlength' => 10, 'unique' => true],
        'jenis_unit' => ['required' => false, 'maxlength' => 1, 'options' => self::TYPES],
        'id_jenjang_pendidikan' => ['required' => false, 'options' => JenjangPendidikan::class],
        'id_parent' => ['required' => false, 'options' => UnitKerja::class],
        'info_level' => ['required' => false, 'type' => 'integer'],
        'info_left' => ['required' => false, 'type' => 'integer'],
        'info_right' => ['required' => false, 'type' => 'integer'],
        'apakah_akademik' => ['required' => false, 'type' => 'boolean'],
        'apakah_satker' => ['required' => false, 'type' => 'boolean'],
        'apakah_aktif' => ['required' => true, 'type' => 'boolean'],
        'apakah_aktif_pmb' => ['type' => 'boolean'],
        'id_lembaga_akreditasi' => ['required' => false, 'options' => LembagaAkreditasi::class],
        'kebutuhan_lulusan' => ['required' => false, 'options' => self::GRADUATE_REQUIREMENT_LIST, 'control' => 'radio'],
        'kelompok_prodi' => ['required' => false, 'options' => self::MAJOR_GROUP_LIST, 'control' => 'radio'],
        'id_pimpinan' => ['required' => false, 'options' => Pegawai::class],
        'visi' => ['required' => false],
        'misi' => ['required' => false],
        'alamat' => ['required' => false],
        'telepon' => ['required' => false],
        'website' => ['required' => false],
        'email' => ['required' => false],
        'gelar' => ['required' => false],
        'gelar_en' => ['required' => false],
        'gelar_singkat' => ['required' => false],
        'gelar_singkat_en' => ['required' => false],
        'pmb_deskripsi_unit' => ['required' => false],
        'pmb_prospek_karir' => ['required' => false],
        'pmb_bidang_ilmu' => ['required' => false],
        'pmb_biaya_kuliah' => ['required' => false],
    ];

    /**
     * Jenjang pendidikan.
     */
    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class, 'id_jenjang_pendidikan');
    }

    /**
     * Nama prodi.
     */
    protected function namaProdi(): Attribute
    {
        return Attribute::make(
            get: fn () => static::showNamaProdi($this->nama_unit, $this->jenjang->kode_jenjang)
        );
    }

    /**
     * Nama prodi.
     */
    public static function showNamaProdi($namaUnit, $kodeJenjang)
    {
        if (empty($kodeJenjang)) {
            return $namaUnit;
        }

        return $kodeJenjang . ' - ' . $namaUnit;
    }

    /**
     * Display options.
     * @param int|null $id
     * @param bool $withIndent
     * @param bool $isOnlyActive
     * @param bool $isActivePMB
     * @param bool $excludeInactiveParents
     * @param bool $excludeInactiveChildren
     * @param bool|null $isCheckPascaSarjana
     * @return array
     */
    public static function options(
        int $id = null,
        bool $withIndent = true,
        bool $isOnlyActive = false,
        bool $isActivePMB = false,
        bool $excludeInactiveParents = false,
        bool $excludeInactiveChildren = false,
        bool|null $isCheckPascaSarjana = null
    ) {
        // build sql
        $sql = "select u.id, u.nama_unit, u.info_level, j.kode_jenjang, u.id_parent, j.apakah_pasca, u.jenis_unit";

        // exclude inactive children select
        if ($excludeInactiveChildren) {
            $sql .= ", coalesce(ac.active_children_count, 0) as active_children_count, coalesce(ac.total_children_count, 0) as total_children_count";
        }

        if ($excludeInactiveParents) {
            $sql .= ", p.apakah_aktif as parent_active";
        }


        $sql .= " from core.unit_kerja u
        left join core.jenjang_pendidikan j on j.id = u.id_jenjang_pendidikan and j.waktu_dihapus is null";

        // exclude inactive children join
        if ($excludeInactiveChildren) {
            $sql .= "
                left join (
                    select
                        c.id_parent,
                        COUNT(c.id) AS total_children_count,
                        COUNT(c.id) FILTER(WHERE c.apakah_aktif = true) AS active_children_count
                    from core.unit_kerja c
                    where
                        c.waktu_dihapus is null
                    group by c.id_parent
                ) ac on ac.id_parent = u.id";
        }

        if ($excludeInactiveParents) {
            $sql .= " left join core.unit_kerja p on p.id = u.id_parent and p.waktu_dihapus is null";
        }

        $sql .= " where u.waktu_dihapus is null";

        if ($isOnlyActive) {
            $sql .= " and u.apakah_aktif = true";
        }

        if ($isActivePMB) {
            $sql .= " and u.apakah_aktif_pmb = true";
        }

        if (isset($isCheckPascaSarjana)) {
            $boolPasca = $isCheckPascaSarjana ? 'true' : 'false';
            $sql .= " and (j.apakah_pasca is null OR j.apakah_pasca is $boolPasca)";
        }

        $param = [];
        if (!empty($id)) {
            $sql .= " and u.id = ?";
            $param[] = $id;
        }

        $sql .= " order by u.info_left";
        $rows = DB::select($sql, $param);
        $minLevel = $rows[0]->info_level;

        if (isset($isCheckPascaSarjana)) {
            $rows = Format::removeUnitHasEmptyChild($rows);
        }

        $options = [];
        foreach ($rows as $row) {
            // Mengecek apakah parent unit aktif
            if ($excludeInactiveParents && $row->id_parent && !$row->parent_active) {
                continue; // Skip jika parent nonaktif
            }

            // Mengecek apakah unit memiliki child aktif
            if ($excludeInactiveChildren) {
                if ($row->total_children_count > 0 && $row->active_children_count == 0) {
                    continue; // Skip if all children are inactive
                }
            }

            $label = $row->nama_unit;
            if ($row->kode_jenjang) {
                $label = static::showNamaProdi($label, $row->kode_jenjang);
            }
            if ($withIndent && empty($id) && $row->info_level > $minLevel) {
                $label = str_repeat('&nbsp;', ($row->info_level - $minLevel) * 4) . $label;
            }

            $options[$row->id] = $label;
        }

        return $options;
    }

    /**
     * Display option value.
     * @param int $id
     * @return mixed
     */
    public static function optionValue($id)
    {
        return current(static::options($id));
    }

    /**
     * Get options unit by type/jenis
     *
     * @param array $type
     * @return array
     */
    public static function optionByType(array $type, $isWithCheckUserUnit = true, $isRawData = false, $isOnlyActive = false, $isHaveJenjang = false)
    {
        $orderColumn = static::OPTION_ORDER;

        $data = static::orderByRaw($orderColumn)
            ->whereIn('jenis_unit', $type)
            ->when($isHaveJenjang, function ($query) {
                $query->whereNotNull('id_jenjang_pendidikan');
            })
            ->with('jenjangPendidikan');

        $userUnit = session()->get('user.unit_kerja');

        $isInternalRole = SessionManager::isInternalRole();
        if ($isWithCheckUserUnit && !empty($userUnit) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $data = $data->whereIn('id', $ids);
            } else {
                $data = $data->where('id', 0); // pastikan tidak ada data yang ditampilkan
            }
        }

        if ($isOnlyActive) {
            $data->where('apakah_aktif', true);
        }

        $data = $data->get(['id', static::OPTION_COLUMN, 'id_jenjang_pendidikan', 'jenis_unit', 'info_left', 'apakah_aktif'])
            ->toArray();

        $data = array_map(function ($item) {
            if ($item['jenis_unit'] === self::STUDY_PROGRAM) {
                $kodeJenjang = $item['jenjang_pendidikan']['kode_jenjang'] ?? '';
                $item['nama_unit'] = "{$kodeJenjang} - {$item['nama_unit']}";
                $item['nama_unit'] .= $item['apakah_aktif'] ? '' : ' (Tidak Aktif)';
            }

            return $item;
        }, $data);

        if ($isRawData) {
            return $data;
        }

        return array_column($data, 'nama_unit', 'id');
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.unit_kerja';

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        UnitKerjaCache::destroy($model->id);
    }

    public static function showInformation(int $id)
    {
        $self = static::find($id);
        $sql = "SELECT nama_unit, jenis_unit FROM core.unit_kerja
                where info_left <= ?
                and info_right >= ?
                and info_level < ?
                ORDER BY info_left DESC";

        // execute sql
        $parent = DB::select($sql, [$self['info_left'], $self['info_right'], $self['info_level']]);
        $mapping = collect($parent)->map(function ($item) {
            return (array) $item;
        })->toArray();
        $mapping = Cstr::toMapObject('jenis_unit', $mapping);

        return $mapping;
    }

    public function pimpinan()
    {
        return $this->belongsTo(Pegawai::class, 'id_pimpinan');
    }

    public function jenjangPendidikan()
    {
        return $this->belongsTo(JenjangPendidikan::class, 'id_jenjang_pendidikan');
    }
}
