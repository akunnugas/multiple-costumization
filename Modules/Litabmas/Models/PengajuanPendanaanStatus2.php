<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanStatus2 extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_status';

    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_agenda_kegiatan',
        'status',
    ];

    const LEVEL1_DRAFT = 'draft';

    const LEVEL2_KONFIRMASI_ANGGOTA = 'konfirmasi_anggota';

    const LEVEL3_DIAJUKAN = 'diajukan';

    const LEVEL5_PROSES_SELEKSI_ADMINISTRASI = 'proses_seleksi_administrasi';
    const LEVEL5_TIDAK_LOLOS_ADMINISTRASI = 'tidak_lolos_administrasi_seleksi';
    const LEVEL5_LOLOS_ADMINISTRASI = 'lolos_administrasi_seleksi';

    // optional
    const LEVEL7_PENINJAUAN_PROPOSAL = 'peninjauan_proposal';

    const LEVEL8_NOMINASI_BELUM_DITINJAU = 'proses_nominasi';
    const LEVEL8_TIDAK_LOLOS_NOMINASI = 'tidak_lolos_nominasi';
    const LEVEL8_LOLOS_NOMINASI = 'lolos_nominasi';

    const LEVEL10_BELUM_PENENTUAN_PENDANAAN = 'proses_penentuan_pendanaan';
    const LEVEL10_TIDAK_LOLOS_PENDANAAN = 'tidak_lolos_pendanaan';
    const LEVEL10_LOLOS_PENDANAAN = 'lolos_pendanaan';

    const LEVEL12_PENGUMPULAN_HASIL = 'selesai';

    public static function getLabelStatus($status)
    {
        switch ($status) {
            case self::LEVEL1_DRAFT:
                return 'Draft';
            case self::LEVEL2_KONFIRMASI_ANGGOTA:
                return 'Konfirmasi Anggota';
            case self::LEVEL3_DIAJUKAN:
                return 'Diajukan';
            case self::LEVEL5_PROSES_SELEKSI_ADMINISTRASI:
                return 'Proses Seleksi Administrasi';
            case self::LEVEL5_TIDAK_LOLOS_ADMINISTRASI:
                return 'Tidak Lolos Seleksi Administrasi';
            case self::LEVEL5_LOLOS_ADMINISTRASI:
                return 'Lolos Seleksi Administrasi';
            case self::LEVEL7_PENINJAUAN_PROPOSAL:
                return 'Peninjauan Proposal';
            case self::LEVEL8_NOMINASI_BELUM_DITINJAU:
                return 'Proses Seleksi Nominasi';
            case self::LEVEL8_TIDAK_LOLOS_NOMINASI:
                return 'Tidak Lolos Nominasi';
            case self::LEVEL8_LOLOS_NOMINASI:
                return 'Lolos Nominasi';
            case self::LEVEL10_BELUM_PENENTUAN_PENDANAAN:
                return 'Proses Penentuan Pendanaan';
            case self::LEVEL10_TIDAK_LOLOS_PENDANAAN:
                return 'Tidak Lolos Pendanaan';
            case self::LEVEL10_LOLOS_PENDANAAN:
                return 'Lolos Pendanaan';
            case self::LEVEL12_PENGUMPULAN_HASIL:
                return 'Selesai';
            default:
                return '-';
        }
    }

    public static function getStatusColorVariant($status)
    {
        switch ($status) {
            case self::LEVEL1_DRAFT:
                return 'default';
            case self::LEVEL2_KONFIRMASI_ANGGOTA:
                return 'primary';
            case self::LEVEL3_DIAJUKAN:
                return 'primary';
            case self::LEVEL5_PROSES_SELEKSI_ADMINISTRASI:
                return 'warning';
            case self::LEVEL5_TIDAK_LOLOS_ADMINISTRASI:
                return 'danger';
            case self::LEVEL5_LOLOS_ADMINISTRASI:
                return 'success';
            case self::LEVEL7_PENINJAUAN_PROPOSAL:
                return 'warning';
            case self::LEVEL8_NOMINASI_BELUM_DITINJAU:
                return 'warning';
            case self::LEVEL8_TIDAK_LOLOS_NOMINASI:
                return 'danger';
            case self::LEVEL8_LOLOS_NOMINASI:
                return 'success';
            case self::LEVEL10_BELUM_PENENTUAN_PENDANAAN:
                return 'warning';
            case self::LEVEL10_TIDAK_LOLOS_PENDANAAN:
                return 'danger';
            case self::LEVEL10_LOLOS_PENDANAAN:
                return 'success';
            case self::LEVEL12_PENGUMPULAN_HASIL:
                return 'success';
            default:
                return 'default';
        }
    }
}
