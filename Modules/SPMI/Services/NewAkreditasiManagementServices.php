<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\PenilaianKlaster;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

class NewAkreditasiManagementServices
{
    // JENIS PENILAIAN
    const PILIHAN = 'IN';
    const INPUTNILAI = 'IA';
    const PROSES = 'PR';
    const TOTAL = 'TL';
    const SKORAKHIR = 'SA';

    // JENIS BUTIR
    const ELEMEN = 'E';
    const DIMENSI = 'D';
    const INDIKATOR = 'I';

    // STANDAR PT
    const SNDIKTI = 'SN';
    const RENSTRA = 'RS';

    // PEMENUHAN SYARAT PERLU AKREDITASI
    const TERAKREDITASI = 'A';
    const PERINGKATAKREDITASI = 'P';
    const TIDAKADA = 'T';

    // SASARAN MUTU
    const MASUKAN = 'M';
    const SASARAN_MUTU_PROSES = 'P';
    const LUARAN_ATAU_CAPAIAN = 'LP';
    const DAMPAK = 'D';

    const SASARAN_MUTU = [
        'M' => 'Masukan',
        'P' => 'Proses',
        'LP' => 'Luaran atau Capaian',
        'D' => 'Dampak'
    ];

    const SUMBER_DATA = [
        'IN' => 'Kualitatif',
        'PR' => 'Kuantitatif',
        'TL' => 'Akumulasi Skor',
        'SA' => 'Skor Akhir'
    ];

    const JENIS_BUTIR = [
        'E' => 'Elemen',
        'D' => 'Dimensi',
        'I' => 'Indikator'
    ];

    const STANDAR_PT = [
        'SN' => 'SN-Dikti',
        'RS' => 'Target pada Rencana Strategis'
    ];

    const SYARAT_AKREDITASI = [
        'T' => 'Tidak',
        'P' => 'Peringkat Akreditasi',
        'A' => 'Terakreditasi'
    ];

    const PEMENUHAN_INDIKATOR = [
        1 => 'Memenuhi Indikator',
        0 => 'Tidak Memenuhi Indikator'
    ];

    const MELAMPUI_SESUAI = [
        0 => 'Sesuai',
        1 => 'Melampui'
    ];

    const SYARAT_PERINGKAT = [
        1 => 'Unggul',
        0 => 'Baik Sekali'
    ];

    const SYARAT_TERAKREDITASI = [
        1 => 'Memenuhi Syarat Perlu',
        0 => 'Tidak Memenuhi Syarat Perlu'
    ];

    const TINGKAT_DAYA_SAING = [
        1 => 'Lokal/Wilayah',
        2 => 'Nasional',
        3 => 'Internasional'
    ];

    const TAGS = [
        'FSP' => 'Fasilitas & Sarana Prasarana',
        'KDI' => 'Kinerja & Dampak Institusi',
        'MLA' => 'Mahasiswa, Lulusan & Alumni',
        'TRI' => 'Tridharma',
        'PBK' => 'Pembelajaran & Kurikulum',
        'PLT' => 'Penelitian',
        'PKM' => 'Pengabdian kepada Masyarakat (PkM)',
        'SDM' => 'SDM',
        'TKK' => 'Tata Kelola & Kepemimpinan',
        'SPM' => 'SPMI',
        'KEU' => 'Keuangan',
        'LLN' => 'Lain-Lain',
    ];

    // SUMBER TARIK DATA
    const INPUTMANUAL = 'IN';
    const AKADEMIK = 'AK';
    const SDM = 'SDM';
    const AKREDITASICLOUD = 'AC';
    const SDMAKADEMIK = 'SA';
    const TRACERSTUDY = 'TS';

    // JENIS TARIK DATA
    const LANGSUNG = 'L';
    const POPUP = 'P';

    // JENIS FORM
    const FORM_INPUT_ROW = 'FIR';
    const FORM_ROW_CATEGORY = 'FBC';
    const FORM_COLUMN_CATEGORY = 'FCC';
    const FORM_COLUMN_CATEGORY_MANUAL = 'FCM';
    const FORM_FIXED_ROW_OLD = 'FBR';
    const FORM_FIXED_ROW = 'FFR';
    const FORM_FIXED_ROW_CATEGORY = 'FRC';
    const FORM_TAHUN_AKADEMIK = 'FTA';
    const FORM_KOHORT = 'FKM';
    const FORM_KOHORT_LULUSAN = 'FKL';
    const FORM_CUSTOM = 'FCT';
    const FORM_TEXTAREA = 'FXA';

    // orientasi layout
    const PORTRAIT = 'P';
    const LANDSCAPE = 'L';

    // JENIS EDISI
    const BUTIR = 'BA';
    const EVALUASIDIRI = 'ED';

    // BADAN AKREDITASI
    const BANPT = 'BANPT';
    const LAMPT = 'LAMPTKES'; // Lembaga Akreditasi Mandiri Perguruan Tinggi Kesehatan
    const LAMTEKNIK = 'LAMTEKNIK';
    const LAMEMBA = 'LAMEMBA';
    const LAMSAMA = 'LAMSAMA';

    const JENIS_EDISI = [
        self::BUTIR => 'Butir Akreditasi',
        self::EVALUASIDIRI => 'Evaluasi Diri'
    ];

    const JENIS_TARIK_DATA = [
        self::INPUTMANUAL => '<label class="label label-info">Input Manual</label>',
        self::AKADEMIK => '<label class="label label-success">Akademik</label>',
        self::SDM => '<label class="label label-danger">Kepegawaian</label>',
        // self::SDMAKADEMIK => '<label class="label label-warning">SDM dan Akademik</label>',
        self::AKREDITASICLOUD => '<label class="label label-primary">Unit Kerja</label>',
        self::TRACERSTUDY => '<label class="label label-primary">Tracer Study</label>'
    ];

    const OPSI_TARIK_DATA = [
        self::LANGSUNG => 'Langsung',
        self::POPUP => 'Popup Modal'
    ];

    const JENIS_IMPORT = [
        /*0 => '<i class="fa fa-times" style="color:#f56954"></i>',*/
        0 => '',
        1 => '<label class="label label-success">Excel</label>'
    ];

    const FORM_PENGISIAN = [
        self::FORM_TEXTAREA => 'Form Textarea',
        self::FORM_INPUT_ROW => 'Form Input',
        self::FORM_ROW_CATEGORY => 'Form Row Category',
        self::FORM_COLUMN_CATEGORY => 'Form Column Category',
        self::FORM_COLUMN_CATEGORY_MANUAL => 'Form Column Category Input Manual',
        self::FORM_FIXED_ROW => 'Form Fixed Row (New)',
        self::FORM_FIXED_ROW_OLD => 'Form Fixed Row (Old)',
        self::FORM_FIXED_ROW_CATEGORY => 'Form Fixed Row Category',
        self::FORM_TAHUN_AKADEMIK => 'Form Fixed Row Tahun Akademik',
        self::FORM_KOHORT => 'Form Kohort Mahasiswa',
        self::FORM_CUSTOM => 'Form Custom (Harus dibuat di Helper)'
    ];

    const JENIS_FORM_PENGISIAN = [
        self::FORM_TEXTAREA => 'Form Textarea',
        self::FORM_INPUT_ROW => 'Form Input',
        self::FORM_ROW_CATEGORY => 'Form Row Category',
        self::FORM_COLUMN_CATEGORY => 'Form Column Category',
        self::FORM_COLUMN_CATEGORY_MANUAL => 'Form Column Category Input Manual',
        self::FORM_FIXED_ROW => 'Form Fixed Row',
        self::FORM_FIXED_ROW_CATEGORY => 'Form Fixed Row Category',
        self::FORM_TAHUN_AKADEMIK => 'Form Fixed Row Tahun Akademik',
        self::FORM_KOHORT => 'Form Kohort Mahasiswa',
        self::FORM_CUSTOM => 'Form Custom (Harus dibuat di Helper)'
    ];

    const FUNCTION_FORM_PENGISIAN = [
        self::FORM_TEXTAREA => 'createFormTextArea',
        self::FORM_INPUT_ROW => 'createFormStandart',
        self::FORM_ROW_CATEGORY => 'createFormRowCategory',
        self::FORM_COLUMN_CATEGORY => 'createFormColumnCategory',
        self::FORM_COLUMN_CATEGORY_MANUAL => 'createFormColumnCategoryManual',
        self::FORM_FIXED_ROW => 'createFormFixedRow',
        self::FORM_FIXED_ROW_OLD => 'createFormFixedRowOld',
        self::FORM_FIXED_ROW_CATEGORY => 'createFormFixedRowCategory',
        self::FORM_TAHUN_AKADEMIK => 'createFormByTahunAkademik',
        self::FORM_KOHORT => 'createFormKohortMahasiswa',
        self::FORM_KOHORT_LULUSAN => 'createFormKohortLulusan',
    ];

    const ORIENTASI_LAYOUT = [
        self::PORTRAIT => 'Portrait',
        self::LANDSCAPE => 'Landscape',
    ];

    public function changeFormType($value)
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

    public function getPanduanPenilaian($listKodePanduanPenilaian = [])
    {
        $sql = "select a.kodespmi, a.namaspmi as namapanduan, a.spmisingkatan as namasingkat, a.edisispmi as tgledisi,
                a.kodejenisstandar as idstandarakreditasi, st.jenisstandar as standarakreditasi,
                a.kodebanpt as kodelk, c.singkatan as namalk,
                a.kodeevaluasi as kodeled, d.singkatan as namaled,
                a.kodeprogram as idjenjang, j.programpendidikan as jenjang,
                a.kodebentukpt, pt.bentukpt,
                a.status as idstatuspt,
                case
                    when a.status = 'N' then 'Negeri'
                    when a.status = 'S' then 'Swasta'
                    else null
                end as statuspt,
                a.keterangan, a.isaktif, a.isskorakhir
            from akreditasi.ms_spmi a
            join akreditasi.ms_jenisstandarakreditasi st using(kodejenisstandar)
            join akreditasi.ms_banpt c using(kodebanpt)
            left join akreditasi.ms_programpendidikan b using(kodeprogram)
            left join akreditasi.ms_banpt d on d.kodebanpt = a.kodeevaluasi
            left join akreditasi.ms_programpendidikan j using(kodeprogram)
            left join sdm.ms_bentukpt pt using(kodebentukpt)";

        if (!empty($listKodePanduanPenilaian)) {
            $sql .= " where a.kodespmi in ('" . implode("','", $listKodePanduanPenilaian) . "')";
        }

        $sql .= " order by a.kodespmi";

        $result = DB::connection('akreditasicloud')->select($sql);

        return $result;
    }

    public function getPanduanAkreditasi($listKodePanduanPengisian = [])
    {
        $sql = "select a.kodebanpt, a.namabanpt as namapanduan, a.singkatan as namasingkat, a.edisi as tgledisi,
            a.tglberlaku, a.tglkadaluarsa as tglberakhir, a.issapto, a.isaktif, a.keterangan, a.jenisedisi as idedisi,
            case
                when a.jenisedisi = 'ED' then 'Evaluasi Diri'
                else 'Laporan Kinerja'
            end as namaedisi,
            ak.idlembaga, ak.namasingkat as lembagaakreditasi,
            a.kodelevelakses as idlevelakses, c.levelakses,
            a.kodejenisbuku as idbukuakreditasi, b.jenisbuku as bukuakreditasi,
            a.kodejenisstandar as idstandarakreditasi, st.jenisstandar as standarakreditasi,
            a.kodeevaluasi as idled, led.singkatan as namaled
        from akreditasi.ms_banpt a
        join akreditasi.ms_lembagaakreditasi ak on ak.idlembaga = a.lembaga
        join akreditasi.ms_jenisbuku b using(kodejenisbuku)
        join akreditasi.ms_levelakses c using(kodelevelakses)
        join akreditasi.ms_jenisstandarakreditasi st using(kodejenisstandar)
        left join akreditasi.ms_banpt led on led.kodebanpt = a.kodeevaluasi";

        if (!empty($listKodePanduanPengisian)) {
            $sql .= " where a.kodebanpt in ('" . implode("','", $listKodePanduanPengisian) . "')";
        }

        $sql .= " order by a.jenisedisi desc, a.kodebanpt";

        $result = DB::connection('akreditasicloud')->select($sql);

        return $result;
    }

    public function getMatriksPenilaian($kodePanduanPenilaian)
    {
        $klasterSql = "select idklaster, namaklaster from akreditasi.ms_spmiklaster";
        $standarAkreditasiSql = "select idstandarakreditasi, kodestandar from akreditasi.ms_standarakreditasi";
        $dataSql = "select * from akreditasi.ms_butirspmi where kodespmi = '$kodePanduanPenilaian' order by infoleft";
        $butirLkSql = "select * from akreditasi.ms_butirspmiakreditasi where kodespmi = '$kodePanduanPenilaian' order by kodespmi,nobutirspmi,kodebanpt,nobutir";
        $butirLedSql = "select * from akreditasi.ms_butirspmievaluasi where kodespmi = '$kodePanduanPenilaian' order by kodespmi,nobutirspmi,kodebanpt,kodeled";
        $skorSql = "select * from akreditasi.ms_butirspmiskor where kodespmi = '$kodePanduanPenilaian' order by kodespmi,nobutirspmi,kodekriteria";

        $sumber = self::SUMBER_DATA;
        $jenisbutir = self::JENIS_BUTIR;
        $standarpt = self::STANDAR_PT;
        $syaratakreditasi = self::SYARAT_AKREDITASI;
        $klaster = DB::connection('akreditasicloud')->select($klasterSql);
        $klaster = collect($klaster)->pluck('namaklaster', 'idklaster')->toArray();
        $jenisedisi = self::JENIS_EDISI;
        $standarakreditasi = DB::connection('akreditasicloud')->select($standarAkreditasiSql);
        $standarakreditasi = collect($standarakreditasi)->pluck('kodestandar', 'idstandarakreditasi')->toArray();

        $data = json_decode(json_encode(DB::connection('akreditasicloud')->select($dataSql)), true);
        $butirlk = json_decode(json_encode(DB::connection('akreditasicloud')->select($butirLkSql)), true);
        $butirled = json_decode(json_encode(DB::connection('akreditasicloud')->select($butirLedSql)), true);
        $skor = json_decode(json_encode(DB::connection('akreditasicloud')->select($skorSql)), true);

        $i = 0;
        $nobutir = '';
        $arrButirlk = [];
        foreach ($butirlk as $col) {
            if ($nobutir != $col['nobutirspmi']) {
                $i = 0;
                $nobutir = $col['nobutirspmi'];
            }

            $arrButirlk[$col['nobutirspmi']][$i] = [
                'kodebanpt' => $col['kodebanpt'],
                'nobutir' => $col['nobutir'],
            ];

            $i++;
        }

        $nobutir = '';
        $arrButired = [];
        foreach ($butirled as $col) {
            if ($nobutir != $col['nobutirspmi']) {
                $i = 0;
                $nobutir = $col['nobutirspmi'];
            }

            $arrButired[$col['nobutirspmi']][$i] = [
                'kodebanpt' => $col['kodebanpt'],
                'kodeled' => $col['kodeled'],
            ];

            $i++;
        }

        $arrSkor = [];
        foreach ($skor as $col) {
            $arrSkor[$col['nobutirspmi']][$col['kodekriteria']] = [
                'skor' => $col['kodekriteria'],
                'uraian' => $col['uraian'],
                'nilaimin' => $col['nilaimin'],
                'nilaimax' => $col['nilaimax'],
                'rumus' => $col['rumus'],
                'operatormin' => $col['operatormin'],
                'operatormax' => $col['operatormax'],
                'isdisable' => $col['disable'],
            ];

            $i++;
        }

        $i = 0;
        $arrData = [];
        foreach ($data as $row) {
            $kodeStandar = $standarakreditasi[$row['idstandarakreditasi']] ?? null;
            $stdAkreditasi = $kodeStandar ? explode(' ', $kodeStandar) : [null];

            $arrData[$i] = $row;
            $arrData[$i]['level'] = ($row['level'] ?? 1) - 1;
            $arrData[$i]['namaklaster'] = $klaster[$row['idklaster']] ?? null;
            $arrData[$i]['idstandarpt'] = $row['standarpt'] ?? null;
            $arrData[$i]['namastandarpt'] = $standarpt[$row['standarpt']] ?? null;
            $arrData[$i]['idkategori'] = $row['jenisbutir'] ?? null;
            $arrData[$i]['namakategori'] = $jenisbutir[$row['jenisbutir']] ?? null;
            $arrData[$i]['kodestandar'] = $stdAkreditasi[0] ?? null;
            $arrData[$i]['standarakreditasi'] = $kodeStandar;
            $arrData[$i]['idsyaratakreditasi'] = $row['syaratakreditasi'] ?? null;
            $arrData[$i]['namasyaratakreditasi'] = $syaratakreditasi[$row['syaratakreditasi']] ?? null;
            $arrData[$i]['idjenispenilaian'] = $row['sumberdata'] ?? null;
            $arrData[$i]['jenispenilaian'] = $sumber[$row['sumberdata']] ?? null;
            $arrData[$i]['rumuspenilaian'] = $row['rumustotal'] ?? null;
            $arrData[$i]['parampenilaian'] = $row['params'] ?? null;
            $arrData[$i]['idsumberreferensi'] = $row['jenisedisi'] ?? null;
            $arrData[$i]['sumberreferensi'] = $jenisedisi[$row['jenisedisi']] ?? null;
            $arrData[$i]['istampilkandinilaiakhir'] = $row['isbutirnilaiakhir'] ?? null;
            $arrData[$i]['namadinilaiakhir'] = $row['butirnilaiakhir'] ?? null;
            $arrData[$i]['butirlk'] = $arrButirlk[$row['nobutirspmi']] ?? [];
            $arrData[$i]['butirled'] = $arrButired[$row['nobutirspmi']] ?? [];
            $arrData[$i]['pilihanskor'] = $arrSkor[$row['nobutirspmi']] ?? [];

            unset($arrData[$i]['jenisbutir'], $arrData[$i]['standarpt'], $arrData[$i]['syaratakreditasi'], $arrData[$i]['sumberdata'], $arrData[$i]['rumustotal'], $arrData[$i]['params'], $arrData[$i]['jenisedisi'], $arrData[$i]['isbutirnilaiakhir'], $arrData[$i]['butirnilaiakhir']);

            $i++;
        }

        return $arrData;
    }

    public function getButirLK($kodePengisianPanduan)
    {
        $dataSql = "select * from akreditasi.ms_butirbanpt where kodebanpt = '$kodePengisianPanduan' order by infoleft";

        $aJenisForm = self::JENIS_FORM_PENGISIAN;
        $aOpsiTarikData = self::OPSI_TARIK_DATA;
        $aJenisTarikData = [
            self::INPUTMANUAL => 'Input Manual',
            self::AKADEMIK => 'Akademik',
            self::SDM => 'Kepegawaian',
            self::SDMAKADEMIK => 'SDM dan Akademik',
            self::AKREDITASICLOUD => 'Unit Kerja'
        ];

        $data = json_decode(json_encode(DB::connection('akreditasicloud')->select($dataSql)), true);

        $i = 0;
        $arrData = [];
        foreach ($data as $row) {
            $arrData[$i] = $row;
            $arrData[$i]['level'] = $row['level'] - 1;
            $arrData[$i]['idhakakses'] = $row['isedit'];
            $arrData[$i]['idjenisform'] = $row['jenisform'];
            $arrData[$i]['namaform'] = $aJenisForm[$row['jenisform']] ?? null;
            $arrData[$i]['idtarikdata'] = $row['tarikdata'];
            $arrData[$i]['namatarikdata'] = $aJenisTarikData[$row['tarikdata']] ?? null;
            $arrData[$i]['idjenistarikdata'] = $row['jenistarikdata'];
            $arrData[$i]['namajenistarikdata'] = $aOpsiTarikData[$row['jenistarikdata']] ?? null;

            unset($arrData[$i]['isedit'], $arrData[$i]['jenisform'], $arrData[$i]['tarikdata'], $arrData[$i]['jenistarikdata']);

            $i++;
        }

        return $arrData;
    }

    public function getButirLed($kodePengisianPanduan)
    {
        $dataSql = "select * from akreditasi.ms_evaluasidiri where kodebanpt = '$kodePengisianPanduan' order by infoleft";
        $dataLEDToButirSql = "select * from akreditasi.ms_evaluasidiritobutir where kodebanpt = '$kodePengisianPanduan' order by kodebanpt,kodeled,nobutir";

        $data = json_decode(json_encode(DB::connection('akreditasicloud')->select($dataSql)), true);
        $butir = json_decode(json_encode(DB::connection('akreditasicloud')->select($dataLEDToButirSql)), true);

        $nobutir = '';
        $arrButir = [];
        foreach ($butir as $col) {
            if ($nobutir != $col['kodeled']) {
                $i = 0;
                $nobutir = $col['kodeled'];
            }

            $arrButir[$col['kodeled']][$i] = [
                'kodebanpt' => $col['edisiakreditasi'],
                'nobutir' => $col['nobutir'],
            ];

            $i++;
        }

        $i = 0;
        $arrData = [];
        foreach ($data as $row) {
            $arrData[$i] = $row;
            $arrData[$i]['level'] = $row['level'] - 1;
            $arrData[$i]['butirlk'] = $arrButir[$row['kodeled']] ?? [];

            $i++;
        }

        return $arrData;
    }

    public function getStandarAkreditasi($kodeJenisStandar)
    {
        $dataSql = "select * from akreditasi.ms_standarakreditasi where kodejenisstandar= '$kodeJenisStandar'";

        $data = json_decode(json_encode(DB::connection('akreditasicloud')->select($dataSql)), true);

        return $data;
    }

    public function syncButirLKPanduan($kodePengisianPanduan)
    {
        $modelSPMI = IndikatorLaporanKinerja::class;

        // get data filling guide
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)
            ->where('kode_pengisian_panduan', $kodePengisianPanduan)
            ->where('apakah_data_default', true)
            ->first();
        if (empty($pengisianPanduan)) {
            return false;
        }

        $rawData = self::getButirLK($pengisianPanduan->kode_pengisian_panduan);

        foreach ($rawData as $key => $v) {
            if (!empty($v['idjenisform'])) {
                $addColumns = self::changeFormType($v['idjenisform']);
                $rawData[$key] = array_merge($rawData[$key], $addColumns);
            }
        }

        // collect synced nomor_indikator to track which records are still valid
        $syncedNomorIndikator = array_map(fn($data) => $data['nobutir'], $rawData);

        DB::beginTransaction();
        try {
            foreach ($rawData as $data) {
                $parent = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                    ->where('nomor_indikator', $data['parentnobutir'])
                    ->where('apakah_data_default', true)
                    ->first();

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
                    'apakah_parent' => $data['idhakakses'] == 'R',
                    'info_level' => $data['level'],
                    'apakah_data_default' => true,
                    'apakah_memasukkan_kategori_manual' => false,
                    'apakah_subfooter' => false,
                ], self::changeFormType($data['idjenisform']));

                $target = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                    ->where('nomor_indikator', $data['nobutir'])
                    ->where('apakah_data_default', true)
                    ->first();
                if ($target) {
                    $target->fill($payload);
                    $target->saveQuietly();
                } else {
                    $modelSPMI::create($payload);
                }
            }

            // remove untracked records (not present in synced data)
            $untrackedRecords = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                ->where('apakah_data_default', true)
                ->whereNotIn('nomor_indikator', $syncedNomorIndikator)
                ->get();

            foreach ($untrackedRecords as $record) {
                try {
                    $record->delete();
                } catch (QueryException $e) {
                    Log::warning("Sync LK: Skipped deleting IndikatorLaporanKinerja ID {$record->id} due to constraint: " . $e->getMessage());
                }
            }

            IndikatorLaporanKinerja::resyncTreeStructure($pengisianPanduan->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return new \Exception("Gagal sinkronisasi butir LK: " . $e->getMessage());
        }

        DB::commit();

        return true;
    }

    public function syncButirLEDPanduan($kodePengisianPanduan)
    {
        $modelSPMI = IndikatorEvaluasiDiri::class;

        // get data filling guide
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::SELF_EVALUATION)
            ->where('kode_pengisian_panduan', $kodePengisianPanduan)
            ->where('apakah_data_default', true)
            ->first();
        if (empty($pengisianPanduan)) {
            return false;
        }

        $rawData = self::getButirLed($pengisianPanduan->kode_pengisian_panduan);

        // collect synced nomor_indikator to track which records are still valid
        $syncedNomorIndikator = array_map(fn($data) => $data['kodeled'], $rawData);

        DB::beginTransaction();
        try {
            foreach ($rawData as $data) {
                $parent = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                    ->where('nomor_indikator', $data['parentled'])
                    ->where('apakah_data_default', true)
                    ->first();

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

                $target = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                    ->where('nomor_indikator', $data['kodeled'])
                    ->where('apakah_data_default', true)
                    ->first();
                if ($target) {
                    $target->fill($payload);
                    $target->saveQuietly();
                } else {
                    $modelSPMI::create($payload);
                }
            }

            // remove untracked records (not present in synced data)
            $untrackedRecords = $modelSPMI::where('id_pengisian_panduan', $pengisianPanduan->id)
                ->where('apakah_data_default', true)
                ->whereNotIn('nomor_indikator', $syncedNomorIndikator)
                ->get();

            foreach ($untrackedRecords as $record) {
                try {
                    $record->delete();
                } catch (QueryException $e) {
                    Log::warning("Sync LED: Skipped deleting IndikatorEvaluasiDiri ID {$record->id} due to constraint: " . $e->getMessage());
                }
            }

            IndikatorEvaluasiDiri::resyncTreeStructure($pengisianPanduan->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return new \Exception("Gagal sinkronisasi butir LED: " . $e->getMessage());
        }

        DB::commit();

        return true;
    }

    public function tarikPanduanPengisian($kodePengisianPanduan, $kodeLembaga, $apakahAktif = false)
    {
        $dataPanduan = self::getPanduanAkreditasi([$kodePengisianPanduan]);
        if (empty($dataPanduan)) {
            return new \Exception("Panduan pengisian akreditasi dengan kode '$kodePengisianPanduan' tidak ditemukan di Akreditasi Cloud.");
        }

        $dataPanduan = json_decode(json_encode($dataPanduan), true);
        $rp = $dataPanduan[0];

        // transaction
        DB::beginTransaction();

        // create panduan
        $idsAkreditasiBuku = AkreditasiBuku::pluck('id', 'kode_buku')->toArray();

        try {
            $payload = [
                'kode_pengisian_panduan' => $rp['kodebanpt'],
                'nama_pengisian_panduan' => $rp['namapanduan'],
                'nama_singkat' => $rp['namasingkat'],
                'id_lembaga_akreditasi' => LembagaAkreditasi::where('kode_lembaga', $kodeLembaga)->value('id'),
                'kode_level_akses' => $rp['idlevelakses'],
                'id_jenis_standar' => JenisStandar::where('kode_jenis_standar', $rp['idstandarakreditasi'])->value('id'),
                'tipe_edisi' => $rp['idedisi'] == 'ED' ? AkreditasiBuku::SELF_EVALUATION : AkreditasiBuku::PERFORMANCE_REPORT,
                'id_akreditasi_buku' => $rp['idedisi'] == 'ED' ? $idsAkreditasiBuku['LED'] : $idsAkreditasiBuku['LKPS'],
                'apakah_aktif' => $apakahAktif,
                'tanggal_edisi' => $rp['tgledisi'],
                'tanggal_efektif' => $rp['tglberlaku'],
                'tanggal_kadaluwarsa' => $rp['tglberakhir'],
                'apakah_sapto' => $rp['issapto'] == '1' ? true : false,
            ];

            if (!empty($rp['idled'])) {
                $payload['id_pengisian_panduan'] = PengisianPanduan::where('kode_pengisian_panduan', $rp['idled'])
                    ->where('apakah_data_default', true)
                    ->value('id');
            }

            PengisianPanduan::updateOrCreate(
                ['kode_pengisian_panduan' => $rp['kodebanpt'], 'apakah_data_default' => true],
                $payload
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return new \Exception("Gagal menarik panduan pengisian akreditasi: " . $e->getMessage());
        }

        DB::commit();

        return true;
    }

    public function syncButirMatriks($kodePenilaianPanduan)
    {
        $dataPenilaianMatriks = self::getMatriksPenilaian($kodePenilaianPanduan);

        if (empty($dataPenilaianMatriks)) {
            throw new \Exception("Data matriks penilaian dengan kode '$kodePenilaianPanduan' tidak ditemukan di Akreditasi Cloud.");
        }

        $panduanPenilaian = PenilaianPanduan::where('kode_penilaian_panduan', $kodePenilaianPanduan)
            ->where('apakah_data_default', true)
            ->first();
        if (empty($panduanPenilaian)) {
            throw new \Exception("Panduan penilaian dengan kode '$kodePenilaianPanduan' tidak ditemukan.");
        }

        $idPanduanPenilaian = $panduanPenilaian->id;

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

        $skorMatriksPredikatPenilaian = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $idPanduanPenilaian)
            ->pluck('id', 'nilai')->toArray();

        // Referensi Kluster Penilaian
        $mappedPenilaianKlaster = array_map(fn($item) => $item['idklaster'] ?? null, $dataPenilaianMatriks);
        $mappedPenilaianKlaster = array_filter($mappedPenilaianKlaster);
        $refPenilaianKlaster = PenilaianKlaster::whereIn('kode_klaster', $mappedPenilaianKlaster)
            ->pluck('id', 'kode_klaster')->toArray();

        // Referensi butir LK
        $mappedIndikatorLaporanKinerja = array_map(fn($item) => $item['butirlk'] ?? null, $dataPenilaianMatriks);
        $mappedIndikatorLaporanKinerja = array_filter($mappedIndikatorLaporanKinerja, fn($item) => !empty($item));
        $mappedIndikatorLaporanKinerja = array_unique(Arr::flatten($mappedIndikatorLaporanKinerja));
        $refIndikatorLaporanKinerja = IndikatorLaporanKinerja::where('id_pengisian_panduan', $panduanPenilaian->id_laporan_kinerja ?? null)
            ->whereIn('nomor_indikator', $mappedIndikatorLaporanKinerja)
            ->pluck('id', 'nomor_indikator')->toArray();

        // Referensi butir LED
        $panduanPengisianLED = PengisianPanduan::where('id', $panduanPenilaian->id_laporan_kinerja ?? null)
            ->value('id_pengisian_panduan') ?? null;
        $mappedIndikatorEvaluasiDiri = array_map(fn($item) => $item['butirled'] ?? null, $dataPenilaianMatriks);
        $mappedIndikatorEvaluasiDiri = array_filter($mappedIndikatorEvaluasiDiri, fn($item) => !empty($item));
        $mappedIndikatorEvaluasiDiri = array_unique(Arr::flatten($mappedIndikatorEvaluasiDiri));
        $refIndikatorEvaluasiDiri = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $panduanPengisianLED)
            ->whereIn('nomor_indikator', $mappedIndikatorEvaluasiDiri)
            ->pluck('id', 'nomor_indikator')->toArray();

        $accreditationStandarts = AkreditasiStandar::where('id_jenis_standar', $panduanPenilaian->id_jenis_standar)->get();

        DB::beginTransaction();
        try {
            $savedMatrices = [];

            foreach ($dataPenilaianMatriks as $matriksItem) {
                $mappedData = [];

                foreach ($mapField as $key => $selfKey) {
                    $value = $matriksItem[$key] ?? null;

                    // Sanitasi html tag di pertanyaan penilaian
                    if ($key == 'namabutirspmi') {
                        $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                        continue;
                    }

                    if ($key == 'kodespmi') {
                        $mappedData[$selfKey] = $idPanduanPenilaian;
                        continue;
                    }

                    // Mapping parent matrix
                    if ($key == 'parentbutirspmi' && !empty($value)) {
                        $parentMatrix = array_filter($savedMatrices, fn($item) => $item['nomor_penilaian'] == $value);
                        $parentMatrix = array_values($parentMatrix);
                        $mappedData[$selfKey] = $parentMatrix[0]['id'] ?? null;
                        continue;
                    }

                    if ($key == 'idklaster') {
                        $mappedData[$selfKey] = $refPenilaianKlaster[$value] ?? null;
                        continue;
                    }

                    if ($key == 'idsumberreferensi') {
                        $mappedData[$selfKey] = $value == 'BA' ? 'pr' : ($value == 'GB' ? 'me' : 'se');
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

                        $kodeStandarRef = $accreditationStandarts->where('kode_standar', $standartCode)->first()->id ?? null;
                        if (!empty($kodeStandarRef)) {
                            $mappedData[$selfKey] = $kodeStandarRef;
                        }
                        continue;
                    }

                    // Mapping skor matrix
                    if ($key == 'pilihanskor' && !empty($value) && is_array($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) {
                            return [
                                'nilai' => $item['skor'] ?? null,
                                'deskripsi' => $item['uraian'] ?? null,
                                'apakah_nonaktif' => $item['isdisable'] ?? false,
                            ];
                        }, $value);
                        continue;
                    }

                    // Jika ada butir LK maka diambil dari referensi LK
                    if ($key == 'butirlk' && !empty($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) use ($refIndikatorLaporanKinerja) {
                            return [
                                'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
                                'id_butir_referensi' => $refIndikatorLaporanKinerja[$item['nobutir']] ?? null,
                            ];
                        }, $value);
                        continue;
                    }

                    // Jika ada butir LED maka diambil dari referensi LED
                    if ($key == 'butirled' && !empty($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) use ($refIndikatorEvaluasiDiri) {
                            return [
                                'jenis_referensi' => PenilaianMatriks::REFERENCE_SELF_EVALUATION,
                                'id_butir_referensi' => $refIndikatorEvaluasiDiri[$item['kodeled']] ?? null,
                            ];
                        }, $value);
                        continue;
                    }

                    $mappedData[$selfKey] = $value;
                }

                // Jika kategori penilaian adalah element
                if (
                    ($mappedData['kategori_penilaian'] ?? null) == PenilaianMatriks::CATEGORY_ELEMENT &&
                    ($mappedData['jenis_penilaian'] ?? null) == PenilaianMatriks::TYPE_FINAL_SCORE
                ) {
                    $mappedData['bobot_penilaian'] = null;
                }

                $toStoreMatrix = Arr::except($mappedData, ['indikator_laporan_kinerja', 'indikator_evaluasi_diri', 'scores']);

                $currentMatrik = PenilaianMatriks::where('id_penilaian_panduan', $idPanduanPenilaian)
                    ->where('nomor_penilaian', $toStoreMatrix['nomor_penilaian'])
                    ->where('apakah_data_default', true)
                    ->first();
                if ($currentMatrik) {
                    $currentMatrik->update([
                        ...$toStoreMatrix,
                    ]);
                    $savedMatrix = $currentMatrik->toArray();
                } else {
                    $toStoreMatrix['apakah_data_default'] = true;
                    $toStoreMatrix['butir_indikator_spme'] = true;
                    $toStoreMatrix['butir_indikator_iku'] = true;
                    $newMatrik = PenilaianMatriks::create($toStoreMatrix);
                    $savedMatrix = $newMatrik->toArray();
                }

                // Simpan referensi butir LK dan LED
                $indikatorLaporanKinerja = $mappedData['indikator_laporan_kinerja'] ?? [];
                $indikatorEvaluasiDiri = $mappedData['indikator_evaluasi_diri'] ?? [];

                if (!empty($indikatorLaporanKinerja)) {
                    foreach ($indikatorLaporanKinerja as $item) {
                        if (empty($item['id_butir_referensi'])) {
                            continue;
                        }
                        PenilaianMatriksReferensi::updateOrCreate(
                            [
                                'id_penilaian_matriks' => $savedMatrix['id'],
                                'id_butir_referensi' => $item['id_butir_referensi'],
                            ],
                            [
                                'jenis_referensi' => $item['jenis_referensi'],
                                'waktu_diubah' => Carbon::now(),
                            ]
                        );
                    }
                }

                if (!empty($indikatorEvaluasiDiri)) {
                    foreach ($indikatorEvaluasiDiri as $item) {
                        if (is_null($item['id_butir_referensi'])) {
                            continue;
                        }
                        PenilaianMatriksReferensi::updateOrCreate(
                            [
                                'id_penilaian_matriks' => $savedMatrix['id'],
                                'id_butir_referensi' => $item['id_butir_referensi'],
                            ],
                            [
                                'jenis_referensi' => $item['jenis_referensi'],
                                'waktu_diubah' => Carbon::now(),
                            ]
                        );
                    }
                }

                // // Simpan data skor matrix
                $scores = $mappedData['scores'] ?? [];
                if (!empty($scores)) {
                    foreach ($scores as $score) {
                        $idSkorPredikat = $skorMatriksPredikatPenilaian[$score['nilai']] ?? null;
                        $pmp = PenilaianMatriksPredikat::where('id_penilaian_matriks', $savedMatrix['id'])
                            ->where('id_skor_matriks_predikat_penilaian', $idSkorPredikat)
                            ->first();
                        if ($pmp) {
                            $pmp->update([
                                'deskripsi' => $score['deskripsi'] ?? null,
                                'apakah_nonaktif' => !empty($score['apakah_nonaktif']) ? true : false,
                            ]);
                        } else {
                            PenilaianMatriksPredikat::create([
                                'id_penilaian_matriks' => $savedMatrix['id'],
                                'id_skor_matriks_predikat_penilaian' => $idSkorPredikat,
                                'deskripsi' => $score['deskripsi'] ?? null,
                                'apakah_nonaktif' => !empty($score['apakah_nonaktif']) ? false : true,
                            ]);
                        }
                    }
                }

                $savedMatrices[] = $savedMatrix;
            }

            // remove untracked matriks records (not present in synced data)
            $syncedNomorPenilaian = array_map(fn($item) => $item['nomor_penilaian'], $savedMatrices);

            $untrackedMatriks = PenilaianMatriks::where('id_penilaian_panduan', $idPanduanPenilaian)
                ->where('apakah_data_default', true)
                ->whereNotIn('nomor_penilaian', $syncedNomorPenilaian)
                ->get();

            foreach ($untrackedMatriks as $matriksRecord) {
                try {
                    // remove related referensi and predikat first
                    PenilaianMatriksReferensi::where('id_penilaian_matriks', $matriksRecord->id)->delete();
                    PenilaianMatriksPredikat::where('id_penilaian_matriks', $matriksRecord->id)->delete();
                    $matriksRecord->delete();
                } catch (QueryException $e) {
                    Log::warning("Sync Matriks: Skipped deleting PenilaianMatriks ID {$matriksRecord->id} due to constraint: " . $e->getMessage());
                }
            }

            // Update total indikator matriks
            $totalIndikatorMatriks = count(
                array_filter(
                    $savedMatrices,
                    fn($item) => ($item['kategori_penilaian'] ?? null) == PenilaianMatriks::CATEGORY_INDICATOR
                        && ($item['apakah_aktif'] ?? false) == true
                )
            );

            PenilaianMatriks::resyncTreeStructure($idPanduanPenilaian);

            PenilaianPanduan::find($idPanduanPenilaian)?->update([
                'total_indikator_matriks' => $totalIndikatorMatriks,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Gagal migrasi penilaian matriks: " . $e->getMessage());
        }

        DB::commit();

        return true;
    }

    public function syncStandarAkreditasi($kodeJenisStandar)
    {
        $parentSql = "select * from akreditasi.ms_jenisstandarakreditasi where kodejenisstandar = '$kodeJenisStandar'";
        $parentStandar = DB::connection('akreditasicloud')->select($parentSql);
        if (empty($parentStandar)) {
            throw new \Exception("Jenis standar akreditasi dengan kode jenis standar '$kodeJenisStandar' tidak ditemukan di Akreditasi Cloud.");
        }

        $parentStandar = json_decode(json_encode($parentStandar), true)[0];

        $dataStandar = self::getStandarAkreditasi($kodeJenisStandar);

        if (empty($dataStandar)) {
            throw new \Exception("Data standar akreditasi dengan kode jenis standar '$kodeJenisStandar' tidak ditemukan di Akreditasi Cloud.");
        }

        DB::beginTransaction();

        try {
            $akreditasiJenisStandar = JenisStandar::where('kode_jenis_standar', $kodeJenisStandar)
                ->where('apakah_data_default', true)
                ->first();
            if (empty($akreditasiJenisStandar)) {
                // create jenis standar akreditasi
                $akreditasiJenisStandar = JenisStandar::create([
                    'kode_jenis_standar' => $parentStandar['kodejenisstandar'],
                    'nama_jenis_standar' => $parentStandar['jenisstandar'],
                    'apakah_data_default' => true,
                ]);
            } else {
                // update jenis standar akreditasi
                $akreditasiJenisStandar->update([
                    'kode_jenis_standar' => $parentStandar['kodejenisstandar'],
                    'nama_jenis_standar' => $parentStandar['jenisstandar'],
                ]);
            }

            foreach ($dataStandar as $standarItem) {
                $akreditasiStandar = AkreditasiStandar::where('id_jenis_standar', $akreditasiJenisStandar->id)
                    ->where('apakah_data_default', true)
                    ->where('kode_standar', $standarItem['kodestandar'])
                    ->first();
                if ($akreditasiStandar) {
                    $akreditasiStandar->update([
                        'kode_standar' => $standarItem['kodestandar'],
                        'nama_standar' => $standarItem['standarakreditasi'],
                        'id_jenis_standar' => $akreditasiJenisStandar->id,
                    ]);
                } else {
                    AkreditasiStandar::create([
                        'kode_standar' => $standarItem['kodestandar'],
                        'nama_standar' => $standarItem['standarakreditasi'],
                        'id_jenis_standar' => $akreditasiJenisStandar->id,
                        'apakah_data_default' => true,
                    ]);
                }
            }

            // remove untracked standar records (not present in synced data)
            $syncedKodeStandar = array_map(fn($item) => $item['kodestandar'], $dataStandar);

            $untrackedStandar = AkreditasiStandar::where('id_jenis_standar', $akreditasiJenisStandar->id)
                ->where('apakah_data_default', true)
                ->whereNotIn('kode_standar', $syncedKodeStandar)
                ->get();

            foreach ($untrackedStandar as $standarRecord) {
                try {
                    $standarRecord->delete();
                } catch (QueryException $e) {
                    Log::warning("Sync Standar: Skipped deleting AkreditasiStandar ID {$standarRecord->id} due to constraint: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Gagal migrasi standar akreditasi: " . $e->getMessage());
        }

        DB::commit();

        return true;
    }

    public function getSyaratPerlu($kodePenilaianPanduan, $kodeProgram = null)
    {
        $statusAkreditasiSql = "select kodestatus, statusakreditasi from akreditasi.ms_statusakreditasi";
        $statusAkreditasi = DB::connection('akreditasicloud')->select($statusAkreditasiSql);
        $statusAkreditasi = collect($statusAkreditasi)->pluck('statusakreditasi', 'kodestatus')->toArray();

        $jenisSyarat = [
            'T' => 'Terakreditasi',
            'P' => 'Peringkat',
        ];

        $dataSql = "select a.*
            from akreditasi.ms_syaratperlu a
            join akreditasi.ms_butirspmi b using(nobutirspmi, kodespmi)
            where a.kodespmi = ?";

        $bindings = [$kodePenilaianPanduan];

        if (!empty($kodeProgram)) {
            $dataSql .= " and a.kodeprogram = ?";
            $bindings[] = $kodeProgram;
        }

        $dataSql .= " order by a.kodeprogram, a.kodestatus desc, a.jenis desc, b.infoleft";

        $data = json_decode(json_encode(DB::connection('akreditasicloud')->select($dataSql, $bindings)), true);

        $arrData = [];
        foreach ($data as $i => $row) {
            $arrData[$i] = [
                'kodeprogram' => $row['kodeprogram'],
                'kodespmi' => $row['kodespmi'],
                'nobutirspmi' => $row['nobutirspmi'],
                'kodeakreditasi' => $row['kodestatus'],
                'statusakreditasi' => $statusAkreditasi[$row['kodestatus']] ?? null,
                'kodesyarat' => $row['jenis'],
                'jenissyarat' => $jenisSyarat[$row['jenis']] ?? null,
                'skor' => $row['skor'],
            ];
        }

        return $arrData;
    }

    public function syncSyaratPerlu($kodePenilaianPanduan, $dataSyaratPerlu = null)
    {
        $panduanPenilaian = PenilaianPanduan::where('kode_penilaian_panduan', $kodePenilaianPanduan)
            ->where('apakah_data_default', true)
            ->first();
        if (empty($panduanPenilaian)) {
            throw new \Exception("Panduan penilaian dengan kode '$kodePenilaianPanduan' tidak ditemukan.");
        }

        $idPanduanPenilaian = $panduanPenilaian->id;

        if (is_null($dataSyaratPerlu)) {
            $dataSyaratPerlu = self::getSyaratPerlu($kodePenilaianPanduan);

            if (empty($dataSyaratPerlu)) {
                throw new \Exception("Data syarat perlu dengan kode '$kodePenilaianPanduan' tidak ditemukan di Akreditasi Cloud.");
            }
        }

        if (empty($dataSyaratPerlu)) {
            return true;
        }

        // Referensi Matriks Penilaian
        $mappedNomorPenilaian = array_map(fn($item) => $item['nobutirspmi'], $dataSyaratPerlu);
        $mappedNomorPenilaian = array_unique($mappedNomorPenilaian);
        $refPenilaianMatriks = PenilaianMatriks::where('id_penilaian_panduan', $idPanduanPenilaian)
            ->whereIn('nomor_penilaian', $mappedNomorPenilaian)
            ->pluck('id', 'nomor_penilaian')->toArray();

        // Referensi Peringkat Akreditasi
        $mappedKodePeringkat = array_map(fn($item) => $item['kodeakreditasi'], $dataSyaratPerlu);
        $mappedKodePeringkat = array_unique($mappedKodePeringkat);
        $refAkreditasiPeringkat = AkreditasiPeringkat::whereIn('kode_peringkat', $mappedKodePeringkat)
            ->pluck('id', 'kode_peringkat')->toArray();

        // Collect synced composite keys for cleanup
        $syncedKeys = [];

        DB::beginTransaction();
        try {
            foreach ($dataSyaratPerlu as $item) {
                $idPenilaianMatriks = $refPenilaianMatriks[$item['nobutirspmi']] ?? null;
                $idAkreditasiPeringkat = $refAkreditasiPeringkat[$item['kodeakreditasi']] ?? null;

                if (empty($idPenilaianMatriks)) {
                    continue;
                }

                $payload = [
                    'id_penilaian_panduan' => $idPanduanPenilaian,
                    'id_penilaian_matriks' => $idPenilaianMatriks,
                    'id_akreditasi_peringkat' => $idAkreditasiPeringkat,
                    'jenis_syarat_akreditasi' => $item['kodesyarat'],
                    'nilai_syarat_akreditasi' => $item['skor'],
                    'apakah_data_default' => true,
                ];

                AkreditasiSyarat::updateOrCreate(
                    [
                        'id_penilaian_panduan' => $idPanduanPenilaian,
                        'id_penilaian_matriks' => $idPenilaianMatriks,
                        'apakah_data_default' => true,
                    ],
                    $payload
                );

                $syncedKeys[] = $idPenilaianMatriks;
            }

            // Remove untracked syarat records (not present in synced data)
            $syncedKeys = array_unique($syncedKeys);

            $untrackedSyarat = AkreditasiSyarat::where('id_penilaian_panduan', $idPanduanPenilaian)
                ->where('apakah_data_default', true)
                ->whereNotIn('id_penilaian_matriks', $syncedKeys)
                ->get();

            foreach ($untrackedSyarat as $syaratRecord) {
                try {
                    $syaratRecord->delete();
                } catch (QueryException $e) {
                    Log::warning("Sync SyaratPerlu: Skipped deleting AkreditasiSyarat ID {$syaratRecord->id} due to constraint: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Gagal migrasi syarat perlu akreditasi: " . $e->getMessage());
        }

        DB::commit();

        return true;
    }
}
