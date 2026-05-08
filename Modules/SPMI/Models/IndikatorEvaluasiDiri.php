<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\TreeStructure;
use Modules\DMS\Models\Dokumen;

class IndikatorEvaluasiDiri extends IndonesianModel
{
    use HasFactory, TreeStructure, SoftDeletes;

    const OPTION_ORDER = 'nomor_indikator asc';
    const OPTION_COLUMN = 'nama_indikator_evaluasi_diri';

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
    protected $table = 'spmi.indikator_evaluasi_diri';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengisian_panduan',
        'nomor_indikator',
        'nama_indikator_evaluasi_diri',
        'deskripsi',
        'apakah_parent',
        'apakah_komentar',
        'apakah_aktif',
        'id_parent',
        'apakah_data_default',
        'apakah_key_point'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengisian_panduan' => ['required' => true, 'options' => PengisianPanduan::class], //
        'nomor_indikator' => ['required' => true, 'maxlength' => 255], //
        'nama_indikator_evaluasi_diri' => ['required' => true], //
        'deskripsi' => ['required' => false], //
        'is_label' => ['required' => false, 'type' => 'boolean'], //
        'apakah_komentar' => ['required' => false, 'type' => 'boolean'], //
        'apakah_aktif' => ['required' => false, 'type' => 'boolean'], //
        'apakah_parent' => ['required' => false, 'type' => 'boolean'], //
        'id_parent' => ['required' => false], //
        'apakah_data_default' => ['required' => false, 'type' => 'boolean'], //
        'id_dokumen' => [
            'required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024
        ], // ID Dokumen
    ];

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
            $item->nama_indikator_evaluasi_diri = str_repeat('&nbsp;', $item->info_level * 4) . $item->nama_indikator_evaluasi_diri;
            return $item;
        });

        return $list->pluck('nama_indikator_evaluasi_diri', 'id')->toArray();
    }

    public static function getPengisianPanduanByID($id)
    {
        return self::find($id)->id_pengisian_panduan;
    }

    public function checkIsDefaultData()
    {
        return $this->apakah_data_default;
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
