<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\TreeStructure;

class PenilaianMatriks extends IndonesianModel
{
    use TreeStructure, SoftDeletes;

    const OPTION_ORDER = 'info_left asc';
    const OPTION_COLUMN = 'pertanyaan_penilaian';

    const TYPE_QUALITATIVE = 'IN';
    const TYPE_QUANTITATIVE = 'PR';
    const TYPE_ACCUMULATION = 'TL';
    const TYPE_FINAL_SCORE = 'SA';
    const TYPE_MIXED = 'GB';
    const TYPES = [
        self::TYPE_QUALITATIVE => 'Kualitatif',
        self::TYPE_FINAL_SCORE => 'Skor Akhir',
        self::TYPE_QUANTITATIVE => 'Kuantitatif',
        self::TYPE_ACCUMULATION => 'Akumulasi Skor',
        self::TYPE_MIXED => 'Gabungan (Kualitatif & Kuantitatif)'
    ];

    const CATEGORY_ELEMENT = 'E';
    const CATEGORY_DIMENSION = 'D';
    const CATEGORY_INDICATOR = 'I';
    const CATEGORIES = [
        self::CATEGORY_ELEMENT => 'Elemen',
        self::CATEGORY_DIMENSION => 'Dimensi',
        self::CATEGORY_INDICATOR => 'Indikator'
    ];

    const REFERENCE_PERFORMANCE_REPORT = 'pr';
    const REFERENCE_SELF_EVALUATION = 'se';
    const REFERENCE_MIXED_EVALUATION = 'me';
    const REFERENCES = [
        self::REFERENCE_PERFORMANCE_REPORT => 'Laporan Kinerja',
        self::REFERENCE_SELF_EVALUATION => 'Evaluasi Diri',
        self::REFERENCE_MIXED_EVALUATION => 'Laporan Kinerja dan Evaluasi Diri'
    ];

    const REFERENCES_WITHOUT_MIXED_EVALUATION = [
        self::REFERENCE_PERFORMANCE_REPORT => 'Laporan Kinerja',
        self::REFERENCE_SELF_EVALUATION => 'Evaluasi Diri',
    ];

    const ACCREDITATION_REQUIREMENT_NOT = 'T';
    const ACCREDITATION_REQUIREMENT_RANK = 'P';
    const ACCREDITATION_REQUIREMENT_ACCREDITED = 'A';
    const akreditasi_syarat = [
        self::ACCREDITATION_REQUIREMENT_NOT => 'Tidak',
        self::ACCREDITATION_REQUIREMENT_RANK => 'Peringkat Akreditasi',
        self::ACCREDITATION_REQUIREMENT_ACCREDITED => 'Terakreditasi'
    ];

    const UNIVERSITY_STANDARD_SN = 'SN';
    const UNIVERSITY_STANDARD_RS = 'RS';
    const UNIVERSITY_STANDARDS = [
        self::UNIVERSITY_STANDARD_SN => 'SN-Dikti',
        self::UNIVERSITY_STANDARD_RS => 'Target pada Rencana Strategis'
    ];

    const STATUSES = [
        1 => 'Aktif',
        0 => 'Tidak Aktif'
    ];

    const CODE_INDIKATOR_TAMBAHAN = 'C.10';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_matriks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_panduan',
        'id_parent',
        'nomor_penilaian',
        'kategori_penilaian',
        'pertanyaan_penilaian',
        'id_penilaian_klaster',
        'id_akreditasi_standar',
        'syarat_terakreditasi',
        'standar_perguruan_tinggi',
        'apakah_aktif',
        'jenis_penilaian',
        'bobot_penilaian',
        'referensi_penilaian',
        'deskripsi',
        'apakah_nilai_ditampilkan',
        'apakah_data_default',
        'info_level',
        'rumus_penilaian',
        'info_left',
        'info_right',
        'butir_indikator_spme',
        'butir_indikator_iku',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_panduan' => ['required' => true, 'options' => PenilaianPanduan::class], // Panduan Penilaian
        'id_parent' => ['type' => 'numeric', 'options' => self::class], // Parent Matrix Penilaian
        'nomor_penilaian' => ['required' => true, 'maxlength' => 15], // No. Penilaian
        'kategori_penilaian' => [
            'required' => true,
            'maxlength' => 255,
            'options' => self::CATEGORIES
        ], // Kategori Matrix Penilaian (E: Element, D: Dimensi, I: Indikator)
        'pertanyaan_penilaian' => ['required' => true, 'control' => 'textarea'], // Pertanyaan Penilaian
        'id_penilaian_klaster' => ['required' => false, 'options' => PenilaianKlaster::class], // Kluster Penilaian
        'id_akreditasi_standar' => [
            'required' => false,
            'options' => AkreditasiStandar::class,
        ], // Standar Akreditasi
        'syarat_terakreditasi' => [
            'required' => false,
            'options' => self::akreditasi_syarat,
            'control' => 'radio'
        ], // Syarat Perllu Akreditasi (T: Tidak, P: Peringkat Akreditasi, A: Terakreditasi)
        'standar_perguruan_tinggi' => ['required' => false, 'options' => self::UNIVERSITY_STANDARDS, 'control' => 'radio'], // Standart Pendidikan Tinggi (SN: SN-Dikti, RS: Target pada Rencana Strategis)
        'apakah_aktif' => ['required' => true, 'type' => 'boolean', 'options' => self::STATUSES], // Status Penilaian
        'jenis_penilaian' => [
            'maxlength' => 2,
            'options' => self::TYPES
        ], // Jenis Penilaian (IN: Kualitatif, PR: Kuantitatif, TL: Akumulasi Skor, SA: Skor Akhir)
        'bobot_penilaian' => ['required' => false, 'type' => 'numeric'], // Bobot Penilaian
        'referensi_penilaian' => [
            'required' => false,
            'maxlength' => 255,
            'options' => self::REFERENCES
        ], // Sumber Referensi (BA: Laporan Kinerja, ED: Evaluasi Diri)
        'deskripsi' => ['required' => false, 'control' => 'textarea'], // Keterangan
        'apakah_nilai_ditampilkan' => ['required' => false, 'type' => 'boolean'], // Tampilkan hasil akhir
        'butir_indikator_spme' => ['required' => false, 'type' => 'boolean'], // Apakah Butir Indikator SPME
        'butir_indikator_iku' => ['required' => false, 'type' => 'boolean'], // Apakah Butir Indikator IKU
        'rumus_penilaian' => ['required' => false], // Rumus
        'apakah_parent_custom' => ['required' => false, 'type' => 'boolean'], // Appended Attribute
    ];

    protected $appends = [
        'apakah_parent_custom'
    ];

    public function getApakahParentCustomAttribute(): bool
    {
        return $this->kategori_penilaian === self::CATEGORY_ELEMENT;
    }

    protected static function defineDepthField()
    {
        return 'info_level';
    }

    /**
     * Display options by id_penilaian_panduan.
     * @return array
     */
    public static function optionsByPenilaianPanduan($assessmentGuideId, $isDefaultData = true)
    {
        $list = static::where('id_penilaian_panduan', $assessmentGuideId)
            ->where('apakah_data_default', $isDefaultData)
            ->orderByRaw(static::OPTION_ORDER)
            ->get(['id', static::OPTION_COLUMN, 'info_level']);

        // Menambahkan indentasi pada pertanyaan
        $list = $list->map(function ($item, $key) {
            $item->pertanyaan_penilaian = str_repeat('&nbsp;', $item->info_level * 4) . $item->pertanyaan_penilaian;
            return $item;
        });

        $list = $list->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();

        return $list;
    }

    public function getParentElement($type = null)
    {
        $currentMatrix = $this;
        $parentMatrix = null;

        while (!is_null($currentMatrix->id_parent)) {
            $currentMatrix = PenilaianMatriks::find($currentMatrix->id_parent);
            $condition = $currentMatrix->jenis_penilaian == $type;

            if (empty($type)) {
                $condition = ($currentMatrix->jenis_penilaian == self::TYPE_ACCUMULATION || $currentMatrix->jenis_penilaian == self::TYPE_FINAL_SCORE);
            }

            if (
                $currentMatrix->kategori_penilaian == self::CATEGORY_ELEMENT &&
                $condition
            ) {
                $parentMatrix = $currentMatrix;
                break;
            }
        }

        return $parentMatrix;
    }

    public static function resyncTreeStructure($idPenilaianPanduan)
    {
        // Get all nodes for this penilaian panduan
        $allNodes = static::where('id_penilaian_panduan', $idPenilaianPanduan)
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
