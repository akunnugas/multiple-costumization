<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisPendaftaran extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jenis_pendaftaran';
    protected $fillable = ['kode_jenis_pendaftaran', 'nama_jenis_pendaftaran', 'tanggal_awal_pendaftaran', 'tanggal_akhir_pendaftaran'];

    /**
     * Constant order for default options in ModelTrait.
     */
    const OPTION_ORDER = 'nama_jenis_pendaftaran';

    /**
     * Jenis Transfer/Pendaftaran
     */
    const CODE_PDB = '0';
    const CODE_PINDAHAN = '1';
    const CODE_ALIHJENJANG = '2';
    const CODE_LINTASJALUR = '3';
    const CODE_RPL = '4';
    const CODE_NAIKKELAS = '5';
    const CODE_AKSELERASI = '6';
    const CODE_MENGULANG = '7';
    const CODE_LANJUTANSEMESTER = '8';
    const CODE_PAB = '9';
    const CODE_PUTUSSEKOLAH = '10';
    const CODE_KELASEKSTENSI = '11';
    const CODE_COURSE = '12';
    const CODE_FASTTRACK = '13';
    const CODE_RPLTRANSFER = '14';
    const CODE_LISTS = [
        self::CODE_PDB => 'Peserta Didik Baru',
        self::CODE_PINDAHAN => 'Pindahan',
        self::CODE_ALIHJENJANG => 'Alih Jenjang',
        self::CODE_LINTASJALUR => 'Lintas Jalur',
        self::CODE_RPL => 'RPL Perolehan SKS',
        self::CODE_NAIKKELAS => 'Naik Kelas',
        self::CODE_AKSELERASI => 'Akselerasi',
        self::CODE_MENGULANG => 'Mengulang',
        self::CODE_LANJUTANSEMESTER => 'Lanjutan Semester',
        self::CODE_PAB => 'Pindahan Alih Bentuk',
        self::CODE_PUTUSSEKOLAH => 'Putus Sekolah',
        self::CODE_KELASEKSTENSI => 'Kelas Ekstensi',
        self::CODE_COURSE => 'Pendidikan Non Gelar (Course)',
        self::CODE_FASTTRACK => 'FAST TRACK',
        self::CODE_RPLTRANSFER => 'RPL Transfer SKS'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_jenis_pendaftaran' => ['required' => true, 'maxlength' => 10, 'unique' => true], // Kode Jenis Pendaftaran
        'nama_jenis_pendaftaran' => ['required' => true, 'maxlength' => 255], // Nama Jenis Pendaftaran
        'tanggal_awal_pendaftaran' => ['nullable' => true, 'type' => 'date'], // Tanggal Awal
        'tanggal_akhir_pendaftaran' => ['nullable' => true, 'type' => 'date'], // Tanggal Akhir
    ];
}
