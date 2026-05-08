<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\UI;
use Illuminate\Support\Str;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\DataPengisianLK;
use Modules\SPMI\Models\DokumenPendukungPengisianLk;
use Modules\SPMI\Models\IndikatorCell;
use Modules\SPMI\Models\IndikatorKolom;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\IndikatorBaris;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PenilaianMatriksReferensi;

class IndikatorLaporanKinerjaManagementService
{
    /**
     * @var IndikatorLaporanKinerja
     */
    protected $model = IndikatorLaporanKinerja::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new IndikatorLaporanKinerja;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {

        $table = $this->model->getTable();
        $sql = "SELECT * FROM $table";

        $defaultFilter = 'waktu_dihapus is null';

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return IndikatorLaporanKinerja
     */
    public function show(int $id): IndikatorLaporanKinerja
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return IndikatorLaporanKinerja
     */
    public function store(array $data): IndikatorLaporanKinerja|Error
    {
        try {
            $data['apakah_data_default'] = false; // Harcode for IKT
            $new = $this->model->create($data);

            if (isset($data['program_studi']) && isset($data['periode_ami'])) {
                $mappingLK = [];
                foreach ($data['periode_ami'] as $periode) {
                    foreach ($data['program_studi'] as $unit) {
                        $mappingLK[] = [
                            'id_indikator_laporan_kinerja' => $new->id,
                            'id_audit_periode' => $periode,
                            'id_unit' => $unit,
                        ];
                    }
                }

                MappingLK::insert($mappingLK);
            }

            return $new;
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return IndikatorLaporanKinerja
     */
    public function update(array $data, int $id): IndikatorLaporanKinerja
    {
        $model = $this->model->findOrFail($id);
        if (isset($data['jenis_form']) && is_array($data['jenis_form'])) {
            $data['jenis_form'] = $data['jenis_form']['value'];
        }
        if ($data['id_parent'] == '') {
            $data['id_parent'] = null;
        }
        $model->update($data);

        if (isset($data['program_studi']) && isset($data['periode_ami'])) {
            MappingLK::where('id_indikator_laporan_kinerja', $id)->delete();
            $mappingLK = [];
            foreach ($data['periode_ami'] as $periode) {
                foreach ($data['program_studi'] as $unit) {
                    $mappingLK[] = [
                        'id_indikator_laporan_kinerja' => $id,
                        'id_audit_periode' => $periode,
                        'id_unit' => $unit,
                    ];
                }
            }

            MappingLK::insert($mappingLK);
        }

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id): true | Error
    {
        if ($this->checkReference($id)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        // Cek apakah sudah terdapat mapping data
        $isHasMapping = MappingLK::where('id_indikator_laporan_kinerja', $id)->exists();

        if ($isHasMapping) {
            return new Error('Tidak dapat menghapus data yang sudah dimappingkan.');
        }

        try {
            IndikatorLaporanKinerja::where('id', $id)->delete();
        } catch (\Exception) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi');
        }

        return true;
    }

    protected function checkReference(int $id): bool
    {
        $isReferenceDataPengisian = DataPengisianLK::where('id_indikator_laporan_kinerja', $id)->exists();
        $isReferenceDokumenPendukung = DokumenPendukungPengisianLk::where('id_indikator_laporan_kinerja', $id)->exists();
        $isReferencePenilaianMatriks = PenilaianMatriksReferensi::join('spmi.penilaian_matriks', 'spmi.penilaian_matriks.id', '=', 'penilaian_matriks_referensi.id_penilaian_matriks')
            ->whereNull('spmi.penilaian_matriks.waktu_dihapus')
            ->where('jenis_referensi', AkreditasiBuku::PERFORMANCE_REPORT)
            ->where('id_butir_referensi', $id)
            ->exists();

        return $isReferenceDataPengisian || $isReferenceDokumenPendukung || $isReferencePenilaianMatriks;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        // Cek apakah sudah terdapat referensi data
        $isReferenceDataPengisian = DataPengisianLK::whereIn('id_indikator_laporan_kinerja', $ids)->exists();
        $isReferenceDokumenPendukung = DokumenPendukungPengisianLk::whereIn('id_indikator_laporan_kinerja', $ids)->exists();
        $isReferencePenilaianMatriks = PenilaianMatriksReferensi::join('spmi.penilaian_matriks', 'spmi.penilaian_matriks.id', '=', 'penilaian_matriks_referensi.id_penilaian_matriks')
            ->whereNull('spmi.penilaian_matriks.waktu_dihapus')
            ->where('jenis_referensi', AkreditasiBuku::PERFORMANCE_REPORT)
            ->whereIn('id_butir_referensi', $ids)
            ->exists();

        if ($isReferenceDataPengisian || $isReferenceDokumenPendukung || $isReferencePenilaianMatriks) {
            return new Error('Beberapa data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        // Cek apakah sudah terdapat mapping data
        $isHasMapping = MappingLK::whereIn('id_indikator_laporan_kinerja', $ids)->exists();

        if ($isHasMapping) {
            return new Error('Beberapa data tabel laporan kinerja tambahan tidak dapat dihapus. Karena sedang digunakan pada pemetaan LK.');
        }

        try {
            foreach ($ids as $id) {
                IndikatorLaporanKinerja::where('id', $id)->delete();
            }
        } catch (\Throwable) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi');
        }

        return true;
    }

    /**
     * Get all indicator performance report by filling guide
     *
     * @return mixed
     */
    public function showAllIndicatorsByPengisianPanduan(int $idPanduanPengisian,int $idAuditPeriode,int $idUnit, bool $isTree = false, $link = null)
    {
        $data = DB::table('spmi.mapping_lk as mlk')
            ->where('mlk.id_audit_periode', $idAuditPeriode)
            ->where('mlk.id_unit', $idUnit)
            ->join('spmi.indikator_laporan_kinerja as ilk', function ($join) use ($idPanduanPengisian) {
                $join->on('ilk.id', '=', 'mlk.id_indikator_laporan_kinerja')
                    ->where('ilk.id_pengisian_panduan', '=', $idPanduanPengisian);
            })
            ->where('ilk.waktu_dihapus', null)
            ->orderBy('ilk.info_left', 'asc')
            ->get([
                'ilk.id',
                'ilk.id_parent',
                'ilk.nomor_indikator',
                'ilk.nama_indikator_laporan_kinerja',
                'ilk.apakah_parent',
                'ilk.info_level',
                'ilk.info_left',
                'ilk.info_right',
                'ilk.apakah_data_default',
            ]);

        if ($isTree) {
            $tree = [];
            foreach ($data as $row) {
                $v = (array) $row;
                $id = $v['id'];

                $tree[$id] = $v + [
                    'link' => $link . $id,
                    'children' => [],
                ];
            }

            foreach ($tree as $id => &$node) {
                $pid = $node['id_parent'];

                if ($pid !== null && isset($tree[$pid])) {
                    $tree[$pid]['children'][] = &$node;
                } else {
                    if ($pid !== null && isset($node['apakah_parent']) && $node['apakah_parent'] === false) {
                        $node['id_parent'] = null;
                    }
                }
            }
            unset($node);

            $roots = array_filter($tree, function ($node) {
                return $node['id_parent'] === null;
            });

            $sortChildren = function (&$nodes) use (&$sortChildren) {
                usort($nodes, fn($a, $b) => $a['info_left'] <=> $b['info_left']);

                foreach ($nodes as &$n) {
                    if (!empty($n['children'])) {
                        usort($n['children'], fn($a, $b) => $a['info_left'] <=> $b['info_left']);
                        $sortChildren($n['children']);
                    }
                }
            };
            $sortChildren($roots);

            $flatten = function ($nodes) use (&$flatten) {
                $result = [];
                foreach ($nodes as $node) {
                    $children = $node['children'] ?? [];
                    $node['children'] = [];
                    $result[] = $node;
                    if (!empty($children)) {
                        $result = array_merge($result, $flatten($children));
                    }
                }
                return $result;
            };

            $data = collect($flatten($roots));
        }

        return $data;
    }

    /**
     * Generator Table Indicator Performance Report
     */
    public function generateTable(int $id, $cond = [], $records = [], $isEditAll = false, $isPreview = false)
    {
        // indicator performance report information
        $indicator = $this->show($id);

        // get condition from indicator performance report
        $cond['jenis_form'] = $indicator->jenis_form;
        $cond['apakah_layout_fixed'] = $indicator->apakah_layout_fixed;
        $cond['apakah_menggunakan_kategori'] = $indicator->apakah_menggunakan_kategori;
        $cond['apakah_memasukkan_kategori_manual'] = $indicator->apakah_memasukkan_kategori_manual;
        $cond['apakah_menggunakan_ts'] = $indicator->apakah_menggunakan_ts;
        $cond['jenis_layout'] = $indicator->jenis_layout;
        $cond['apakah_subfooter'] = $indicator->apakah_subfooter;
        $cond['is_preview'] = $isPreview;

        // Create Header
        list($contentHeader, $dataChildHeader) = $this->generateColumns($id, (!$cond['apakah_layout_fixed'] && !$cond['is_preview']), $cond);
        // Create Row
        $rows = $this->generateRow($id);
        // Create Cell
        $dataCell = $this->generateCell($id, $dataChildHeader);

        // Create Output table
        $output = '<table class="table table-bordered table-hover table-striped" cellpadding="4" cellspacing="0">';

        $output .= $contentHeader;
        $output .= '<tbody>';

        $dataRecords = [];
        if ($rows->count() > 0) { // if row exist (category)
            // prepare variable to count or sum
            for ($no = 1; $no <= count($dataChildHeader); $no++) {
                $sum[$no] = 0;
                $count[$no] = 0;
                $countnotempty[$no] = 0;
            }
            $footersSubs = [];
            $lastKey = 1;
            $number = 0;
            foreach ($rows as $k => $row) {
                if ($cond['apakah_menggunakan_kategori']) {
                    if ($cond['jenis_form'] == IndikatorLaporanKinerja::FORM_ROW) {
                        $output .= '<tr>';
                        $output .= '<td colspan="100%"><b>' . $row['nama'] . '</b></td>';
                        $output .= '</tr>';
                    }
                }

                // add record for input
                $record = $records[$k + 1] ?? [];
                $countRecord = count($record);
                $row['key'] = $k + 1;
                $row['firstKey'] = $lastKey;
                $row['lastKey'] = $row['firstKey']  + ($countRecord == 1 ? 0 : $countRecord);
                $lastKey = $row['lastKey'] + 1;
                if ($row['info_level'] == 0) {
                    $number++;
                    $row['number'] = $number;
                }

                list($contentRecord, $footerRecords, $dataRecord) = $this->getRecord($dataChildHeader, $cond, $row, $dataCell, $record, $isEditAll, $dataRecords);


                // merge data record not change key
                $dataRecords = $dataRecords + $dataRecord;

                $output .= $contentRecord;

                // sum all record
                foreach ($footerRecords['sum'] as $k => $v) {
                    $sum[$k] += $v;
                    $count[$k] += $footerRecords['count'][$k];
                    $countnotempty[$k] += $footerRecords['countnotempty'][$k];
                }

                // add sub footer if exist
                if ($cond['apakah_subfooter']) {
                    $footersSubs[] = $footerRecords;
                    if ($row['row_range_to'] == $row['key'] || (empty($row['row_range_to']) && empty($row['row_range_from']))) {
                        $output .= $this->getFooter($id, dataFooter: $footersSubs, isSubFooter: true, childHeader: $dataChildHeader);
                        // reset footer sub
                        $footersSubs = [];
                    }
                }
            }

            $dataFooterAll = [];
            $dataFooterAll['sum'] = $sum;
            $dataFooterAll['count'] = $count;
            $dataFooterAll['countnotempty'] = $countnotempty;


            $output .= '</tbody>';
            $output .= $this->getFooter($id, dataFooter: $dataFooterAll, childHeader: $dataChildHeader, records: $dataRecords);
        } else if (!$cond['apakah_layout_fixed'] && !empty($dataChildHeader)) { // this normal table
            list($contentRecord, $footerRecords, $dataRecord) = $this->getRecord($dataChildHeader, $cond, [], $dataCell, $records, $isEditAll);
            $output .=  $contentRecord;

            $output .= $this->getFooter($id, dataFooter: $footerRecords, records: $dataRecord, childHeader: $dataChildHeader);
        } else {
            $output .= '<tr>';
            $output .= '<td colspan="100%">Tidak ada data</td>';
            $output .= '</tr>';

            $output .= '</tbody>';

            if (!$cond['apakah_subfooter'])
                $output .= $this->getFooter($id, dataFooter: $cond, records: $rows);
        }

        $output .= '</table>';

        return $output;
    }


    /**
     * Generator Sub Footer
     * getRecord
     * @return mixed
     */
    public function getSubFooter($id, $cond = [], $rows)
    {
        $subFooter = IndikatorCell::where('id_indikator_laporan_kinerja', $id)->where('kategori_cell', IndikatorCell::FOOTER)->orderBy('id', 'asc')->get();

        $output = '';
        $output .= '<tr>';
        foreach ($subFooter as $f) {
            $output .= '<td rowspan="' . $f['rowspan'] . '" colspan="' . $f['colspan'] . '">' . $f['nama'] . '</td>';
        }
        $output .= '</tr>';

        return $output;
    }

    /**
     * Generator Footer
     *
     * @return mixed
     */
    public function getFooter($id, $dataFooter, $isSubFooter = false, $childHeader = [], $records = [])
    {
        $data = IndikatorCell::where('id_indikator_laporan_kinerja', $id)
            ->where('kategori_cell', IndikatorCell::FOOTER)->orderBy('id', 'asc')->where('apakah_sub_footer', $isSubFooter)->get()->toArray();

        $sum = $count = $countnotempty = $footers = [];
        foreach ($data as $row) {
            $footers[$row['row_to']][$row['column_to']] = $row;
        }

        $countHeader = count($childHeader);

        for ($no = 1; $no <= $countHeader; $no++) {
            $sum[$no] = 0;
            $count[$no] = 0;
            $countnotempty[$no] = 0;
            foreach ($footers as $rowTo => $row) {
                // cek if has child with column_to = $no
                if (!isset($row[$no])) {
                    $footers[$rowTo][$no] = null;
                }

                // order by row_to
                ksort($footers[$rowTo]);
            }
        }

        $output = '';
        if ($isSubFooter) {
            if (!empty($dataFooter)) {
                foreach ($dataFooter as $k => $row) {
                    if (!empty($row['sum'])) {
                        foreach ($row['sum'] as $keyColumn => $value) {
                            $sum[$keyColumn] += $value;
                            $count[$keyColumn] += $row['count'][$keyColumn];
                            $countnotempty[$keyColumn] += $row['countnotempty'][$keyColumn];
                        }
                    }
                }
            }

            $dataFooter = [];
            $dataFooter['sum'] = $sum;
            $dataFooter['count'] = $count;
            $dataFooter['countnotempty'] = $countnotempty;
        }

        if (!$isSubFooter)
            $output .= '<tfoot>';

        foreach ($footers as $rowTo => $rows) {

            $output .= '<tr>';
            foreach ($rows as $columnTo => $f) {
                $attr = [];
                $class = 'item-footer';

                if (empty($f)) {
                    if (!empty($childHeader[$columnTo]['isbuttonaction']))
                        $output .= '<td class="disabled" rowspan="1" colspan="1"></td>';
                    continue;
                }

                if (!empty($f['properti'])) {
                    $properti = explode(';;', $f['properti']);
                    foreach ($properti as $k => $v) {
                        [$name, $value] = explode('::', $v);
                        $attr[$name] = $value;
                    }
                }

                if (!empty($attr['validation_column'])) {

                    // search $record on array multidimensional 2 level
                    $data = array_filter($records, function ($item) use ($attr) {
                        return !empty($item[$attr['validation_column']]);
                    });

                    $customFooter = [];
                    $customFooter['sum'] = 0;
                    $customFooter['count'] = 0;
                    $customFooter['countnotempty'] = 0;
                    foreach ($data as $k => $v) {
                        $numRow = 0;
                        foreach ($v as $key => $value) {
                            if ($columnTo == $key && ($childHeader[$key]['jenis_form'] == IndikatorKolom::NUMBER || $childHeader[$key]['jenis_form'] == IndikatorKolom::DECIMAL)) {
                                $numRow += $value;
                            }
                        }
                        $customFooter['sum'] += $numRow;
                        $customFooter['count'] += 1;
                    }

                    $dataFooter['sum'][$columnTo] = $customFooter['sum'];
                    $dataFooter['count'][$columnTo] = $customFooter['count'];
                }

                $text = '';
                $isDisabled = false;

                if ($f['properti'] && !empty($records) && !empty($attr['formula'])) {
                    $num = '';
                    $formulas = explode(' ', $attr['formula']);
                    foreach ($formulas as $k => $row) {
                        if ($k % 2) {
                            $num .= $row;
                        } else {
                            [$rowTo, $columnTo] = explode(':', $row);
                            $num .= ($records[$rowTo][$columnTo] ?? 0);
                        }
                    }

                    // FIXME : Belum nemu ide lagi selain eval
                    if (!empty($num)) {
                        $text = eval('return ' . $num . ';');
                    }
                } else if ($f['jenis_cell'] == IndikatorCell::SUM_COLUMN) {
                    $class .= ' txt-right sum';
                    $text = $dataFooter['sum'][$columnTo] ?? "";
                } else if ($f['jenis_cell'] == IndikatorCell::AVERAGE) {
                    $class .= ' txt-right average';
                    if ($dataFooter['count'][$columnTo] != 0)
                        $text = ($dataFooter['sum'][$columnTo] / ($dataFooter['count'][$columnTo]));
                    else
                        $text = 0;
                } else if ($f['jenis_cell'] == IndikatorCell::COUNT_DATA_COLUMN) {
                    $class .= ' txt-right count';
                    $text = $dataFooter['count'][$columnTo];
                } else if ($f['jenis_cell'] == IndikatorCell::COUNT_DATA_NOT_EMPTY_COLUMN) {
                    $class .= ' txt-right count-not-empty';
                    $text = $dataFooter['countnotempty'][$columnTo];
                } else if ($f['jenis_cell'] == IndikatorCell::SUM_ALL) {
                    $class .= ' txt-right sum-all';
                    if (
                        !empty($childHeader[$columnTo])
                        || $childHeader[$columnTo] == IndikatorKolom::JUMLAH
                        || $childHeader[$columnTo] == IndikatorKolom::RERATADATATIDAKKOSONG
                        || $childHeader[$columnTo] == IndikatorKolom::RERATASEMUDATA
                    ) {
                        // unset datafooter by child header
                        unset($dataFooter['sum'][$columnTo]);
                    }
                    $text = array_sum($dataFooter['sum']);
                } else if ($f['jenis_cell'] == IndikatorCell::DISABLED) {
                    $isDisabled = true;
                }

                $childHeader[$columnTo]['jenis_form']  = $childHeader[$columnTo]['jenis_form'] ?? IndikatorKolom::TEXT_BOX;
                if ($childHeader[$columnTo]['jenis_form'] == IndikatorKolom::NUMBER && !empty($text)) {
                    $text = round($text);
                } else if ($childHeader[$columnTo]['jenis_form'] == IndikatorKolom::DECIMAL && !empty($text)) {
                    $text = round($text, 2);
                }

                if ($isDisabled)
                    $class .= ' disabled';


                $operator = $f['jenis_cell'] == IndikatorCell::LABEL ? '' : ' = ';

                $output .= '<td class="' . $class . '" rowspan="' . $f['rowspan'] . '" colspan="' . $f['colspan'] . '">' . (!empty($f['nama']) ? $f['nama'] . $operator : '') . ' ' . $text . '</td>';
            }
            $output .= '</tr>';
        }

        if (!$isSubFooter)
            $output .= '</tfoot>';

        return $output;
    }

    /**
     * Generator Record
     *
     * @return mixed
     */
    public function getRecord($headerChild, $cond = [], $category = [], $customCell, $records = [], $isEditAll = false)
    {
        // get param _GET idrecord
        $idrecord = request()->get('id_record');

        // add default record for save
        if ((!empty($category) && empty($records) || !$cond['apakah_layout_fixed'] && empty($idrecord))) {
            $records[] = ['isdefault' => true];
        }

        // change to custom cell array with key ID|ROW_TO|COLUMN_TO
        $customCell = $customCell->mapWithKeys(function ($item, $key) {
            return [$item['row_to'] . '|' . $item['column_to'] => $item];
        });

        $output = '';
        $footer = [];
        $sum = $count = $countnotempty =  [];
        // prepare variable to count or sum
        for ($no = 1; $no <= count($headerChild); $no++) {
            $sum[$no] = 0;
            $count[$no] = 0;
            $countnotempty[$no] = 0;
        }

        if ($cond['apakah_layout_fixed']) {
            if ($category['firstKey'] == 1) {
                $rowTo =  $category['firstKey'];
            } else {
                $rowTo =  $category['lastKey'];
            }
        } else {
            $rowTo = 1;
        }

        $dataRecord = [];
        $defaultNumber = 0;
        foreach ($records as $keyRecord => $record) {
            if (!empty($record['isdefault']) && $cond['is_preview'] && !$cond['apakah_layout_fixed']) {
                if ($keyRecord == 0) {
                    $output .= '<tr>';
                    $output .= '<td style="text-align: center" class="txt-not-found" colspan="100%">Tidak ada data</td>';
                    $output .= '</tr>';
                }
                continue;
            }

            if (!empty($category) && isset($idrecord)) {
                [$parentRecord, $newIdrecord] = explode('-', $idrecord);

                if ($parentRecord == $category['key'] && $newIdrecord == $rowTo) {
                    $record['isediting'] = true;
                }
            } else {
                if ($rowTo == $idrecord && isset($idrecord)) {
                    $record['isediting'] = true;
                }
            }

            if (!empty($record['isediting']) || $isEditAll || (!empty($record['isdefault']) && !$cond['apakah_layout_fixed'])) {
                $isEdit = true;
            } else {
                $isEdit = false;
            }

            if ($cond['is_preview'] && $cond['apakah_layout_fixed'] && $keyRecord == 0) {
                $isEdit = false;
            }

            $isParent = false;
            if (!empty($category) && !empty($category['is_has_child']) && $cond['jenis_form'] == IndikatorLaporanKinerja::FORM_COLUMN) {
                $isEdit = false;
                $isParent = true;
            }


            $output .= '<tr>';
            $isHasColumnNo = false;
            $inputNumber = [];
            $s[] = $rowTo;
            foreach ($headerChild as $columnTo => $cell) {
                $customAttr = $customCell[$rowTo . '|' . $columnTo] ?? null;
                $label = !empty($customAttr['nama']) ? $customAttr['nama'] . ' = ' : null;
                $isDisabled = false;
                if (!empty($customAttr['jenis_form']) && $customAttr['jenis_form'] == IndikatorLaporanKinerja::FORM_ROW) {
                    $isDisabled = true;
                } else if (!empty($customAttr['jenis_cell']) && $customAttr['jenis_cell'] == IndikatorCell::DISABLED) {
                    $isDisabled = true;
                }

                if ($columnTo == 1 && strpos(Str::lower($cell['nama']), 'no') !== false) {
                    $number = '';
                    if (!empty($category) && isset($category['is_has_child'])) {
                        if (($category['is_has_child'] || $category['is_number_parent']) && $category['info_level'] == 0) {
                            $number = $category['number'];
                        } else if (!$category['is_number_parent'] && !$category['is_has_child'] && $cond['apakah_layout_fixed']) {
                            $number = $category['number'];
                        } else if (!$category['is_number_parent'] && !$category['is_has_child'] && !$cond['apakah_layout_fixed']) {
                            $defaultNumber++;
                            $number = $defaultNumber;
                        }
                    } else {
                        $defaultNumber++;
                        $number = $defaultNumber;
                    }

                    $output .= '<td ' . ($isDisabled ? "class='disabled'" : "") . '>' . $number . '</td>';
                    $isHasColumnNo = true;
                } else if (($cell['jenis_kolom'] == IndikatorKolom::RERATADATATIDAKKOSONG || $cell['jenis_kolom'] == IndikatorKolom::RERATASEMUDATA || $cell['jenis_kolom'] == IndikatorKolom::JUMLAH) && !$isParent) {
                    $class = null;
                    $params = [];
                    if (!empty($cell['parameter'])) {
                        $param = explode(';;', $cell['parameter']);
                        foreach ($param as $val) {
                            [$name, $value] = explode('::', $val);
                            $params[$name] = $value;
                        }
                    }

                    $rangeFrom = !empty($params['kolomawal']) ? $params['kolomawal'] : null;
                    $rangeTo = !empty($params['kolomakhir']) ? $params['kolomakhir'] : null;
                    $isSumAll = false;
                    if (empty($rangeFrom) && empty($rangeTo)) {
                        $isSumAll = true;
                    }

                    $avgNotNull = $countAvgNotNull = 0;
                    $avg = $countAvg = 0;
                    foreach ($inputNumber as $k => $v) {
                        if ((($rangeFrom <= $k && $k <= $rangeTo) || $isSumAll)) {
                            if (!empty($v)) {
                                $avgNotNull += $v;
                                $countAvgNotNull++;
                            }

                            $avg += $v;
                            $countAvg++;
                        }
                    }

                    if ($cell['jenis_kolom'] == IndikatorKolom::JUMLAH) {
                        $value = $avg;
                    } else if ($cell['jenis_kolom'] == IndikatorKolom::RERATADATATIDAKKOSONG) {
                        $value = ($countAvgNotNull != 0 ? ($avgNotNull / $countAvgNotNull) : 0);
                    } else {
                        $value = ($countAvg != 0 ? ($avg / $countAvg) : 0);
                    }

                    if (!empty($params['pengali']))
                        $value = ($value * (int) $params['pengali']);

                    if (!empty($params['pembagi'])) {
                        $value = ($value / (int) $params['pembagi']);
                    }

                    if (isset($value) && empty($record['isdefault']))
                        $countnotempty[$columnTo] += 1;

                    if (empty($record['isdefault']))
                        $count[$columnTo] += 1;

                    $dataRecord[$rowTo][$columnTo] = $value;
                    $sum[$columnTo] += ($value != '' ? $value : 0);

                    if ($cell['jenis_form'] == IndikatorKolom::NUMBER) {
                        $value = round($value);
                        $class .= ' txt-right';
                    } else if ($cell['jenis_form'] == IndikatorKolom::DECIMAL) {
                        $value = round($value, 2);
                        $class .= ' txt-right';
                    }

                    if ($isDisabled)
                        $class .= ' disabled';


                    $output .= '<td ' . (!empty($class) ? 'class=' . $class . '' : '') . ' >' . $label . $value . '</td>';
                } else if ((($columnTo == 1 && strpos(Str::lower($cell['nama']), 'no') === false) || $columnTo == 2 && $isHasColumnNo) && (($cond['jenis_form'] == IndikatorLaporanKinerja::FORM_COLUMN && $cond['apakah_menggunakan_kategori']) || $cond['apakah_menggunakan_ts']) && $keyRecord == 0 && !empty($category['nama'])) {
                    $label = $category['nama'];
                    if ($cond['apakah_menggunakan_ts'] && !empty($cond['year'])) {
                        $labels = explode('-', $label);
                        $labelts = null;
                        $ts = 0;
                        if (count($labels) > 1) {
                            list($labelts, $ts) = $labels;
                        } else {
                            $labelts = $labels[0];
                        }
                        $label = ($labelts == 'TS' ? Cstr::akademikYear((int) $cond['year'], (int) $ts) : $label);
                    }
                    $output .= '<td rowspan="' . count($records) . '">' . $label  . '</td>';
                } else if ($cell['jenis_kolom'] == IndikatorKolom::PENGISIAN && !$isDisabled) {
                    $class = null;
                    // cell merge label with custom cell
                    $cell['label'] = $label;
                    if (!empty($record['isdefault'])) {
                        $cell['isdefault'] = true;
                    }

                    $value = null;
                    if (!empty($record[$columnTo]) && empty($record['isdefault'])) {
                        $value = $record[$columnTo];
                    }

                    if (!empty($category)) {
                        $keyName = '[' . $category['key'] . '][' . $keyRecord . ']';
                    } else {
                        $keyName = '[' . $keyRecord . ']';
                    }

                    if ($cell['jenis_form'] == IndikatorKolom::NUMBER || $cell['jenis_form']  == IndikatorKolom::DECIMAL || $cell['jenis_form'] == IndikatorKolom::CHECKBOX) {
                        // cast to int
                        if (is_numeric($value)) {
                            $value = (int) $value;
                        }

                        // sum column
                        $sum[$columnTo] += (!empty($value) && is_numeric($value)) ? $value : 0;
                        if ($cell['jenis_form'] != IndikatorKolom::CHECKBOX) {
                            $class .= "txt-right";
                            $inputNumber[$columnTo] = $value;
                        }
                    }
                    $dataRecord[$rowTo][$columnTo] = $value;

                    if (isset($value) && empty($cell['isdefault']))
                        $countnotempty[$columnTo] += 1;

                    if (empty($cell['isdefault']))
                        $count[$columnTo] += 1;

                    if (!empty($cell['isdefault'])) {
                        $cell['nama'] = 'i_' . $columnTo . $keyName;
                    } else {
                        $cell['nama'] = 'u_' . $columnTo . $keyName;
                    }

                    $output .= '<td ' . (!empty($class) ? 'class=' . $class . '' : '') . '>' . $this->generateInput($cell, $value, $isEdit) . '</td>';
                } else if ($cell['jenis_kolom'] == IndikatorKolom::BUKANPENGISIAN && !empty($cell['isbuttonaction'])) {
                    $output .= '<td class="cell-action">';
                    if (!empty($category))
                        $id = $category['key'] . '-' . $rowTo;
                    else
                        $id = $rowTo;

                    if ((!empty($category) && empty($category['is_has_child']) || empty($category)) && !$cond['is_preview'])
                        $output .= $this->getActionButton($id, $record);
                    $output .= '</td>';
                } else {
                    if (!$cond['apakah_layout_fixed'] && IndikatorLaporanKinerja::FORM_COLUMN && ($columnTo == 1 && strpos(Str::lower($cell['nama']), 'no') === false))
                        continue;

                    $output .= '<td ' . ($isDisabled ? "class='disabled'" : "") . '></td>';
                }
            }

            $output .= '</tr>';
            $rowTo++;
        }

        $footer['sum'] = $sum;
        $footer['count'] = $count;
        $footer['countnotempty'] = $countnotempty;

        return [$output, $footer, $dataRecord];
    }

    /**
     * Generator action button
     *
     * @return mixed
     */
    public function getActionButton($rowPosition, $cell)
    {
        $btnSave = '<button type="submit" class="btn btn_outline btn_xs" data-type="saveip" data-act="' . (!empty($cell['isediting']) ? "u" : "i") . '" data-id="' . $rowPosition . '" >
                        <span class="icon icon-check-solid"></span>
                    </button>';

        $btnCancel = '<button type="button" class="btn btn_outline btn_xs" data-type="cancelip">
                        <span class="icon icon-x-mark-solid"></span>
                    </button>';

        $btnEdit = '<button type="button" class="btn btn_outline btn_xs" data-type="editip" data-id="' . $rowPosition . '">
                        <span class="icon icon-pencil-solid"></span>
                    </button>';

        $btnDelete = '<button type="button" class="btn btn_outline btn_xs" data-type="deleteip" data-id="' . $rowPosition . '">
                        <span class="icon icon-trash-solid"></span>
                        </button>';

        $btn = '';
        if (!empty($cell['isediting'])) {
            $btn .= $btnSave;
            $btn .= $btnCancel;
        } else if (!empty($cell['isdefault'])) {
            $btn .= $btnSave;
        } else {
            $btn .= $btnEdit;
            $btn .= $btnDelete;
        }


        $output = '<div class="dropdown-group">';
        $output .= $btn;
        $output .= '</div>';

        return $output;
    }

    /**
     * Generator Input
     *
     * @return array
     */
    public function generateInput($columns, $value = null, $isEdit = false, $class = null, $add = null)
    {
        $type = $columns['jenis_form'];
        $output = '';
        switch ($type) {
            case IndikatorKolom::TEXT_BOX:
                $output = UI::createInputText($columns, $value, $isEdit);
                break;
            case IndikatorKolom::DECIMAL:
                $output = UI::createInputDecimal($columns, $value, $isEdit);
                break;
            case IndikatorKolom::NUMBER:
                $output = UI::createInputNumber($columns, $value, $isEdit);
                break;
            case IndikatorKolom::CHECKBOX:
                $output = UI::createInputCheckbox($columns, $value, $isEdit);
                break;
            case IndikatorKolom::TEXTAREA:
                $output = UI::createInputTextarea($columns, $value, $isEdit);
                break;
            case IndikatorKolom::DATE:
                $output = UI::createInputDate($columns, $value, $isEdit);
                break;
            case IndikatorKolom::DROPDOWN:
                $output = UI::createSelect($columns, $value, $isEdit);
                break;
            default:
                $output = '-';
                break;
        }

        return $output;
    }

    /**
     * Generator Cell
     *
     * @return array
     */
    public function generateCell(int $id, $columns)
    {
        $cell = IndikatorCell::where('id_indikator_laporan_kinerja', $id)
            ->where('kategori_cell', IndikatorCell::CELL)->get();

        return $cell;
    }

    /**
     * Generator columns
     *
     * @return array
     */
    public function generateColumns(int $id, $withAction = false, $cond = [])
    {
        $columns = IndikatorKolom::where('id_indikator_laporan_kinerja', $id)->orderBy('info_left', 'asc')->get()->toArray();

        $i = 1;
        $arrData = array();
        $arrColumns = array();
        $arrColumnsDetail = array();
        foreach ($columns as $row) {
            $level = $row['info_level'];

            if (empty($arrColumns[$level])) {
                $arrColumns[$level]['baris'] = $level;
            }

            $arrColumnsDetail[$level][] = $row;
            $arrColumns[$level]['items'] = $arrColumnsDetail[$level];

            if ($row['colspan'] == 1 || $row['colspan'] == null) {
                $arrData[$i] = $row;
                $i++;
            }
        }
        if ($withAction && !empty($columns)) {
            $countChild = count($arrColumns);
            // get first key of array $arrColumns
            $fKey = array_key_first($arrColumns);

            $action = [
                'nama' => 'Aksi',
                'jenis_kolom' => IndikatorKolom::BUKANPENGISIAN,
                'posisi_kolom' => IndikatorKolom::HORIZONTAL,
                'isbuttonaction' => true,
                'info_level' => 0,
                'rowspan' => $countChild,
                'colspan' => 1,
            ];

            $arrData[] = $action;
            $arrColumns[$fKey]['items'][] = $action;
        }
        $output = '<thead>';

        foreach ($arrColumns as $infoLevel => $column) {
            $output .= '<tr>';
            foreach ($column['items'] as $k => $v) {
                if (strpos(Str::lower($v['nama']), 'ts') !== false && isset($cond['year'])) {
                    $v['nama'] = self::changeToTS($v['nama'], $cond['year']);
                }
                $class = $v['posisi_kolom'] == IndikatorKolom::VERTICAL ? 'vertical' : 'horizontal';
                $output .= '<th rowspan="' . ($v['rowspan'] ?? 1) . '" colspan="' . ($v['colspan'] ?? 1) . '" class="' . $class . '">' . $v['nama'] . '</th>';
            }
            $output .= '</tr>';
        }
        // penomoran
        $output .= '<tr>';

        foreach ($arrData as $k => $v) {
            $output .= '<th>' . $k . '</th>';
        }

        $output .= '</thead>';

        return [$output, $arrData];
    }

    public static function changeToTS($label, $year)
    {
        $labels = explode('-', $label);
        $labelts = null;
        $ts = 0;
        if (count($labels) > 1) {
            list($labelts, $ts) = $labels;
        } else {
            $labelts = $labels[0];
        }
        $label = ($labelts == 'TS' ? Cstr::akademikYear((int) $year, (int) $ts) : $label);
        return $label;
    }

    /**
     * Generator Row
     *
     * @return collection
     */
    public function generateRow(int $id)
    {
        $rows = IndikatorBaris::where('id_indikator_laporan_kinerja', $id)->orderBy('info_left', 'asc')->get();

        // tree
        $tree = [];
        $isHasParent = false;

        $rows = $rows->toArray();
        // make tree recursive
        foreach ($rows as $k => $v) {
            $tree[$v['id']] = $v;
            $tree[$v['id']]['is_has_child'] = false;
            if ($v['id_parent'] != null) {
                $isHasParent = true;
            }
        }

        foreach ($tree as $k => $v) {
            if ($v['id_parent'] != null) {
                $tree[$v['id_parent']]['is_has_child'] = true;
            }

            $tree[$v['id']]['is_number_parent'] = $isHasParent;
        }

        return collect(array_values($tree));
    }
}
