<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\TreeStructure;

class IndikatorLaporanKinerja extends IndonesianModel
{
    use HasFactory, TreeStructure, SoftDeletes;

    // data source
    const DATA_MANUAL_INPUT = 'IN';
    const DATA_AKADEMIK = 'AK';
    const DATA_SDM = 'SDM';
    const DATA_SA = 'SA';
    const DATA_MAHASISWA = 'AC';
    const DATA_TRACER_STUDY = 'TS';
    const DATA_MBKM = 'MBK';
    const DATA_SIMKERMA = 'SKM';
    const DATA_PMB = 'PMB';

    // form type
    const FORM_ROW = 'FR';
    const FORM_COLUMN = 'FC';

    // layout type
    const LAYOUT_LANDSCAPE = 'L';
    const LAYOUT_PORTRAIT = 'P';

    const FORM_TYPE =[
        self::FORM_ROW => 'Form Row',
        self::FORM_COLUMN => 'Form Column',
    ];

    const LAYOUT_TYPE = [
        self::LAYOUT_LANDSCAPE => 'Landscape',
        self::LAYOUT_PORTRAIT => 'Portrait',
    ];

    const DATA_SOURCE = [
        self::DATA_MANUAL_INPUT => 'Manual Input',
        self::DATA_AKADEMIK => 'Akademik',
        self::DATA_SDM => 'Kepegawaian',
        self::DATA_SA => 'SDM dan Akademik',
        self::DATA_MAHASISWA => 'Unit Kerja',
        self::DATA_TRACER_STUDY => 'Tracer Study',
        self::DATA_MBKM => 'MBKM',
        self::DATA_SIMKERMA => 'Kerja Sama',
        self::DATA_PMB => 'PMB',
    ];

    const JENIS_MAPPING_PRODI = 'UP';
    const JENIS_MAPPING_NON_PRODI = 'UNA';
    const JENIS_MAPPING_ALL = 'AU';

    const JENIS_MAPPING = [
        self::JENIS_MAPPING_PRODI => 'Unit Prodi',
        self::JENIS_MAPPING_NON_PRODI => 'Unit Non Prodi',
        self::JENIS_MAPPING_ALL => 'Semua Unit',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.indikator_laporan_kinerja';

    protected $fillable = [
        'id_pengisian_panduan',
        'nomor_indikator',
        'nama_indikator_laporan_kinerja',
        'deskripsi',
        'informasi',
        'jenis_form',
        'apakah_layout_fixed',
        'apakah_data_default',
        'apakah_menggunakan_kategori',
        'apakah_memasukkan_kategori_manual',
        'apakah_menggunakan_ts',
        'apakah_subfooter',
        'jenis_layout',
        'sumber_data',
        'deskripsi_sumber_data',
        'apakah_import_excel',
        'dapat_dilihat_pada_laporan',
        'dapat_lihat_nama_pada_laporan',
        'apakah_parent',
        'apakah_aktif',
        'id_parent',
        'info_level',
        'info_left',
        'info_right',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_pengisian_panduan' => ['required' => true, 'options' => PengisianPanduan::class],
        'id_parent' => ['required' => false, 'options' => IndikatorLaporanKinerja::class],
        'nomor_indikator' => ['required' => true],
        'nama_indikator_laporan_kinerja' => ['required' => true],
        'deskripsi' => ['required' => false, 'control' => 'wysiwyg'],
        'informasi' => ['required' => false, 'control' => 'wysiwyg'],
        'apakah_aktif' => ['required' => false, 'type' => 'boolean'],
        'jenis_form' => ['required' => false, 'options' => self::FORM_TYPE],
        'sumber_data' => ['required' => false, 'options' => self::DATA_SOURCE],
        'deskripsi_sumber_data' => ['required' => false, 'control' => 'wysiwyg'],
        'jenis_layout' => ['required' => false, 'options' => self::LAYOUT_TYPE],
        'info_level' => ['required' => false],
        'info_left' => ['required' => false],
        'info_right' => ['required' => false],
        'apakah_layout_fixed' => ['required' => false, 'type' => 'boolean'],
        'apakah_menggunakan_kategori' => ['required' => false, 'type' => 'boolean'],
        'apakah_memasukkan_kategori_manual' => ['required' => false, 'type' => 'boolean'],
        'apakah_menggunakan_ts' => ['required' => false, 'type' => 'boolean'],
        'apakah_subfooter' => ['required' => false, 'type' => 'boolean'],
        'apakah_import_excel' => ['required' => false, 'type' => 'boolean'],
        'dapat_dilihat_pada_laporan' => ['required' => false],
        'dapat_lihat_nama_pada_laporan' => ['required' => false],
        'apakah_parent' => ['required' => false, 'type' => 'boolean'],
    ];

    /**
     * Get list of form type
     *
     * @return array
     */
    public static function getFormType()
    {
        return self::FORM_TYPE;
    }

    /**
     * Get list of layout type
     *
     * @return array
     */
    public static function getLayoutType()
    {
        return self::LAYOUT_TYPE;
    }

    /**
     * Get list of data source
     *
     * @return array
     */
    public static function getDataSource()
    {
        return self::DATA_SOURCE;
    }


    protected static function defineDepthField()
    {
        return 'info_level';
    }

    public static function options()
    {
        return self::getListComboTree();
    }

    public static function optionsByPengisianPanduan($id)
    {
        return self::getListComboTree(['id_pengisian_panduan' => $id]);
    }

    /**
     * Get list of combo tree
     *
     * @param array $condition
     * @return array
     */
    protected static function getListComboTree($condition = [], $disableParent = false)
    {
        $class = static::class;

        if (!empty($condition)) {
            $list = $class::where($condition)->orderBy('info_left', 'asc')->get();
        } else {
            $list = $class::orderBy('info_left', 'asc')->get();
        }

        // add prefix to name
        $list = $list->map(function ($item, $key) {
            $item->nama_indikator_laporan_kinerja = str_repeat('&nbsp;', $item->info_level * 4) . $item->nama_indikator_laporan_kinerja;
            return $item;
        });

        return $list->pluck('nama_indikator_laporan_kinerja', 'id')->toArray();
    }

    public static function getByPengisianPanduan($id)
    {
        return self::where('id_pengisian_panduan', $id)->get()->pluck('nama_indikator_laporan_kinerja', 'id')->toArray();
    }

    public static function getPengisianPanduanByID($id)
    {
        return self::where('id', $id)->first()->id_pengisian_panduan;
    }

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\IndikatorLaporanKinerjaFactory::new();
    }

    public function detailIndicator($id)
    {
        $indicator = [];
    }

    public function PengisianPanduan()
    {
        return $this->belongsTo(PengisianPanduan::class);
    }

    public static function resyncTreeStructure($idPengisianPanduan)
    {
        // Get all nodes for this penilaian panduan
        $allNodes = static::where('id_pengisian_panduan', $idPengisianPanduan)
            ->orderBy('id')
            ->get();

        if ($allNodes->isEmpty()) {
            return 0;
        }

        // Build parent-child map
        $children = [];
        $rootNodes = [];

        foreach ($allNodes as $node) {
            $parentId = $node->id_parent;

            if (empty($parentId)) {
                $rootNodes[] = $node->id;
            } else {
                if (!isset($children[$parentId])) {
                    $children[$parentId] = [];
                }
                $children[$parentId][] = $node->id;
            }
        }

        // Create lookup map for quick access
        $nodesMap = [];
        foreach ($allNodes as $node) {
            $nodesMap[$node->id] = $node;
        }

        // Traverse tree and assign left/right values
        $counter = 1;
        $updates = [];

        foreach ($rootNodes as $rootId) {
            $counter = static::traverseAndAssign($rootId, $counter, 0, $children, $nodesMap, $updates);
        }

        // Batch update all nodes
        foreach ($updates as $update) {
            static::where('id', $update['id'])->update([
                'info_left' => $update['info_left'],
                'info_right' => $update['info_right'],
                'info_level' => $update['info_level']
            ]);
        }

        return count($updates);
    }

    /**
     * Recursively traverse tree and assign left/right values
     */
    protected static function traverseAndAssign($nodeId, $counter, $depth, $children, $nodesMap, &$updates)
    {
        if (!isset($nodesMap[$nodeId])) {
            return $counter;
        }

        $leftValue = $counter++;

        // Process children
        if (isset($children[$nodeId])) {
            foreach ($children[$nodeId] as $childId) {
                $counter = static::traverseAndAssign($childId, $counter, $depth + 1, $children, $nodesMap, $updates);
            }
        }

        $rightValue = $counter++;

        // Store update info
        $updates[] = [
            'id' => $nodeId,
            'info_left' => $leftValue,
            'info_right' => $rightValue,
            'info_level' => $depth
        ];

        return $counter;
    }
}
