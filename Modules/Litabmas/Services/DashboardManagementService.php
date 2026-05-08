<?php

namespace Modules\Litabmas\Services;

use Illuminate\Support\Facades\DB;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;

class DashboardManagementService
{
    public function loadMappingTugas($idBiodata, $listIdProposalTerlibat)
    {
        $listDataReviewer = PengajuanPendanaanReviewer::whereIn('id_pengajuan_pendanaan', $listIdProposalTerlibat)
            ->where('id_biodata', $idBiodata)
            ->get();

        $mappingTugas = [];
        foreach ($listDataReviewer as $reviewer) {
            if ($reviewer->apakah_review_proposal) {
                $mappingTugas[$reviewer['id_pengajuan_pendanaan']][PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_PROPOSAL] = 1;
            }

            if ($reviewer->apakah_review_luaran) {
                $mappingTugas[$reviewer['id_pengajuan_pendanaan']][PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_LUARAN] = 1;
            }

            if ($reviewer->apakah_review_antara) {
                $mappingTugas[$reviewer['id_pengajuan_pendanaan']][PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_LAPORAN_ANTARA] = 1;
            }
        }

        return $mappingTugas;
    }

    public function loadDataAnggota($idBiodata, $dataJadwalProposal)
    {
        $listId = [];
        foreach ($dataJadwalProposal as $jadwal) {
            if (!in_array($jadwal['id_pengajuan_pendanaan'], $listId)) {
                $listId[] = $jadwal['id_pengajuan_pendanaan'];
            }
        }

        $dataAnggota = PengajuanPendanaanAnggota::whereIn('id_pengajuan_pendanaan', $listId)
            ->where('id_biodata', $idBiodata)
            ->pluck('id', 'id_pengajuan_pendanaan')
            ->toArray();

        return $dataAnggota;
    }

    public function formatCalendarData($dataJadwalProposal)
    {
        $formatedData = [];

        foreach ($dataJadwalProposal as $jadwal) {
            $formatedData[] = [
                'start' => $jadwal['waktu_pelaksanaan'],
                'end' => $jadwal['waktu_pelaksanaan'],
                'title' => $jadwal['nama_kegiatan'],
            ];
        }

        return $formatedData;
    }

    public function getSebaranStatusProposal(array $filter = [])
    {
        $sqlWhere = " where pp.waktu_dihapus is null";
        $sqlWhere .= " AND pp.status_agenda_kegiatan <> '" . PengajuanPendanaanStatus2::LEVEL1_DRAFT . "'";
        $sqlBinding = [];

        if (!empty($filter['jenis_pendanaan'])) {
            $sqlWhere .= " AND pp.kode_jenis_pendanaan = :jenis_pendanaan";
            $sqlBinding['jenis_pendanaan'] = $filter['jenis_pendanaan'];
        }

        if (!empty($filter['unit'])) {
            $sqlWhere .= " AND sp.id_unit_kerja = :unit_kerja";
            $sqlBinding['unit_kerja'] = $filter['unit'];
        }

        if (!empty($filter['periode_pendanaan'])) {
            $sqlWhere .= " AND sp.id_periode_pendanaan = :periode_pendanaan";
            $sqlBinding['periode_pendanaan'] = $filter['periode_pendanaan'];
        }

        $sql = "select
                    pp.id,
                    pp.judul_penelitian,
                    (array_agg(ps.status ORDER BY ps.waktu_dibuat))[1] as status
                from litabmas.pengajuan_pendanaan pp
                join litabmas.sumber_pendanaan sp on pp.id_sumber_pendanaan = sp.id
                join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan
                left join litabmas.pengajuan_pendanaan_status ps on ps.id_pengajuan_pendanaan = pp.id
                " . $sqlWhere . "
                group by pp.id, pp.judul_penelitian;
            ";

        $result = DB::select($sql, $sqlBinding);

        //convert data dari db result ke data matang yang dibutuhkan
        $finalData = [];
        foreach ($result as $item) {
            if (empty($item->status)) {
                $finalData[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN][] = $item->id;
                continue;
            }

            if (!isset($finalData[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN]) || !in_array($item->id, $finalData[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN])) {
                $finalData[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN][] = $item->id;
            }

            $finalData[$item->status][] = $item->id;
        }

        $dashboardData = [];
        foreach ($finalData as $key => $item) {
            $dashboardData[$key] = [
                'label' => PengajuanPendanaanStatus2::getLabelStatus($key),
                'jumlah' => count($item)
            ];
        }

        return $dashboardData;
    }

    /**
     * Get sumber pendanaan sesuai filter.
     *
     * @param array $filter
     * @return mixed|null
     */
    public function getSumberPendanaanDashboard(array $filter = [])
    {
        $sqlBinding = [];
        $joinCondition = "";
        
        if (!empty($filter['jenis_pendanaan'])) {
            $joinCondition .= " AND kp.kode_jenis_pendanaan = :jenis_pendanaan";
            $sqlBinding['jenis_pendanaan'] = $filter['jenis_pendanaan'];
        }

        $sql = " SELECT
                f.id id_sumber_pendanaan,
                f.nama_sumber_pendanaan,
                f.total_pendanaan total_pendanaan_sumber_pendanaan,
                f.mata_uang mata_uang_sumber_pendanaan,
                f.id_periode_pendanaan,
                f.id_unit_kerja,
                kp.id id_klaster,
                kp.kode_jenis_pendanaan kode_jenis_pendanaan_klaster,
                kp.nama_klaster,
                kp.maksimal_anggaran maksimal_anggaran_klaster,
                kp.mata_uang mata_uang_klaster,
                pp.id id_pengajuan,
                pp.mata_uang mata_uang_pengajuan,
                pp.nominal_anggaran_diajukan,
                pp.nominal_anggaran_disetujui,
                pp.status_agenda_kegiatan
            FROM  litabmas.sumber_pendanaan f
            join litabmas.klaster_pendanaan kp on kp.id_sumber_pendanaan  = f.id and kp.waktu_dihapus is null {$joinCondition}
            left join litabmas.pengajuan_pendanaan pp on pp.id_klaster_pendanaan = kp.id and pp.id_sumber_pendanaan = f.id  and pp.waktu_dihapus is null
            ";

        $sqlWhere = " WHERE f.waktu_dihapus IS NULL";
        
        if (!empty($filter['unit'])) {
            $sqlWhere .= " AND f.id_unit_kerja = :unit_kerja";
            $sqlBinding['unit_kerja'] = $filter['unit'];
        }

        if (!empty($filter['periode_pendanaan'])) {
            $sqlWhere .= " AND f.id_periode_pendanaan = :periode_pendanaan";
            $sqlBinding['periode_pendanaan'] = $filter['periode_pendanaan'];
        }

        $sql .= $sqlWhere;

        $result = DB::select($sql, $sqlBinding);

        $finalData = [];
        $klasterProcessed = [];

        foreach ($result as $item) {
            $id = $item->id_sumber_pendanaan;
            $idKlaster = $item->id_klaster;

            if (!isset($finalData[$id])) {
                $finalData[$id] = [
                    'nama_sumber_pendanaan' => $item->nama_sumber_pendanaan,
                    'mata_uang' => $item->mata_uang_sumber_pendanaan,
                    'maksimal_anggaran_klaster' => 0,
                    'nominal_anggaran_disetujui' => 0,
                    'jumlah_proposal' => 0,
                ];
            }

            if (!isset($klasterProcessed[$id][$idKlaster])) {
                $finalData[$id]['maksimal_anggaran_klaster'] += $item->maksimal_anggaran_klaster;
                $klasterProcessed[$id][$idKlaster] = true;
            }

            $finalData[$id]['nominal_anggaran_disetujui'] += $item->nominal_anggaran_disetujui;

            if ($item->id_pengajuan) {
                $finalData[$id]['jumlah_proposal']++;
            }
        }

        //membuat data total dari data final yang dibutuhkan
        $totalData = [
            'maksimal_anggaran_klaster' => 0,
            'nominal_anggaran_disetujui' => 0,
            'jumlah_proposal' => 0,
        ];

        foreach ($finalData as $key => $item) {
            $totalData['maksimal_anggaran_klaster'] += $item['maksimal_anggaran_klaster'];
            $totalData['nominal_anggaran_disetujui'] += $item['nominal_anggaran_disetujui'];
            $totalData['jumlah_proposal'] += $item['jumlah_proposal'];

            $finalData[$key]['anggaran_tersisa'] = $item['maksimal_anggaran_klaster'] - $item['nominal_anggaran_disetujui'];
        }

        $totalData['anggaran_tersisa'] = $totalData['maksimal_anggaran_klaster'] - $totalData['nominal_anggaran_disetujui'];
        $totalData['nominal_anggaran_disetujui_persentase'] = ($totalData['maksimal_anggaran_klaster'] != 0) ? $totalData['nominal_anggaran_disetujui'] / $totalData['maksimal_anggaran_klaster'] * 100 : 0;

        $dashboardData = [
            'data' => $finalData,
            'total' => $totalData,
        ];

        return $dashboardData;
    }

    public function getStatusOutputProposal(array $filter = [])
    {
        $sqlWhere = " where pp.waktu_dihapus is null
            and
            (pp.status_agenda_kegiatan = '" . PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN . "'
            or pp.status_agenda_kegiatan = '" . PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL . "')";

        $sqlBinding = [];

        $joinKlaster = " join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan and kp.waktu_dihapus is null";
        if (!empty($filter['jenis_pendanaan'])) {
            $joinKlaster .= " AND kp.kode_jenis_pendanaan = :jenis_pendanaan";
            $sqlBinding['jenis_pendanaan'] = $filter['jenis_pendanaan'];
        }

        if (!empty($filter['unit'])) {
            $sqlWhere .= " AND sp.id_unit_kerja = :unit_kerja";
            $sqlBinding['unit_kerja'] = $filter['unit'];
        }

        if (!empty($filter['periode_pendanaan'])) {
            $sqlWhere .= " AND sp.id_periode_pendanaan = :periode_pendanaan";
            $sqlBinding['periode_pendanaan'] = $filter['periode_pendanaan'];
        }

        $sql = "select
                    pp.id,
                    pp.judul_penelitian,
                    pp.status_agenda_kegiatan,
                    array_agg(concat(op.id_jenis_output_penelitian,
                    '|',
                        CASE
                            WHEN lo.id_dokumen_output IS NOT NULL THEN 1
                            ELSE 0
                        END,
                    '|',
                        case
                            when lo.status_output = 'disetujui' then 1
                            else 0
                        end
                    )) output_penelitian
                from litabmas.pengajuan_pendanaan pp
                join litabmas.sumber_pendanaan sp on pp.id_sumber_pendanaan = sp.id and sp.waktu_dihapus is null
                " . $joinKlaster . "
                join litabmas.klaster_pendanaan_output_penelitian op on op.id_klaster_pendanaan = kp.id and op.waktu_dihapus is null
                join litabmas.jenis_output_penelitian jo on jo.id = op.id_jenis_output_penelitian and jo.waktu_dihapus is null
                left join (
                    select
                        a.id id_laporan_output,
                        a.id_dokumen_output,
                        a.status_output,
                        a.id_pengajuan_pendanaan,
                        b.id id_output_penelitian,
                        b.id_jenis_output_penelitian
                    from litabmas.pengajuan_pendanaan_laporan_output a
                    join litabmas.pengajuan_pendanaan_output_penelitian b on b.id = a.id_pengajuan_pendanaan_output_penelitian
                    where a.waktu_dihapus is null and b.waktu_dihapus is null
                ) lo on lo.id_pengajuan_pendanaan = pp.id and lo.id_jenis_output_penelitian = op.id_jenis_output_penelitian
                " . $sqlWhere . "
                group by pp.id, pp.judul_penelitian";

        $result = DB::select($sql, $sqlBinding);

        //convert data dari db result ke data matang yang dibutuhkan
        $finalData = [
            'proposal_dengan_luaran' => 0,
            'proposal_dengan_luaran_belum_selesai' => 0,
            'proposal_dengan_luaran_disetujui_minimal_1' => 0,
            'proposal_belum_mengumpulkan_luaran' => 0,
            'total_luaran_disetujui' => 0,
            'total_luaran_belum_disetujui' => 0,
            'luaran' => []
        ];

        foreach ($result as $item) {
            $output_exist = 0;
            $output_disetujui = 0;

            // Convert to array
            $output_penelitian = [];
            if (!empty($item->output_penelitian)) {
                $trimmed = trim($item->output_penelitian, "{}");
                $output_penelitian = explode(",", $trimmed);
            }

            foreach ($output_penelitian as $op) {
                $output_info = explode('|', $op);
                $output_id_jenis = $output_info[0];
                $is_output_exist = $output_info[1];
                $is_output_disetujui = $output_info[2];

                if (!empty($is_output_exist)) {
                    $output_exist++;

                    if (!empty($is_output_disetujui)) {
                        $finalData['total_luaran_disetujui']++;
                        $output_disetujui++;
                    } else {
                        $finalData['total_luaran_belum_disetujui']++;
                    }

                    if (!isset($finalData['luaran'][$output_id_jenis])) {
                        $finalData['luaran'][$output_id_jenis] = 0;
                    }
                    $finalData['luaran'][$output_id_jenis]++;
                }
            }

            if (!empty($output_exist)) {
                $finalData['proposal_dengan_luaran']++;
                if ($item->status_agenda_kegiatan == PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN) {
                    $finalData['proposal_dengan_luaran_belum_selesai']++;
                    if ($output_disetujui > 0) {
                        $finalData['proposal_dengan_luaran_disetujui_minimal_1']++;
                    }
                }
            } else {
                if ($item->status_agenda_kegiatan == PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN) {
                    $finalData['proposal_belum_mengumpulkan_luaran']++;
                }
            }
        }

        return $finalData;
    }

    public function getStatusOutcomeProposal(array $filter = [])
    {
        $sqlWhere = " where pp.waktu_dihapus is null
            and
            (pp.status_agenda_kegiatan = '" . PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN . "'
            or pp.status_agenda_kegiatan = '" . PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL . "')";

        $sqlBinding = [];

        $joinOutcome = " join litabmas.klaster_pendanaan_outcome_penelitian op on op.id_klaster_pendanaan = kp.id and op.waktu_dihapus is null ";
        if (!empty($filter['jenis_pendanaan'])) {
            $joinOutcome .= " AND kp.kode_jenis_pendanaan = :jenis_pendanaan";
            $sqlBinding['jenis_pendanaan'] = $filter['jenis_pendanaan'];
        }

        if (!empty($filter['unit'])) {
            $sqlWhere .= " AND sp.id_unit_kerja = :unit_kerja";
            $sqlBinding['unit_kerja'] = $filter['unit'];
        }

        if (!empty($filter['periode_pendanaan'])) {
            $sqlWhere .= " AND sp.id_periode_pendanaan = :periode_pendanaan";
            $sqlBinding['periode_pendanaan'] = $filter['periode_pendanaan'];
        }

        $sql = "select
                    a.id,
                    a.judul_penelitian,
                    array_agg(concat(a.id_jenis_outcome_penelitian, '|', a.jumlah_publikasi)) publikasi
                from (
                    select
                        pp.id,
                        pp.judul_penelitian,
                        op.id_jenis_outcome_penelitian,
                        count(pub.id) jumlah_publikasi
                    from litabmas.pengajuan_pendanaan pp
                    join litabmas.sumber_pendanaan sp on pp.id_sumber_pendanaan = sp.id and sp.waktu_dihapus is null
                    join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan and kp.waktu_dihapus is null
                    " . $joinOutcome . "
                    join litabmas.jenis_outcome_penelitian jo on jo.id = op.id_jenis_outcome_penelitian and jo.waktu_dihapus is null
                    left join (
                        select concat('A', id) id, id_pengajuan_pendanaan, id_jenis_outcome_penelitian from litabmas.pengajuan_pendanaan_publikasi_artikel
                        union all
                        select concat('B', id) id, id_pengajuan_pendanaan, id_jenis_outcome_penelitian from litabmas.pengajuan_pendanaan_publikasi_buku
                    ) pub on pub.id_pengajuan_pendanaan = pp.id and pub.id_jenis_outcome_penelitian = op.id_jenis_outcome_penelitian
                    " . $sqlWhere . "
                    group by pp.id, pp.judul_penelitian, op.id_jenis_outcome_penelitian
                ) a
                group by a.id, a.judul_penelitian";

        $result = DB::select($sql, $sqlBinding);

        //convert data dari db result ke data matang yang dibutuhkan
        $finalData = [
            'proposal_dengan_publikasi' => 0,
            'proposal_belum_publikasi' => 0,
            'total_outcome_terkumpul' => 0,
            'total_outcome_belum_terkumpul' => 0,
            'outcome' => []
        ];
        foreach ($result as $item) {
            // Convert to array
            $outcome_penelitian = [];
            if (!empty($item->publikasi)) {
                $trimmed = trim($item->publikasi, "{}");
                $outcome_penelitian = explode(",", $trimmed);
            }
            foreach ($outcome_penelitian as $oc) {
                $outcome_info = explode('|', $oc);
                $outcome_id_jenis = $outcome_info[0];
                $outcome_exist = $outcome_info[1];

                if (!empty($outcome_exist)) {
                    if (!isset($finalData['outcome'][$outcome_id_jenis])) {
                        $finalData['outcome'][$outcome_id_jenis] = 0;
                    }
                    $finalData['outcome'][$outcome_id_jenis] += $outcome_exist;
                    $finalData['total_outcome_terkumpul'] += $outcome_exist;
                } else {
                    $finalData['total_outcome_belum_terkumpul']++;
                }
            }

            if (!empty($outcome_exist)) {
                $finalData['proposal_dengan_publikasi']++;
            } else {
                $finalData['proposal_belum_publikasi']++;
            }
        }

        return $finalData;
    }
}
