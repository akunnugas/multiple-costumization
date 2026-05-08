<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\Biodata;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // utk tampung id_biodata yg tidak terpakai (yg salah)
        $unUsedIdBiodata = [];

        DB::beginTransaction();

        // 1. remapping pengajuan pendanaan anggota
        $sql = "select distinct ppa.id_biodata, b.id_user
            from litabmas.pengajuan_pendanaan_anggota ppa
            left join core.biodata b on b.id = ppa.id_biodata
            left join litabmas.dosen_eksternal de on de.id_biodata = b.id
            where (b.ref_key_pegawai is null and b.ref_key_mahasiswa is null)
                and de.id is null"; // yg bukan dosen eksternal

        $dataPeneliti = DB::select($sql);
        foreach ($dataPeneliti as $peneliti) {
            // get biodata doublenya (yg benar)
            $biodata = Biodata::where('id_user', $peneliti->id_user)
                ->where('id', '<>', $peneliti->id_biodata)
                ->orderBy('id')
                ->first();
            if (!empty($biodata)) {
                // update pengajuan_pendanaan_anggota ke biodata yg benar
                DB::table('litabmas.pengajuan_pendanaan_anggota')
                    ->where('id_biodata', $peneliti->id_biodata)
                    ->update(['id_biodata' => $biodata->id]);
                $unUsedIdBiodata[] = $peneliti->id_biodata;
            }
        }

        DB::commit();

        DB::beginTransaction();

        // 2. remapping pengajuan pendanaan pembimbing
        $sql = "select distinct ppp.id_biodata, b.id_user
            from litabmas.pengajuan_pendanaan_pembimbing ppp
            left join core.biodata b on b.id = ppp.id_biodata
            left join litabmas.dosen_eksternal de on de.id_biodata = b.id
            where (b.ref_key_pegawai is null and b.ref_key_mahasiswa is null)
                and de.id is null"; // yg bukan dosen eksternal

        $dataPembimbing = DB::select($sql);

        foreach ($dataPembimbing as $pembimbing) {
            // get biodata doublenya (yg benar)
            $biodata = Biodata::where('id_user', $pembimbing->id_user)
                ->where('id', '<>', $pembimbing->id_biodata)
                ->orderBy('id')
                ->first();
            if (!empty($biodata)) {
                // update pengajuan_pendanaan_pembimbing ke biodata yg benar
                DB::table('litabmas.pengajuan_pendanaan_pembimbing')
                    ->where('id_biodata', $pembimbing->id_biodata)
                    ->update(['id_biodata' => $biodata->id]);
                $unUsedIdBiodata[] = $pembimbing->id_biodata;
            }
        }

        DB::commit();

        DB::beginTransaction();

        // 3. remapping pengajuan pendanaan reviewer administrasi
        $sql = "select distinct ppra.id_biodata, b.id_user
            from litabmas.pengajuan_pendanaan_reviewer_administrasi ppra
            left join core.biodata b on b.id = ppra.id_biodata
            left join litabmas.dosen_eksternal de on de.id_biodata = b.id
            where (b.ref_key_pegawai is null and b.ref_key_mahasiswa is null)
                and de.id is null"; // yg bukan dosen eksternal

        $dataReviewerAdministrasi = DB::select($sql);

        foreach ($dataReviewerAdministrasi as $reviewerAdministrasi) {
            // get biodata doublenya (yg benar)
            $biodata = Biodata::where('id_user', $reviewerAdministrasi->id_user)
                ->where('id', '<>', $reviewerAdministrasi->id_biodata)
                ->orderBy('id')
                ->first();
            if (!empty($biodata)) {
                // update pengajuan_pendanaan_reviewer_administrasi ke biodata yg benar
                DB::table('litabmas.pengajuan_pendanaan_reviewer_administrasi')
                    ->where('id_biodata', $reviewerAdministrasi->id_biodata)
                    ->update(['id_biodata' => $biodata->id]);
                $unUsedIdBiodata[] = $reviewerAdministrasi->id_biodata;
            }
        }

        DB::commit();

        DB::beginTransaction();

        // 4. remapping pengajuan pendanaan reviewer kegiatan
        $sql = "select distinct pprk.id_biodata, b.id_user
            from litabmas.pengajuan_pendanaan_reviewer_kegiatan pprk
            left join core.biodata b on b.id = pprk.id_biodata
            left join litabmas.dosen_eksternal de on de.id_biodata = b.id
            where (b.ref_key_pegawai is null and b.ref_key_mahasiswa is null)
                and de.id is null"; // yg bukan dosen eksternal

        $dataReviewerKegiatan = DB::select($sql);

        foreach ($dataReviewerKegiatan as $reviewerKegiatan) {
            // get biodata doublenya (yg benar)
            $biodata = Biodata::where('id_user', $reviewerKegiatan->id_user)
                ->where('id', '<>', $reviewerKegiatan->id_biodata)
                ->orderBy('id')
                ->first();
            if (!empty($biodata)) {
                // update pengajuan_pendanaan_reviewer_kegiatan ke biodata yg benar
                DB::table('litabmas.pengajuan_pendanaan_reviewer_kegiatan')
                    ->where('id_biodata', $reviewerKegiatan->id_biodata)
                    ->update(['id_biodata' => $biodata->id]);
                $unUsedIdBiodata[] = $reviewerKegiatan->id_biodata;
            }
        }

        DB::commit();

        DB::beginTransaction();

        // force delete biodata yg tidak terpakai
        if (!empty($unUsedIdBiodata)) {
            // delete menggunakan konsep looping agar handle data yg kena error di awal
            $biodatas = Biodata::whereNotIn('id', $unUsedIdBiodata)->get();
            try {
                foreach ($biodatas as $biodata) {
                    $biodata->forceDelete();
                }
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::error('Remapping ID Biodata:', [
                    'message' => $th->getMessage(),
                    'data' => $biodata,
                ]);
            }
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
