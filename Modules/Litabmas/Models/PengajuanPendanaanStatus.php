<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanStatus extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'status_penilaian_administrasi',
        'status_penentuan_nominasi',
        'status_penentuan_pendanaan',
        'status_penilaian_isian_proposal',
        'status_penilaian_presentasi_proposal',
        'status_penilaian_progress_report',
        'status_penilaian_output',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'unique' => true, 'options' => PengajuanPendanaan::class],
        'status_penilaian_administrasi' => ['options' => self::PENILAIAN_ADMINISTRASI_OPTIONS],
        'status_penentuan_nominasi' => ['options' => self::PENENTUAN_NOMINASI_OPTIONS],
        'status_penentuan_pendanaan' => ['maxlength' => 255, 'options' => self::PENENTUAN_PENDANAAN_OPTIONS],
        'status_penilaian_isian_proposal' => ['maxlength' => 255, self::PENILAIAN_ISIAN_PROPOSAL_OPTIONS],
        'status_penilaian_presentasi_proposal' => ['maxlength' => 255, self::PENILAIAN_PRESENTASI_OPTIONS],
        'status_penilaian_progress_report' => ['maxlength' => 255, self::PENILAIAN_PROGRESS_REPORT_OPTIONS],
        'status_penilaian_output' => ['maxlength' => 255, self::PENILAIAN_OUTPUT_OPTIONS],
    ];


    // [START] Status Penilaian Administrasi
    const PENILAIAN_ADMINISTRASI_BELUM_DIVALIDASI = null;
    const PENILAIAN_ADMINISTRASI_PROSES_VALIDASI = 'proses_validasi';
    const PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_DOKUMEN = 'tidak_lolos_dokumen';
    const PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_SIMILARITY_AI = 'tidak_lolos_similarity_ai';
    const PENILAIAN_ADMINISTRASI_LOLOS = 'lolos_administrasi';
    const PENILAIAN_ADMINISTRASI_OPTIONS = [
        self::PENILAIAN_ADMINISTRASI_BELUM_DIVALIDASI => 'Belum Divalidasi',
        self::PENILAIAN_ADMINISTRASI_PROSES_VALIDASI => 'Proses Validasi',
        self::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_DOKUMEN => 'Tidak Lolos Administrasi', // secara text sama kyk similariy & ai tapi beda alasan kenapa tidak lolosnya
        self::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_SIMILARITY_AI => 'Tidak Lolos Administrasi',
        self::PENILAIAN_ADMINISTRASI_LOLOS => 'Lolos Administrasi',
    ];
    const STATUS_PENILAIAN_ADMINISTRASI = [ // adalah nama kolom nya
            // ketika belum melakukan validasi sama sekali
        self::PENILAIAN_ADMINISTRASI_BELUM_DIVALIDASI => [
            'value' => self::PENILAIAN_ADMINISTRASI_BELUM_DIVALIDASI,
            'text' => 'Belum Divalidasi',
            'variant' => 'warning',
        ],
            // ketika divalidasi tapi tidak lolos dokumen/dokumen dinyatakan tidak lengkap
        self::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_DOKUMEN => [
            'value' => self::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_DOKUMEN,
            'text' => 'Tidak Lolos Administrasi',
            'variant' => 'danger'
        ],
            // ketika divalidasi dan dokumen lengkap
        self::PENILAIAN_ADMINISTRASI_PROSES_VALIDASI => [
            'value' => self::PENILAIAN_ADMINISTRASI_PROSES_VALIDASI,
            'text' => 'Proses Validasi',
            'variant' => 'warning',
        ],
            // ketika divalidasi, dokumen lengkap tapi tidak lolos similarity & ai
        self::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_SIMILARITY_AI => [
            'value' => self::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_SIMILARITY_AI,
            'text' => 'Tidak Lolos Administrasi',
            'variant' => 'danger'
        ],
            // ketika divalidasi, dokumen lengkap dan lolos similarity & ai
        self::PENILAIAN_ADMINISTRASI_LOLOS => [
            'value' => self::PENILAIAN_ADMINISTRASI_LOLOS,
            'text' => 'Lolos Administrasi',
            'variant' => 'success'
        ],
    ];
    // [END] Status Penilaian Administrasi

    // [START] Status Penentuan Nominasi
    const PENENTUAN_NOMINASI_BELUM_DINOMINASIKAN = null;
    const PENENTUAN_NOMINASI_LOLOS_NOMINASI = 'lolos_nominasi';
    const PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI = 'tidak_lolos_nominasi';

    const PENENTUAN_NOMINASI_OPTIONS = [
        self::PENENTUAN_NOMINASI_BELUM_DINOMINASIKAN => 'Belum Ditentukan',
        self::PENENTUAN_NOMINASI_LOLOS_NOMINASI => 'Lolos Nominasi',
        self::PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI => 'Tidak Lolos Nominasi',
    ];

    const STATUS_PENENTUAN_NOMINASI = [ // adalah nama kolom nya
        self::PENENTUAN_NOMINASI_BELUM_DINOMINASIKAN => [
            'value' => self::PENENTUAN_NOMINASI_BELUM_DINOMINASIKAN,
            'text' => 'Belum Ditentukan',
            'variant' => 'warning',
        ],
        self::PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI => [
            'value' => self::PENENTUAN_NOMINASI_TIDAK_LOLOS_NOMINASI,
            'text' => 'Tidak Lolos Nominasi',
            'variant' => 'danger'
        ],
        self::PENENTUAN_NOMINASI_LOLOS_NOMINASI => [
            'value' => self::PENENTUAN_NOMINASI_LOLOS_NOMINASI,
            'text' => 'Lolos Nominasi',
            'variant' => 'success',
        ],
    ];
    // [END] Status Penentuan Nominasi

    // [START] Status Penentuan Pendanaan
    const PENENTUAN_PENDANAAN_BELUM_DITENTUKAN = null;
    const PENENTUAN_PENDANAAN_LOLOS_PENDANAAN = 'lolos_pendanaan';
    const PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN = 'tidak_lolos_pendanaan';

    const PENENTUAN_PENDANAAN_OPTIONS = [
        self::PENENTUAN_PENDANAAN_BELUM_DITENTUKAN => 'Belum Ditentukan',
        self::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN => 'Lolos Pendanaan',
        self::PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN => 'Tidak Lolos Pendanaan',
    ];

    const STATUS_PENENTUAN_PENDANAAN = [
        self::PENENTUAN_PENDANAAN_BELUM_DITENTUKAN => [
            'value' => self::PENENTUAN_PENDANAAN_BELUM_DITENTUKAN,
            'text' => 'Belum Ditentukan',
            'variant' => 'warning',
        ],
        self::PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN => [
            'value' => self::PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN,
            'text' => 'Tidak Lolos Pendanaan',
            'variant' => 'danger'
        ],
        self::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN => [
            'value' => self::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN,
            'text' => 'Lolos Pendanaan',
            'variant' => 'success',
        ],
    ];
    // [END] Status Penentuan Pendanaan

    // [START] Status Penilaian Presentasi Proposal
    const PENILAIAN_PRESENTASI_BELUM_DINILAI = null;
    const PENILAIAN_PRESENTASI_PROSES_PENILAIAN = 'proses_penilaian';
    const PENILAIAN_PRESENTASI_SUDAH_DINILAI = 'sudah_dinilai';
    const PENILAIAN_PRESENTASI_OPTIONS = [
        self::PENILAIAN_PRESENTASI_BELUM_DINILAI => 'Belum Dinilai',
        self::PENILAIAN_PRESENTASI_PROSES_PENILAIAN => 'Proses Penilaian',
        self::PENILAIAN_PRESENTASI_SUDAH_DINILAI => 'Sudah Dinilai',
    ];

    const STATUS_PENILAIAN_PRESENTASI_PROPOSAL = [ // adalah nama kolom nya
        self::PENILAIAN_PRESENTASI_BELUM_DINILAI => [
            'value' => self::PENILAIAN_PRESENTASI_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::PENILAIAN_PRESENTASI_PROSES_PENILAIAN => [
            'value' => self::PENILAIAN_PRESENTASI_PROSES_PENILAIAN,
            'text' => 'Proses Penilaian',
            'variant' => 'warning',
        ],
        self::PENILAIAN_PRESENTASI_SUDAH_DINILAI => [
            'value' => self::PENILAIAN_PRESENTASI_SUDAH_DINILAI,
            'text' => 'Sudah Dinilai',
            'variant' => 'success'
        ],
    ];
    // [END] Status Penilaian Presentasi Proposal

    // [START] Status Penilaian Isian Proposal
    const PENILAIAN_ISIAN_PROPOSAL_BELUM_DINILAI = null;
    const PENILAIAN_ISIAN_PROPOSAL_PROSES_PENILAIAN = 'proses_penilaian';
    const PENILAIAN_ISIAN_PROPOSAL_SUDAH_DINILAI = 'sudah_dinilai';
    const PENILAIAN_ISIAN_PROPOSAL_OPTIONS = [
        self::PENILAIAN_ISIAN_PROPOSAL_BELUM_DINILAI => 'Belum Dinilai',
        self::PENILAIAN_ISIAN_PROPOSAL_PROSES_PENILAIAN => 'Proses Penilaian',
        self::PENILAIAN_ISIAN_PROPOSAL_SUDAH_DINILAI => 'Sudah Dinilai',
    ];
    const STATUS_PENILAIAN_ISIAN_PROPOSAL = [ // adalah nama kolom nya
        self::PENILAIAN_ISIAN_PROPOSAL_BELUM_DINILAI => [
            'value' => self::PENILAIAN_ISIAN_PROPOSAL_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::PENILAIAN_ISIAN_PROPOSAL_PROSES_PENILAIAN => [
            'value' => self::PENILAIAN_ISIAN_PROPOSAL_PROSES_PENILAIAN,
            'text' => 'Proses Penilaian',
            'variant' => 'warning',
        ],
        self::PENILAIAN_ISIAN_PROPOSAL_SUDAH_DINILAI => [
            'value' => self::PENILAIAN_ISIAN_PROPOSAL_SUDAH_DINILAI,
            'text' => 'Sudah Dinilai',
            'variant' => 'success'
        ],
    ];
    // [END] Status Penilaian Isian Proposal

    // [START] Status Penilaian Progress Report
    const PENILAIAN_PROGRESS_REPORT_BELUM_DINILAI = null;
    const PENILAIAN_PROGRESS_REPORT_DIREVISI = 'direvisi';
    const PENILAIAN_PROGRESS_REPORT_DISETUJUI = 'disetujui';
    const PENILAIAN_PROGRESS_REPORT_OPTIONS = [
        self::PENILAIAN_PROGRESS_REPORT_BELUM_DINILAI => 'Belum Dinilai',
        self::PENILAIAN_PROGRESS_REPORT_DIREVISI => 'Direvisi',
        self::PENILAIAN_PROGRESS_REPORT_DISETUJUI => 'Disetujui',
    ];
    const STATUS_PENILAIAN_PROGRESS_REPORT = [ // adalah nama kolom nya
        self::PENILAIAN_PROGRESS_REPORT_BELUM_DINILAI => [
            'value' => self::PENILAIAN_PROGRESS_REPORT_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::PENILAIAN_PROGRESS_REPORT_DIREVISI => [
            'value' => self::PENILAIAN_PROGRESS_REPORT_DIREVISI,
            'text' => 'Direvisi',
            'variant' => 'warning',
        ],
        self::PENILAIAN_PROGRESS_REPORT_DISETUJUI => [
            'value' => self::PENILAIAN_PROGRESS_REPORT_DISETUJUI,
            'text' => 'Disetujui',
            'variant' => 'success'
        ],
    ];
    // [END] Status Penilaian Progress Report

    // [START] Status Penilaian Output
    const PENILAIAN_OUTPUT_BELUM_DINILAI = null;
    const PENILAIAN_OUTPUT_DIREVISI = 'direvisi';
    const PENILAIAN_OUTPUT_DISETUJUI = 'disetujui';
    const PENILAIAN_OUTPUT_OPTIONS = [
        self::PENILAIAN_OUTPUT_BELUM_DINILAI => 'Belum Dinilai',
        self::PENILAIAN_OUTPUT_DIREVISI => 'Direvisi',
        self::PENILAIAN_OUTPUT_DISETUJUI => 'Disetujui',
    ];
    const STATUS_PENILAIAN_OUTPUT = [ // adalah nama kolom nya
        self::PENILAIAN_OUTPUT_BELUM_DINILAI => [
            'value' => self::PENILAIAN_OUTPUT_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::PENILAIAN_OUTPUT_DIREVISI => [
            'value' => self::PENILAIAN_OUTPUT_DIREVISI,
            'text' => 'Direvisi',
            'variant' => 'warning',
        ],
        self::PENILAIAN_OUTPUT_DISETUJUI => [
            'value' => self::PENILAIAN_OUTPUT_DISETUJUI,
            'text' => 'Disetujui',
            'variant' => 'success'
        ],
    ];
    // [END] Status Penilaian Output

    /**
     * Get status info (value, text, & variant) by field and value.
     *
     * @param $field
     * @param $value
     * @return mixed|null
     */
    public static function getStatusInfo($field, $value) {
        $const = strtoupper($field);
        $const = "self::{$const}"; // get by nama kolom

        // check if const exists
        if (defined($const)) {
            $const = constant($const);
            if (array_key_exists($value, $const)) {
                return $const[$value];
            }
        }

        return null;
    }
}
