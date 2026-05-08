<?php

namespace Modules\Kerjasama\Services;

use Modules\Kerjasama\Models\JenisDokumen;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Mitra;
use Illuminate\Support\Facades\DB;
use Modules\Kerjasama\Models\Kegiatan;

class DashboardService
{

    public function countJenisDokumen()
    {
        $result = DB::select("
        SELECT
            jd.jenis_dokumen,
            COUNT(k.id) AS kerjasama_count
        FROM
            kerjasama.jenis_dokumen jd
        LEFT JOIN
            kerjasama.kerjasama k
            ON k.id_jenis_dokumen = jd.id
            AND k.waktu_dihapus IS NULL
        WHERE
            jd.waktu_dihapus IS NULL
        GROUP BY
            jd.id, jd.jenis_dokumen
        HAVING
            COUNT(k.id) > 0
        ORDER BY
            jd.jenis_dokumen
    ");
        return $result;
    }


    public function countGroupedByJenisAndBentukKegiatan()
    {
        $results =  Kegiatan::getGroupedByBentukAndJenisKegiatan();

        return $results;
    }

    public function mostUnitKerja()
    {
        $result = Kerjasama::getGroupedByUnitKerja();
        return $result;
    }


    public function pieProfilMitraKerjasama()
    {

        $result = Mitra::countByJenisMitra();
        return $result;
    }


    public function pieRuangLingkupMitra()
    {

        $result = Mitra::countByLingkupMitra();

        return $result;
    }

    public function mostKriteriaMitra()
    {

        $result = Mitra::countByKriteriaMitra();
        return $result;
    }

    public function provinsiSebaranMitra()
    {

        $result = Mitra::countByProvinsi();
        return $result;
    }

    public function implementasiKegiatan()
    {
        $result = Kegiatan::countKegiatanHasilPelaksanaan();
        return $result;
    }
    public function implementasiKerjasama()
    {

        $result = Kerjasama::countKegiatanHasilPelaksanaan();
        return $result;
    }
}
