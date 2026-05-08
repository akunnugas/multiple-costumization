<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Helpers\AccreditationAPI;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\PenilaianKlaster;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\IndikatorCell;
use Modules\SPMI\Models\IndikatorKolom;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\IndikatorBaris;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Helpers\AccreditationSync;

class AccreditationSyncManagementService
{
    /**
     * Sync API Accreditation Agency.
     */
    public static function syncAccreditationAgencies()
    {
        $modelSPMI = LembagaAkreditasi::class;

        $mapping = [];
        $mapping['idlembaga'] = 'kode_lembaga';
        $mapping['lembagaakreditasi'] = 'nama_lembaga';
        $mapping['namasingkat'] = 'nama_singkat_lembaga';

        $accreditationSync = new AccreditationSync;
        $agenciesSPME = $accreditationSync->getAgency();

        // Save Mapping
        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $agenciesSPME['data'], accreditationSync::LembagaAkreditasi);

        return [$err, $msg];
    }

    /**
     * Sync API Accreditation Books.
     */
    public static function syncAkreditasiBuku()
    {
        $modelSPMI = AkreditasiBuku::class;

        $mapping = [];
        $mapping['kodejenisbuku'] = 'kode_buku';
        $mapping['jenisbuku'] = 'nama_buku';

        $accreditationSync = new AccreditationSync;
        $booksSPME = $accreditationSync->getBook();

        // Save Mapping
        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $booksSPME['data'], accreditationSync::AkreditasiBuku);

        return [$err, $msg];
    }

    /**
     *
     * Sync API Filling Guide.
     *
     */
    public static function syncPengisianPanduan($idlembaga = null, $arrKodeBanpt = [])
    {
        $modelSPMI = PengisianPanduan::class;

        $mapping = [];
        $mapping['kodebanpt'] = 'kode_pengisian_panduan';
        $mapping['namapanduan'] = 'nama_pengisian_panduan';
        $mapping['namasingkat'] = 'nama_singkat';
        $mapping['tgledisi'] = 'tanggal_edisi';
        $mapping['tglberlaku'] = 'tanggal_efektif';
        $mapping['tglberakhir'] = 'tanggal_kadaluwarsa';
        $mapping['issapto'] = 'apakah_sapto';
        $mapping['isaktif'] = 'apakah_aktif';
        $mapping['keterangan'] = 'deskripsi';
        $mapping['idedisi'] = 'tipe_edisi';
        $mapping['idlembaga'] = 'id_lembaga_akreditasi';
        $mapping['idlevelakses'] = 'kode_level_akses';
        $mapping['idbukuakreditasi'] = 'id_akreditasi_buku';
        $mapping['idstandarakreditasi'] = 'id_jenis_standar';
        $mapping['idled'] = 'id_pengisian_panduan|kodebanpt';

        $accreditationSync = new AccreditationSync;
        $guidesSPME = $accreditationSync->getGuide($idlembaga, $arrKodeBanpt);

        $ref = [];
        $ref['id_lembaga_akreditasi'] = LembagaAkreditasi::all()->pluck('id', 'kode_lembaga')->toArray();
        $ref['id_akreditasi_buku'] = AkreditasiBuku::all()->pluck('id', 'kode_buku')->toArray();
        $ref['id_jenis_standar'] = JenisStandar::all()->pluck('id', 'kode_jenis_standar')->toArray();

        foreach ($guidesSPME['data'] as $key => $value) {
            $guidesSPME['data'][$key]['idedisi'] = $value['idedisi'] == 'ED' ? 'se' : 'pr';
        }

        // Save Mapping
        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $guidesSPME['data'], accreditationSync::PengisianPanduan, $ref);

        return [$err, $msg];
    }

    /**
     * Sync API Indicator Performance Report.
     */
    public static function syncIndicatorPerformanceReports($kodePengisianPanduan)
    {
        $modelSPMI = IndikatorLaporanKinerja::class;

        $mapping = [];
        $mapping['kodebanpt'] = 'id_pengisian_panduan';
        $mapping['nobutir'] = 'nomor_indikator';
        $mapping['namabutir'] = 'nama_indikator_laporan_kinerja';
        $mapping['uraian'] = 'deskripsi';
        $mapping['keterangan'] = 'informasi';
        $mapping['jenislayout'] = 'jenis_layout';
        $mapping['idtarikdata'] = 'sumber_data.IN';
        $mapping['sumberdata'] = 'deskripsi_sumber_data';
        $mapping['parentled'] = 'apakah_import_excel';
        $mapping['istampilkan'] = 'dapat_lihat_nama_pada_laporan';
        $mapping['islabel'] = 'apakah_parent';
        $mapping['isaktif'] = 'apakah_aktif';
        $mapping['parentnobutir'] = 'id_parent|nobutir';
        $mapping['level'] = 'info_level';
        $mapping['infoleft'] = 'info_left';
        $mapping['inforight'] = 'info_right';

        // column child
        $mapping['kolom'] = 'arrchildren';
        $mapping['baris'] = 'arrchildren';
        $mapping['labelcell'] = 'arrchildren';
        $mapping['footer'] = 'arrchildren';

        // add default mapping
        $mapping['jenis_form'] = 'jenis_form.FR';
        $mapping['apakah_layout_fixed'] = 'apakah_layout_fixed.false';
        $mapping['apakah_menggunakan_kategori'] = 'apakah_menggunakan_kategori.false';
        $mapping['apakah_memasukkan_kategori_manual'] = 'apakah_memasukkan_kategori_manual.false';
        $mapping['apakah_menggunakan_ts'] = 'apakah_menggunakan_ts.false';
        $mapping['apakah_data_default'] = 'apakah_data_default.true';
        $mapping['apakah_subfooter'] = 'apakah_subfooter.false';

        // get data filling guide
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)->where('kode_pengisian_panduan', $kodePengisianPanduan)->pluck('kode_pengisian_panduan', 'id')->toArray();

        $ref = [];
        $ref['id_pengisian_panduan'] = PengisianPanduan::all()->pluck('id', 'kode_pengisian_panduan')->toArray();

        $defaultValue = [];
        $defaultValue['apakah_data_default'] = true;

        $accreditationSync = new AccreditationSync;
        $params = [];
        $err = false;
        $msg = null;

        foreach ($pengisianPanduan as $key => $value) {
            $params['id_pengisian_panduan'] = $value;
            $indicatorPerformanceReportsSPME = $accreditationSync->getIndicator($value);

            foreach ($indicatorPerformanceReportsSPME['data'] as $key => $v) {
                if (!empty($v['idjenisform'])) {
                    $addColumns = self::changeFormType($v['idjenisform']);
                    $indicatorPerformanceReportsSPME['data'][$key] = array_merge($indicatorPerformanceReportsSPME['data'][$key], $addColumns);
                    $indicatorPerformanceReportsSPME['data'][$key]['islabel'] = (empty($v['islabel']) ? '0' : '1');
                }
            }

            // Save Mapping
            list($err, $msg, $child) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $indicatorPerformanceReportsSPME['data'], accreditationSync::INDICATORPERFORMANCEREPORT, $ref, $params, $defaultValue);

            if ($child && !$err) {
                list($err, $msg) = self::syncChildIndicator($child);
            }

            if ($err) {
                break;
            }
        }

        return [$err, $msg];
    }

    public static function syncButirPanduanNew($kodePengisianPanduan)
    {
        $modelSPMI = IndikatorLaporanKinerja::class;

        // get data filling guide
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)
            ->where('kode_pengisian_panduan', $kodePengisianPanduan)->first();

        if (empty($pengisianPanduan)) {
            return false;
        }

        $accreditationSync = new AccreditationSync;

        $indicatorPerformanceReportsSPME = $accreditationSync->getIndicator($pengisianPanduan->kode_pengisian_panduan);

        foreach ($indicatorPerformanceReportsSPME['data'] as $key => $v) {
            if (!empty($v['idjenisform'])) {
                $addColumns = self::changeFormType($v['idjenisform']);
                $indicatorPerformanceReportsSPME['data'][$key] = array_merge($indicatorPerformanceReportsSPME['data'][$key], $addColumns);
            }
        }

        foreach ($indicatorPerformanceReportsSPME['data'] as $data) {
            $parent = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                ->where('nomor_indikator', $data['parentnobutir'])->first();
            $payload = array_merge([
                'id_pengisian_panduan' => $pengisianPanduan->id,
                'nomor_indikator' => $data['nobutir'],
                'nama_indikator_laporan_kinerja' => $data['namabutir'],
                'deskripsi' => $data['uraian'],
                'informasi' => $data['keterangan'],
                'jenis_layout' => $data['jenislayout'],
                'sumber_data' => $data['idtarikdata'] ?? IndikatorLaporanKinerja::DATA_MANUAL_INPUT,
                'deskripsi_sumber_data' => $data['sumberdata'],
                'apakah_import_excel' => null,
                'dapat_lihat_nama_pada_laporan' => $data['istampilkan'],
                'apakah_aktif' => $data['isaktif'],
                'id_parent' => $parent ? $parent->id : null,
                'info_level' => $data['level'],
                'info_left' => $data['infoleft'],
                'info_right' => $data['inforight'],
                'apakah_data_default' => true,
                'apakah_memasukkan_kategori_manual' => false,
                'apakah_subfooter' => false,
            ], self::changeFormType($data['idjenisform']));

            $modelSPMI::updateOrCreate(
                ['id_pengisian_panduan' => $pengisianPanduan->id, 'nomor_indikator' => $data['nobutir']],
                $payload
            );
        }

        return true;
    }

    public static function syncButirLEDPanduanNew($kodePengisianPanduan)
    {
        $modelSPMI = IndikatorEvaluasiDiri::class;

        // get data filling guide
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::SELF_EVALUATION)
            ->where('kode_pengisian_panduan', $kodePengisianPanduan)->first();

        if (empty($pengisianPanduan)) {
            return false;
        }

        $accreditationSync = new AccreditationSync;

        $indicatorPerformanceReportsSPME = $accreditationSync->getIndicatorLED($pengisianPanduan->kode_pengisian_panduan);

        foreach ($indicatorPerformanceReportsSPME['data'] as $data) {
            $parent = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                ->where('nomor_indikator', $data['parentled'])->first();
            $payload = [
                'id_pengisian_panduan' => $pengisianPanduan->id,
                'nomor_indikator' => $data['kodeled'],
                'nama_indikator_evaluasi_diri' => $data['namaled'],
                'deskripsi' => $data['uraian'],
                'apakah_komentar' => $data['iskomentar'],
                'apakah_key_point' => $data['isringkasan'],
                'apakah_aktif' => $data['isaktif'],
                'apakah_parent' => $data['islabel'],
                'id_parent' => $parent ? $parent->id : null,
                'info_level' => $data['level'],
                'info_left' => $data['infoleft'],
                'info_right' => $data['inforight'],
                'apakah_data_default' => true,
            ];

            $modelSPMI::updateOrCreate(
                ['id_pengisian_panduan' => $pengisianPanduan->id, 'nomor_indikator' => $data['kodeled']],
                $payload
            );
        }

        return true;
    }

    /**
     * Sync all Child Indicator.
     */
    public static function syncChildIndicator($childs)
    {
        foreach ($childs as $key => $value) {
            if ($key == 'kolom') {
                list($err, $msg) = self::syncIndicatorColumns($value);
            } else if ($key == 'baris') {
                list($err, $msg) = self::syncIndikatorBaris($value);
            } else if ($key == 'labelcell') {
                list($err, $msg) = self::syncIndicatorLabelCells($value);
            } else if ($key == 'footer') {
                list($err, $msg) = self::syncIndicatorFooters($value);
            }

            if ($err)
                break;
        }

        if (empty($err)) {
            self::updateSequence('spmi.indikator_kolom');
            self::updateSequence('spmi.indikator_baris');
        }

        return [$err, $msg];
    }

    /**
     * Sync API Indicator Column.
     */
    public static function syncIndicatorColumns($datas)
    {
        $modelSPMI = IndikatorKolom::class;

        $mapping = [];
        $mapping['idkolom'] = 'id';
        $mapping['id_indikator_laporan_kinerja'] = 'id_indikator_laporan_kinerja';
        $mapping['idparent'] = 'id_parent';
        $mapping['namakolom'] = 'nama';
        $mapping['idtipekolom'] = 'jenis_form';
        $mapping['idjeniskolom'] = 'jenis_kolom';
        $mapping['propertikolom'] = 'properti';
        $mapping['option_dropdown'] = 'option_dropdown';
        $mapping['colspan'] = 'colspan';
        $mapping['rowspan'] = 'rowspan';
        $mapping['isvertikal'] = 'posisi_kolom';
        $mapping['istampilkan'] = 'apakah_terlihat';
        $mapping['infoleft'] = 'info_left';
        $mapping['inforight'] = 'info_right';
        $mapping['level'] = 'info_level';
        $mapping['paramjenis'] = 'parameter';

        $indicaorColumnSPME = [];
        foreach ($datas as $keyData => $row) {
            if (!empty($row)) {
                foreach ($row as $keyValue => $value) {
                    $addParent = $keyData + 1;
                    $value['isvertikal'] = (!empty($value['isvertikal']) ? 'V' : 'H');
                    if ($value['idtipekolom'] == IndikatorKolom::DROPDOWN) {
                        // explode option dropdown and search option
                        $optionDropdown = explode(';', $value['propertikolom']);
                        $result = preg_grep('~' . 'option::' . '~', $optionDropdown);
                        // explode option
                        $option = explode('::', $result[0]);
                        $value['option_dropdown'] = $option[1];
                        // delete $result on option dropdown
                        unset($optionDropdown[array_search($result[0], $optionDropdown)]);
                        $value['propertikolom'] = (!empty($optionDropdown) ? implode(';;', $optionDropdown) : null);
                    } else if ($value['idtipekolom'] == IndikatorKolom::NUMBER) {
                        $value['idtipekolom'] = IndikatorKolom::DECIMAL;
                    }

                    $indicaorColumnSPME[] = array_merge($value, ['id_indikator_laporan_kinerja' => $addParent]);
                }
            } else {
                $indicaorColumnSPME[] = null;
            }
        }

        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $indicaorColumnSPME, accreditationSync::INDICATORCOLUMN);

        return [$err, $msg];
    }

    /**
     * Sync API Indicator Row.
     */
    public static function syncIndikatorBaris($datas)
    {
        $modelSPMI = IndikatorBaris::class;

        $mapping = [];
        $mapping['idbaris'] = 'id';
        $mapping['idparent'] = 'id_parent';
        $mapping['namabaris'] = 'nama';
        $mapping['idjenisnomor'] = 'jenis_penomoran';
        $mapping['infoleft'] = 'info_left';
        $mapping['inforight'] = 'info_right';
        $mapping['level'] = 'info_level';
        $mapping['id_indikator_laporan_kinerja'] = 'id_indikator_laporan_kinerja';

        $indicaorRowPME = [];
        foreach ($datas as $keyData => $row) {
            if (!empty($row)) {
                foreach ($row as $keyValue => $value) {
                    $addParent = $keyData + 1;
                    $indicaorRowPME[] = array_merge($value, ['id_indikator_laporan_kinerja' => $addParent]);
                }
            } else {
                $indicaorRowPME[] = null;
            }
        }

        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $indicaorRowPME, accreditationSync::INDICATORROW);

        return [$err, $msg];
    }

    /**
     * Sync API Indicator Label Cell.
     */
    public static function syncIndicatorLabelCells($datas)
    {
        $modelSPMI = IndikatorCell::class;

        $mapping = [];
        $mapping['bariske'] = 'row_to';
        $mapping['kolomke'] = 'column_to';
        $mapping['namalabel'] = 'nama';
        $mapping['idjenis'] = 'jenis_cell';
        $mapping['id_indikator_laporan_kinerja'] = 'id_indikator_laporan_kinerja';

        $indicaorCellSPME = [];
        foreach ($datas as $keyData => $row) {
            if (!empty($row)) {
                foreach ($row as $keyValue => $value) {
                    $addParent = $keyData + 1;
                    $indicaorCellSPME[] = array_merge($value, ['id_indikator_laporan_kinerja' => $addParent]);
                }
            } else {
                $indicaorCellSPME[] = null;
            }
        }

        $addColumns = [];
        $addColumns['kategori_cell'] = IndikatorCell::CELL;
        $addColumns['posisi_label'] = IndikatorCell::LEFT;

        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $indicaorCellSPME, accreditationSync::INDICATORCELL, defaultValue: $addColumns);

        return [$err, $msg];
    }

    /**
     * Sync API Indicator Footer.
     */
    public static function syncIndicatorFooters($datas)
    {
        $modelSPMI = IndikatorCell::class;

        $mapping = [];
        $mapping['bariske'] = 'row_to';
        $mapping['kolomke'] = 'column_to';
        $mapping['namafooter'] = 'nama';
        $mapping['idjenis'] = 'jenis_cell';
        $mapping['align'] = 'posisi_label';
        $mapping['colspan'] = 'colspan';
        $mapping['rowspan'] = 'rowspan';
        $mapping['istampilkan'] = 'dapat_dilihat';
        $mapping['id_indikator_laporan_kinerja'] = 'id_indikator_laporan_kinerja';

        $mappingPositionSPME = self::mappingPositionSPME();
        $indicaorCellSPME = [];
        foreach ($datas as $keyData => $row) {
            if (!empty($row)) {
                foreach ($row as $keyValue => $value) {
                    $addParent = $keyData + 1;
                    if (!empty($value['align'])) {
                        $value['align'] = $mappingPositionSPME[$value['align']];
                    }
                    $indicaorCellSPME[] = array_merge($value, ['id_indikator_laporan_kinerja' => $addParent]);
                }
            } else {
                $indicaorCellSPME[] = null;
            }
        }

        $addColumns = [];
        $addColumns['kategori_cell'] = IndikatorCell::FOOTER;

        list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $indicaorCellSPME, accreditationSync::INDICATORCELL, defaultValue: $addColumns);

        return [$err, $msg];
    }

    /**
     * Change Form Type
     */
    public static function changeFormType($value)
    {
        $data = [];
        switch ($value) {
            case AccreditationSync::FORM_FBC:
                $data['jenis_form'] = 'FR';
                $data['apakah_menggunakan_kategori'] = true;
                break;
            case AccreditationSync::FORM_FCC:
                $data['jenis_form'] = 'FC';
                $data['apakah_menggunakan_kategori'] = true;
                break;
            case AccreditationSync::FORM_FCM:
                $data['jenis_form'] = 'FC';
                $data['apakah_menggunakan_kategori'] = true;
                $data['apakah_memasukkan_kategori_manual'] = true;
                break;
            case AccreditationSync::FORM_FBR:
            case AccreditationSync::FORM_FFR:
                $data['jenis_form'] = 'FC';
                $data['apakah_layout_fixed'] = true;
                $data['apakah_menggunakan_kategori'] = true;
                break;
            case AccreditationSync::FORM_FRC:
                $data['jenis_form'] = 'FC';
                $data['apakah_menggunakan_kategori'] = true;
                $data['apakah_layout_fixed'] = true;
                break;
            case AccreditationSync::FORM_FTA:
                $data['jenis_form'] = 'FC';
                $data['apakah_menggunakan_kategori'] = true;
                $data['apakah_menggunakan_ts'] = true;
                $data['apakah_layout_fixed'] = true;
                break;
            case AccreditationSync::FORM_FKM:
                $data['jenis_form'] = 'FC';
                $data['apakah_menggunakan_kategori'] = true;
                $data['apakah_menggunakan_ts'] = true;
                $data['apakah_layout_fixed'] = true;
                break;
            default:
                $data['jenis_form'] = 'FR';
                break;
        }

        return $data;
    }

    /**
     * mappingPositionSPME
     */
    public static function mappingPositionSPME()
    {
        return [
            'left' => IndikatorCell::LEFT,
            'center' => IndikatorCell::CENTER,
            'right' => IndikatorCell::RIGHT,
        ];
    }

    /**
     * Sync API Indicator self evaluation (LED)
     */
    public static function syncIndicatorSelfEvaluation($kodePengisianPanduan)
    {
        $modelSPMI = IndikatorEvaluasiDiri::class;

        $mapping = [];
        $mapping['kodeled'] = 'nomor_indikator';
        $mapping['kodebanpt'] = 'id_pengisian_panduan';
        $mapping['namaled'] = 'nama_indikator_evaluasi_diri';
        $mapping['uraian'] = 'deskripsi';
        $mapping['iskomentar'] = 'apakah_komentar';
        $mapping['isringkasan'] = 'apakah_key_point';
        $mapping['isaktif'] = 'apakah_aktif';
        $mapping['islabel'] = 'apakah_parent';
        $mapping['parentled'] = 'id_parent|kodeled';
        $mapping['level'] = 'info_level';
        $mapping['infoleft'] = 'info_left';
        $mapping['inforight'] = 'info_right';

        // get data filling guide
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::SELF_EVALUATION)->where('kode_pengisian_panduan', $kodePengisianPanduan)->pluck('kode_pengisian_panduan', 'id')->toArray();

        $ref = [];
        $ref['id_pengisian_panduan'] = PengisianPanduan::all()->pluck('id', 'kode_pengisian_panduan')->toArray();

        $defaultValue = [];
        $defaultValue['apakah_data_default'] = true;

        $accreditationSync = new AccreditationSync;
        $params = [];
        $err = false;
        $msg = null;
        // each filling guide
        foreach ($pengisianPanduan as $key => $value) {
            $params['id_pengisian_panduan'] = $value;
            $indicatorPerformanceReportsSPME = $accreditationSync->getIndicatorLED($value);

            // Save Mapping
            list($err, $msg) = AccreditationAPI::saveMapping($modelSPMI, $mapping, $indicatorPerformanceReportsSPME['data'], accreditationSync::INDICATORSELFEVALUATION, $ref, $params, $defaultValue);

            if ($err) {
                break;
            }
        }

        return [$err, $msg];
    }


    /**
     * Sync API Accreditation Books.
     */
    public static function syncPenilaianPanduan()
    {
        $selectedGuideCode = [
            'IAPS-S1',
        ];

        $accreditationSync = new AccreditationSync;
        $assessmentGuides = $accreditationSync->getPenilaianPanduan();

        // Ambil Panduan sesuai dengan kode yang dipilih
        $assessmentGuides = array_filter($assessmentGuides['data'], fn ($item) => in_array($item['kodespmi'], $selectedGuideCode));
        $assessmentGuides = array_values($assessmentGuides);

        // Ambil LK dan LED
        $mapIndicatorPerformanceCode = array_map(fn ($item) => $item['kodelk'], $assessmentGuides);
        $mapIndicatorPerformanceCode = array_values(array_unique($mapIndicatorPerformanceCode));
        $mapSelfEvaluationCode = array_map(fn ($item) => $item['kodeled'], $assessmentGuides);
        $mapSelfEvaluationCode = array_values(array_unique($mapSelfEvaluationCode));

        $pengisianPanduanArray = PengisianPanduan::whereIn('kode_pengisian_panduan', [
            ...$mapIndicatorPerformanceCode,
            ...$mapSelfEvaluationCode,
        ])->pluck('id', 'kode_pengisian_panduan')->toArray();

        // Ambil Jenjang
        $mapDegreeCode = array_map(fn ($item) => $item['idjenjang'], $assessmentGuides);
        $mapDegreeCode = array_values(array_unique($mapDegreeCode));
        $degrees = JenjangPendidikan::whereIn('kode_jenjang', $mapDegreeCode)->pluck('id', 'kode_jenjang')->toArray();

        // Ambil standar akreditasi
        $mapJenisStandarCode = array_map(fn ($item) => $item['idstandarakreditasi'], $assessmentGuides);
        $mapJenisStandarCode = array_values(array_unique($mapJenisStandarCode));
        $standardTypes = JenisStandar::whereIn('kode_jenis_standar', $mapJenisStandarCode)->pluck('id', 'kode_jenis_standar')->toArray();

        $data = [];
        foreach ($assessmentGuides as $item) {
            $data[] = [
                'kode_penilaian_panduan' => $item['kodespmi'],
                'nama_penilaian_panduan' => $item['namapanduan'],
                'nama_singkat' => $item['namasingkat'],
                'id_laporan_kinerja' => $pengisianPanduanArray[$item['kodelk']] ?? null,
                'id_panduan_evaluasi_diri' => $pengisianPanduanArray[$item['kodeled']] ?? null,
                'tanggal_edisi' => $item['tgledisi'],
                'id_jenjang_pendidikan' => $degrees[$item['idjenjang']] ?? null,
                'id_jenis_standar' => $standardTypes[$item['idstandarakreditasi']] ?? null,
                'deskripsi' => $item['keterangan'],
                'apakah_aktif' => $item['isaktif'],
                'dapat_lihat_skor_akhir' => $item['isskorakhir'],
                'waktu_dibuat' => Carbon::now(),
                'waktu_diubah' => Carbon::now(),
            ];
        }

        $err = false;
        $msg = 'Success to sync data from SPMI API. And Sync data saved';

        DB::beginTransaction();

        try {
            PenilaianPanduan::insert($data);
        } catch (\Exception $e) {
            DB::rollback();

            $err = true;
            $msg = $e->getMessage();
        }

        DB::commit();

        return [$err, $msg];
    }

    public static function syncPenilaianMatrix()
    {
        $msg = 'Success to sync data from SPMI API. And Sync data saved';
        $err = false;

        $accreditationSync = new AccreditationSync;
        $assessmentGuideData = PenilaianPanduan::get();
        $assessmentGuideList = $assessmentGuideData->pluck('id', 'kode_penilaian_panduan')->toArray();

        $mapField = [
            'nobutirspmi' => 'nomor_penilaian',
            'kodespmi' => 'id_penilaian_panduan',
            'parentbutirspmi' => 'id_parent',
            'namabutirspmi' => 'pertanyaan_penilaian',
            'bobot' => 'bobot_penilaian',
            'keterangan' => 'deskripsi',
            'idkategori' => 'kategori_penilaian',
            'idklaster' => 'id_penilaian_klaster',
            'kodestandar' => 'id_akreditasi_standar',
            'idsyaratakreditasi' => 'syarat_terakreditasi',
            'idstandarpt' => 'standar_perguruan_tinggi',
            'isaktif' => 'apakah_aktif',
            'idjenispenilaian' => 'jenis_penilaian',
            'idsumberreferensi' => 'referensi_penilaian',
            'istampilkandinilaiakhir' => 'apakah_nilai_ditampilkan',
            'butirlk' => 'indikator_laporan_kinerja',
            'butirled' => 'indikator_evaluasi_diri',
            'level' => 'info_level',
            'infoleft' => 'info_left',
            'inforight' => 'info_right',
            'pilihanskor' => 'scores',
        ];

        foreach ($assessmentGuideList as $assessmentGuideCode => $assessmentGuideId) {
            $savedMatrices = [];

            DB::beginTransaction();

            $assessmentMatrices = $accreditationSync->getAssessmentMatrices($assessmentGuideCode);
            $assessmentMatrices = $assessmentMatrices['data'] ?? [];

            // Referensi Kluster Penilaian
            $mappedPenilaianKlaster = array_map(fn ($item) => $item['idklaster'], $assessmentMatrices);
            $refPenilaianKlaster = PenilaianKlaster::whereIn('kode_klaster', $mappedPenilaianKlaster)->pluck('id', 'kode_klaster')->toArray();

            // Referensi butir LK
            $indicatorPerformanceGuide = $assessmentGuideData->where('kode_penilaian_panduan', $assessmentGuideCode)->first()->id_laporan_kinerja;
            $mappedIndicatorPerformanceReport = array_map(fn ($item) => $item['butirlk'], $assessmentMatrices);
            $mappedIndicatorPerformanceReport = array_filter($mappedIndicatorPerformanceReport, fn ($item) => !empty($item));
            $mappedIndicatorPerformanceReport = array_unique(Arr::flatten($mappedIndicatorPerformanceReport));
            $refIndicatorPerformanceReport = IndikatorLaporanKinerja::where('id_pengisian_panduan', $indicatorPerformanceGuide)->whereIn('nomor_indikator', $mappedIndicatorPerformanceReport)->pluck('id', 'nomor_indikator')->toArray();

            // Referensi butir LED
            $indicatorSelfEvaluationGuide = $assessmentGuideData->where('kode_penilaian_panduan', $assessmentGuideCode)->first()->id_panduan_evaluasi_diri;
            $mappedIndicatorSelfEvaluation = array_map(fn ($item) => $item['butirled'], $assessmentMatrices);
            $mappedIndicatorSelfEvaluation = array_filter($mappedIndicatorSelfEvaluation, fn ($item) => !empty($item));
            $mappedIndicatorSelfEvaluation = array_unique(Arr::flatten($mappedIndicatorSelfEvaluation));
            $refIndicatorSelfEvaluation = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $indicatorSelfEvaluationGuide)
                ->whereIn('nomor_indikator', $mappedIndicatorSelfEvaluation)->pluck('id', 'nomor_indikator')->toArray();

            // Ambil standar akfreditasi sesuai jenis standar panduan
            $mappedStandarAkreditasi = array_map(
                function ($item) {
                    if (substr($item['kodestandar'], -1) == '.') {
                        $item['kodestandar'] = substr($item['kodestandar'], 0, -1);
                    }

                    return $item['kodestandar'];
                },
                $assessmentMatrices
            );

            $accreditationStandarts = AkreditasiStandar::whereIn('kode_standar', $mappedStandarAkreditasi)->get();

            foreach ($assessmentMatrices as $PenilaianMatriks) {
                $mappedData = [];

                foreach ($mapField as $key => $selfKey) {
                    $value = $PenilaianMatriks[$key] ?? null;

                    // Sanitasi html tag di pertanyaan penilaian
                    if ($key == 'namabutirspmi') {
                        $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                        continue;
                    }

                    if ($key == 'kodespmi') {
                        $mappedData[$selfKey] = $assessmentGuideId;
                        continue;
                    }

                    // Sementara di nonaktifkan
                    // if ($key == 'keterangan') {
                    //     $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                    //     continue;
                    // }

                    // Mapping parent matrix
                    if ($key == 'parentbutirspmi' && !empty($value)) {
                        $parentMatrix = array_filter($savedMatrices, fn ($item) => $item['nomor_penilaian'] == $value);
                        $parentMatrix = array_values($parentMatrix);
                        $mappedData[$selfKey] = $parentMatrix[0]['id'] ?? null;
                        continue;
                    }

                    if ($key == 'idklaster') {
                        $mappedData[$selfKey] = $refPenilaianKlaster[$value] ?? null;
                        continue;
                    }

                    if ($key == 'idsumberreferensi') {
                        $mappedData[$selfKey] = $value == 'BA' ? 'pr' : 'se';
                        continue;
                    }

                    if ($key == 'kodestandar') {
                        if (empty($value)) {
                            continue;
                        }

                        $standartCode = $value;
                        if (substr($value, -1) == '.') {
                            $standartCode = substr($value, 0, -1);
                        }

                        $mappedData[$selfKey] = $accreditationStandarts->where('kode_standar', $standartCode)->first()->id ?? null;
                        continue;
                    }

                    // Mapping skor matrix
                    if ($key == 'pilihanskor' && !empty($value) && is_array($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) {
                            return [
                                'nilai' => $item['skor'],
                                'deskripsi' => $item['uraian'],
                                'apakah_nonaktif' => $item['isdisable'],
                            ];
                        }, $value);
                        continue;
                    }

                    // Jika ada butir LK maka diambil dari referensi LK
                    if ($key == 'butirlk' && !empty($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) use ($refIndicatorPerformanceReport) {
                            return [
                                'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
                                'id_butir_referensi' => $refIndicatorPerformanceReport[$item['nobutir']] ?? null,
                            ];
                        }, $value);
                        continue;
                    }

                    // Jika ada butir LED maka diambil dari referensi LED
                    if ($key == 'butirled' && !empty($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) use ($refIndicatorSelfEvaluation) {
                            return [
                                'jenis_referensi' => PenilaianMatriks::REFERENCE_SELF_EVALUATION,
                                'id_butir_referensi' => $refIndicatorSelfEvaluation[$item['kodeled']] ?? null,
                            ];
                        }, $value);
                        continue;
                    }

                    $mappedData[$selfKey] = $value;
                }

                // Jika katagori penilaian adalah element
                if (
                    ($mappedData['kategori_penilaian'] == PenilaianMatriks::CATEGORY_ELEMENT) &&
                    ($mappedData['jenis_penilaian']) == PenilaianMatriks::TYPE_FINAL_SCORE
                ) {
                    $mappedData['bobot_penilaian'] = null;
                }

                $toStoreMatrix = Arr::except($mappedData, ['indikator_laporan_kinerja', 'indikator_evaluasi_diri', 'scores']);

                try {
                    $savedMatrix = PenilaianMatriks::create([
                        ...$toStoreMatrix,
                        'apakah_data_default' => true,
                    ])
                        ->toArray();
                } catch (\Exception $e) {
                    DB::rollback();

                    $err = true;
                    $msg = $e->getMessage();
                    break;
                }

                // Simpan referensi butir LK dan LED
                $indicatorPerformanceReports = $mappedData['indikator_laporan_kinerja'] ?? [];
                $indicatorSelfEvaluations = $mappedData['indikator_evaluasi_diri'] ?? [];

                if (!empty($indicatorPerformanceReports)) {
                    $indicatorPerformanceReports = array_map(fn ($item) => [
                        'id_penilaian_matriks' => $savedMatrix['id'],
                        ...$item,
                        'waktu_dibuat' => Carbon::now(),
                        'waktu_diubah' => Carbon::now(),
                    ], $indicatorPerformanceReports);

                    try {
                        PenilaianMatriksReferensi::insert($indicatorPerformanceReports);
                    } catch (\Exception $e) {
                        DB::rollback();

                        $err = true;
                        $msg = $e->getMessage();
                        break;
                    }
                }

                if (!empty($indicatorSelfEvaluations)) {
                    $indicatorSelfEvaluations = array_map(fn ($item) => [
                        'id_penilaian_matriks' => $savedMatrix['id'],
                        ...$item,
                        'waktu_dibuat' => Carbon::now(),
                        'waktu_diubah' => Carbon::now(),
                    ], $indicatorSelfEvaluations);

                    try {
                        PenilaianMatriksReferensi::insert($indicatorSelfEvaluations);
                    } catch (\Exception $e) {
                        DB::rollback();

                        $err = true;
                        $msg = $e->getMessage();
                        break;
                    }
                }

                // Simpan data skor matrix
                $scores = $mappedData['scores'] ?? [];
                if (!empty($scores)) {
                    $scores = array_map(fn ($item) => [
                        'id_penilaian_matriks' => $savedMatrix['id'],
                        ...$item,
                        'waktu_dibuat' => Carbon::now(),
                        'waktu_diubah' => Carbon::now(),
                    ], $scores);

                    try {
                        PenilaianMatriksPredikat::insert($scores);
                    } catch (\Exception $e) {
                        DB::rollback();

                        $err = true;
                        $msg = $e->getMessage();
                        break;
                    }
                }

                $savedMatrices[] = $savedMatrix;
            }

            $totalMatrixIndicator = count(
                array_filter(
                    array_map(
                        fn ($item) => $item['kategori_penilaian'],
                        $savedMatrices
                    ),
                    fn ($item) => $item == PenilaianMatriks::CATEGORY_INDICATOR
                )
            );

            PenilaianPanduan::find($assessmentGuideId)->update([
                'total_indikator_matriks' => $totalMatrixIndicator,
            ]);

            DB::commit();
        }

        return [$err, $msg];
    }

    public static function syncAkreditasiSyarat()
    {
        $accreditationSync = new AccreditationSync;

        $assessmentGuideData = PenilaianPanduan::get();
        $assessmentGuideList = $assessmentGuideData->pluck('id', 'kode_penilaian_panduan')->toArray();

        $err = false;
        $msg = 'Success to sync data from SPMI API. And Sync data saved';

        foreach ($assessmentGuideList as $assessmentGuideCode => $assessmentGuideId) {
            DB::beginTransaction();

            $akreditasiSyarat = $accreditationSync->getAkreditasiSyarat($assessmentGuideCode);
            $akreditasiSyarat = $akreditasiSyarat['data'] ?? [];

            // Referensi Matriks Penilaian
            $mappedAssessmentMatrices = array_map(fn ($item) => $item['nobutirspmi'], $akreditasiSyarat);
            $refAssessmentMatrices = PenilaianMatriks::whereIn('nomor_penilaian', $mappedAssessmentMatrices)->pluck('id', 'nomor_penilaian')->toArray();

            // Referensi Peringkat Akreditasi
            $mappedAkreditasiPeringkats = array_map(fn ($item) => $item['kodeakreditasi'], $akreditasiSyarat);
            $refAkreditasiPeringkats = AkreditasiPeringkat::whereIn('kode_peringkat', $mappedAkreditasiPeringkats)->pluck('id', 'kode_peringkat')->toArray();

            foreach ($akreditasiSyarat as $akreditasiSyarat) {
                $data = [
                    'id_penilaian_panduan' => $assessmentGuideId,
                    'id_penilaian_matriks' => $refAssessmentMatrices[$akreditasiSyarat['nobutirspmi']] ?? null,
                    'id_akreditasi_peringkat' => $refAkreditasiPeringkats[$akreditasiSyarat['kodeakreditasi']] ?? null,
                    'jenis_syarat_akreditasi' => $akreditasiSyarat['kodesyarat'],
                    'nilai_syarat_akreditasi' => $akreditasiSyarat['skor'],
                    'apakah_data_default' => true,
                ];

                try {
                    AkreditasiSyarat::updateOrCreate(
                        Arr::only($data, ['id_penilaian_panduan', 'id_penilaian_matriks']),
                        $data
                    );
                } catch (\Exception $e) {
                    DB::rollback();

                    $err = true;
                    $msg = $e->getMessage();
                    break;
                }
            }

            DB::commit();
        }

        return [$err, $msg];
    }

    public static function updateSequence($tableName)
    {
        $lastId = DB::table($tableName)->orderBy('id', 'desc')->first();
        $newId = $lastId->id + 1;
        DB::statement("select setval('$tableName" . "_id_seq', $newId, true);");
    }
}
