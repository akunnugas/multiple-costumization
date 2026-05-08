<?php

namespace Modules\Litabmas\Services;

use Modules\Litabmas\Enums\StatusAgendaKegiatanEnum;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;

class PengajuanPendanaanAnggotaService
{
    /**
     * @var PeriodePendanaan
     */
    protected $model = PengajuanPendanaanAnggota::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanAnggota;
    }

    public function apakahSemuaAnggotaMenerimaUndangan(int $idPengajuanPendanaan): bool
    {
        $query = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->where('apakah_ketua', false)
            ->where('jenis_anggota', '<>', PengajuanPendanaanAnggota::JENIS_MAHASISWA);

        $jumlahAnggota = $query->count();

        // jmlh anggota menerim
        $jumlahAnggotaMenerima = $query->where('apakah_undangan_diterima', PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA)
            ->count();

        return $jumlahAnggotaMenerima === $jumlahAnggota;
    }

    /**
     * Get daftar id biodata anggota yang memiliki/tidak memiliki kuota mendaftar berdasarkan aturan periode pendanaan.
     *
     * @param int $idPeriodePendanaan
     * @param int|null $idPengajuanPendanaan
     * @param bool $yangMemilikiKuota
     * @param int|null $maxAnggotaMendaftar
     * @return mixed
     */
    public function getDaftarIdBiodataAnggotaByKuotaMendaftar(
        int $idPeriodePendanaan,
        int $idPengajuanPendanaan = null,
        bool $yangMemilikiKuota = true,
        int $maxAnggotaMendaftar = null
    ): mixed {
        // cek kuota dari periode pendanaan
        $maxAnggotaMendaftar ??= (new PeriodePendanaanService())->getMaxAnggotaMendaftar($idPeriodePendanaan);

        // get semua user yang sudah mendaftar sebagai anggota proposal di pengajuan pendanaan, lalu validasi dengan max anggota mendaftar
        $query = PengajuanPendanaanAnggota::whereHas('pengajuanPendanaan', function ($query) use ($idPeriodePendanaan) {
            $query->join('litabmas.sumber_pendanaan', 'sumber_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
                ->where('sumber_pendanaan.id_periode_pendanaan', $idPeriodePendanaan)
                ->whereNotIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                    StatusAgendaKegiatanEnum::TIDAK_LOLOS_ADMINISTRASI,
                    StatusAgendaKegiatanEnum::TIDAK_LOLOS_NOMINASI,
                    StatusAgendaKegiatanEnum::TIDAK_LOLOS_PENDANAAN
                ]);
        })
            ->when($idPengajuanPendanaan, function ($query) use ($idPengajuanPendanaan) {
                $query->where('id_pengajuan_pendanaan', '<>', $idPengajuanPendanaan);
            })
            ->where('apakah_ketua', false)
            ->select('id_biodata')
            ->groupBy('id_biodata');

        if ($yangMemilikiKuota) {
            $query->havingRaw('COUNT(id_biodata) < ?', [$maxAnggotaMendaftar]);
        } else {
            $query->havingRaw('COUNT(id_biodata) >= ?', [$maxAnggotaMendaftar]);
        }

        return $query->pluck('id_biodata')->toArray();
    }

    /**
     * Cek apakah ketua sudah maksimal mendaftarkan proposal pada periode ini.
     *
     * @param int $idPeriodePendanaan
     * @param int|null $idBiodata
     * @param int|null $excludeIdPengajuanPendanaan
     * @return array
     */
    public function cekMaksimalMendaftarSebagaiKetua(int $idPeriodePendanaan, int $idBiodata = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        // cek apakah ketua sudah maksimal mendaftar di semua pengajuan pendanaan pada periode ini
        $jmlhKetuaTerdaftar = SumberPendanaan::where('id_periode_pendanaan', $idPeriodePendanaan)
            ->join('litabmas.pengajuan_pendanaan as pp', 'pp.id_sumber_pendanaan', '=', 'litabmas.sumber_pendanaan.id')
            ->join('litabmas.pengajuan_pendanaan_anggota as ppa', 'ppa.id_pengajuan_pendanaan', '=', 'pp.id')
            ->where('ppa.apakah_ketua', true)
            ->where('ppa.id_biodata', $idBiodata)
            ->whereNotIn('pp.status_agenda_kegiatan', [
                PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI,
                PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI,
                PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN,
            ])
            ->count();

        // cek maksimal ketua mendaftar
        $maxKetuaMendaftar = (new PeriodePendanaanService())->getMaxKetuaMendaftar($idPeriodePendanaan);

        return [
            'isFull' => $jmlhKetuaTerdaftar >= $maxKetuaMendaftar,
            'max' => $maxKetuaMendaftar,
            'current' => $jmlhKetuaTerdaftar
        ];
    }

    /**
     * Cek apakah ketua sudah maksimal mendaftarkan proposal pada klaster pendanaan tertentu.
     *
     * @param int $idKlasterPendanaan
     * @param int|null $idBiodata
     * @param int|null $excludeIdPengajuanPendanaan
     * @return array
     */
    public function cekMaksimalPengajuanPerKlaster(int $idKlasterPendanaan, int $idBiodata = null, int $excludeIdPengajuanPendanaan = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;

        // get info klaster pendanaan
        $klasterPendanaan = KlasterPendanaan::find($idKlasterPendanaan);

        // jika klaster tidak mengaktifkan multi ajuan, maka hanya bisa 1x
        if (!$klasterPendanaan || !$klasterPendanaan->apakah_bisa_multi_ajuan) {
            // cek apakah sudah pernah mengajukan di klaster ini
            $jmlhPengajuanDiKlaster = PengajuanPendanaan::where('id_klaster_pendanaan', $idKlasterPendanaan)
                ->join('litabmas.pengajuan_pendanaan_anggota as ppa', 'ppa.id_pengajuan_pendanaan', '=', 'litabmas.pengajuan_pendanaan.id')
                ->where('ppa.apakah_ketua', true)
                ->where('ppa.id_biodata', $idBiodata)
                ->when($excludeIdPengajuanPendanaan, function ($query, $excludeId) {
                    return $query->where('litabmas.pengajuan_pendanaan.id', '!=', $excludeId);
                })
                ->whereNotIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                    PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI,
                    PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI,
                    PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN,
                ])
                ->count();

            return [
                'isFull' => $jmlhPengajuanDiKlaster >= 1,
                'max' => 1,
                'current' => $jmlhPengajuanDiKlaster,
                'message' => 'Anda hanya dapat mengajukan proposal 1 kali pada klaster ini.'
            ];
        }

        // jika multi ajuan diaktifkan, cek maksimal ajuan per user (jika ada)
        $maxAjuanPerUser = $klasterPendanaan->maksimal_ajuan_per_user;

        // jika null atau 0, berarti unlimited
        if (empty($maxAjuanPerUser)) {
            return [
                'isFull' => false,
                'max' => null,
                'current' => 0,
                'message' => 'Anda dapat mengajukan proposal tanpa batas pada klaster ini.'
            ];
        }

        // cek jumlah pengajuan di klaster ini
        $jmlhPengajuanDiKlaster = PengajuanPendanaan::where('id_klaster_pendanaan', $idKlasterPendanaan)
            ->join('litabmas.pengajuan_pendanaan_anggota as ppa', 'ppa.id_pengajuan_pendanaan', '=', 'litabmas.pengajuan_pendanaan.id')
            ->where('ppa.apakah_ketua', true)
            ->where('ppa.id_biodata', $idBiodata)
            ->when($excludeIdPengajuanPendanaan, function ($query, $excludeId) {
                return $query->where('litabmas.pengajuan_pendanaan.id', '!=', $excludeId);
            })
            ->whereNotIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI,
                PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI,
                PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN,
            ])
            ->count();

        return [
            'isFull' => $jmlhPengajuanDiKlaster >= $maxAjuanPerUser,
            'max' => $maxAjuanPerUser,
            'current' => $jmlhPengajuanDiKlaster,
            'message' => "Anda sudah mencapai batas maksimal pengajuan pada klaster ini ({$maxAjuanPerUser} kali)."
        ];
    }
}
