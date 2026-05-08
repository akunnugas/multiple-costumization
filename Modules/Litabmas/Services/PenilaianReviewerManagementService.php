<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Helpers\Error;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Format;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\AspekPenilaianOutputJawaban;
use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PenilaianReviewerIsianProposal;
use Modules\Litabmas\Models\PenilaianReviewerKomposisiProposal;
use Modules\Litabmas\Models\PenilaianReviewerLaporanProgres;
use Modules\Litabmas\Models\PenilaianReviewerOutput;
use Modules\Litabmas\Models\PenilaianReviewerOutputBersama;
use Modules\Litabmas\Models\PenilaianReviewerPresentasiProposal;

class PenilaianReviewerManagementService
{
    /**
     * @var PengajuanPendanaanReviewerKegiatan
     */
    protected $model = PengajuanPendanaanReviewer::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanReviewer;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "SELECT
                pp.id,
                pp.kode_registrasi,
                pp.judul_penelitian,
                kp.nama_klaster,
                initcap(kp.kode_jenis_pendanaan) as kode_jenis_pendanaan,
                sp.id_periode_pendanaan,
                fp.tahun as nama_periode_pendanaan,
                ppr.apakah_review_proposal,
                ppr.apakah_review_luaran,
                ppr.apakah_review_antara,
                coalesce(ppr.status_penilaian_progress_report, 'belum_dinilai') as status_penilaian_antara,
                coalesce(ppr.status_penilaian_output, 'belum_dinilai') as status_penilaian_luaran,
                coalesce(ppr.status_penilaian_isian_proposal, 'belum_dinilai') as status_penilaian_proposal,
                (SELECT
                    string_agg(CONCAT(
                        CASE
                            WHEN ak.kode_agenda = 'feedback_reviewer' AND ppr.apakah_review_proposal THEN 'Review Proposal'
                            WHEN ak.kode_agenda = 'penilaian_luaran' AND ppr.apakah_review_luaran THEN 'Review Luaran'
                            WHEN ak.kode_agenda = 'penilaian_laporan_antara' AND ppr.apakah_review_antara THEN 'Review Antara'
                        ELSE null END,
                        ' (', TO_CHAR(kk.waktu_mulai, 'YYYY-MM-DD'), ' - ', TO_CHAR(kk.waktu_selesai, 'YYYY-MM-DD'), ')'
                    ), ', ')
                FROM litabmas.klaster_pendanaan kp
                JOIN litabmas.klaster_pendanaan_agenda_kegiatan kk ON kk.id_klaster_pendanaan = kp.id
                JOIN litabmas.agenda_kegiatan ak ON kk.id_agenda_kegiatan = ak.id
                WHERE kp.id = pp.id_klaster_pendanaan
                AND ak.kode_agenda IN ('feedback_reviewer', 'penilaian_luaran', 'penilaian_laporan_antara')
                AND (
                    (ak.kode_agenda = 'feedback_reviewer' AND ppr.apakah_review_proposal)
                    OR (ak.kode_agenda = 'penilaian_luaran' AND ppr.apakah_review_luaran)
                    OR (ak.kode_agenda = 'penilaian_laporan_antara' AND ppr.apakah_review_antara)
                )
                ) AS bertugas_sebagai
            FROM
                litabmas.pengajuan_pendanaan pp
            JOIN
                litabmas.klaster_pendanaan kp ON pp.id_klaster_pendanaan = kp.id
            JOIN
                litabmas.sumber_pendanaan sp ON pp.id_sumber_pendanaan = sp.id
            JOIN
                litabmas.periode_pendanaan fp ON sp.id_periode_pendanaan = fp.id
            JOIN
                litabmas.pengajuan_pendanaan_reviewers ppr ON pp.id = ppr.id_pengajuan_pendanaan
                AND ppr.id_biodata = :id_biodata";

        if (empty($order)) {
            $defaultOrder = [
                'field' => 'id asc'
            ];
        }

        $defaultFilter = "pp.waktu_dihapus IS NULL AND pp.status_agenda_kegiatan not in (:status_agenda_kegiatan) AND ppr.apakah_admin = false";

        // scope filter by role user
        $idBiodata = auth()->user()?->biodata?->id;

        $bindings['id_biodata'] = $idBiodata;
        $bindings['status_agenda_kegiatan'] = implode(',', [
            PengajuanPendanaanStatus2::LEVEL1_DRAFT,
            PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA,
            PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN,
            PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI,
            PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI,
        ]);

        $fieldMap = [
            'kode_jenis_pendanaan'   => 'kp.kode_jenis_pendanaan',
            'id_periode_pendanaan'   => 'sp.id_periode_pendanaan',
            'nama_periode_pendanaan' => 'fp.tahun',
            'judul_penelitian'       => 'pp.judul_penelitian',
            'nama_klaster'           => 'kp.nama_klaster',
            'bertugas_sebagai'       => "(SELECT
                    string_agg(CONCAT(
                        CASE
                            WHEN ak.kode_agenda = 'feedback_reviewer' AND ppr.apakah_review_proposal THEN 'Review Proposal'
                            WHEN ak.kode_agenda = 'penilaian_luaran' AND ppr.apakah_review_luaran THEN 'Review Luaran'
                            WHEN ak.kode_agenda = 'penilaian_laporan_antara' AND ppr.apakah_review_antara THEN 'Review Antara'
                        ELSE null END,
                        ' (', 
                        TO_CHAR(kk.waktu_mulai, 'DD'), ' ',
                        CASE EXTRACT(MONTH FROM kk.waktu_mulai)
                            WHEN 1 THEN 'Jan' WHEN 2 THEN 'Feb' WHEN 3 THEN 'Mar' WHEN 4 THEN 'Apr'
                            WHEN 5 THEN 'Mei' WHEN 6 THEN 'Jun' WHEN 7 THEN 'Jul' WHEN 8 THEN 'Agt'
                            WHEN 9 THEN 'Sep' WHEN 10 THEN 'Okt' WHEN 11 THEN 'Nov' WHEN 12 THEN 'Des'
                        END, ' ', TO_CHAR(kk.waktu_mulai, 'YYYY'),
                        ' - ', 
                        TO_CHAR(kk.waktu_selesai, 'DD'), ' ',
                        CASE EXTRACT(MONTH FROM kk.waktu_selesai)
                            WHEN 1 THEN 'Jan' WHEN 2 THEN 'Feb' WHEN 3 THEN 'Mar' WHEN 4 THEN 'Apr'
                            WHEN 5 THEN 'Mei' WHEN 6 THEN 'Jun' WHEN 7 THEN 'Jul' WHEN 8 THEN 'Agt'
                            WHEN 9 THEN 'Sep' WHEN 10 THEN 'Okt' WHEN 11 THEN 'Nov' WHEN 12 THEN 'Des'
                        END, ' ', TO_CHAR(kk.waktu_selesai, 'YYYY'),
                        ')'
                    ), ', ')
                FROM litabmas.klaster_pendanaan kp
                JOIN litabmas.klaster_pendanaan_agenda_kegiatan kk ON kk.id_klaster_pendanaan = kp.id
                JOIN litabmas.agenda_kegiatan ak ON kk.id_agenda_kegiatan = ak.id
                WHERE kp.id = pp.id_klaster_pendanaan
                AND ak.kode_agenda IN ('feedback_reviewer', 'penilaian_luaran', 'penilaian_laporan_antara')
                AND (
                    (ak.kode_agenda = 'feedback_reviewer' AND ppr.apakah_review_proposal)
                    OR (ak.kode_agenda = 'penilaian_luaran' AND ppr.apakah_review_luaran)
                    OR (ak.kode_agenda = 'penilaian_laporan_antara' AND ppr.apakah_review_antara)
                )
            )",
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            defaultOrder: $defaultOrder ?? null,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            // groupBy: $groupBy,
            bindingUsingName: true
        );

        return Pagination::create($sql, $bindings, $page, $perPage, bindingUsingName: true);
    }

    /**
     * Menampilkan data detail
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $table = 'litabmas.pengajuan_pendanaan';

        $pengajuanPendanaanColumns = 'pp.*';
        $periodePendanaanColumns = 'fp.tahun as nama_periode_pendanaan';
        $sumberPendanaanColumns = 'sp.nama_sumber_pendanaan, sp.id_periode_pendanaan, sp.total_pendanaan, sp.sisa_anggaran';
        $klasterPendanaanColumns = 'kp.nama_klaster, kp.maksimal_anggaran';
        $pengelolaColumns = 'pengelola.nama_unit as nama_pengelola_bantuan';
        $outputColumns = 'output.jenis_output_penelitian';
        $bidangIlmuColumns = 'bi.nama_bidang_ilmu';
        $temaKegiatanColumns = 'tk.nama_tema';
        $ketuaColumns = 'b.id as id_ketua';
        $apakahApprove = 'kp.apakah_butuh_approve_semua_anggota';

        $allColumns = $pengajuanPendanaanColumns . ', ' . $periodePendanaanColumns . ', ' . $sumberPendanaanColumns
            . ', ' . $klasterPendanaanColumns . ', ' . $pengelolaColumns . ', ' . $outputColumns . ', '
            . $bidangIlmuColumns . ', ' . $temaKegiatanColumns . ', ' . $ketuaColumns . ', ' . $apakahApprove;
        $sql = "SELECT $allColumns
            FROM $table pp
            join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan
                and kp.waktu_dihapus is null
            join litabmas.sumber_pendanaan sp on sp.id = pp.id_sumber_pendanaan
                and sp.waktu_dihapus is null
            join litabmas.periode_pendanaan fp on fp.id = sp.id_periode_pendanaan
                and fp.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan_anggota ppa on ppa.id_pengajuan_pendanaan = pp.id AND ppa.apakah_ketua = true
                and ppa.waktu_dihapus is null
            join litabmas.bidang_ilmu bi on bi.id = pp.id_bidang_ilmu
                and bi.waktu_dihapus is null
            join litabmas.tema_kegiatan tk on tk.id = pp.id_tema_kegiatan
                and tk.waktu_dihapus is null
            join core.biodata b on b.id = ppa.id_biodata
                and b.waktu_dihapus is null
            join core.unit_kerja pengelola on pengelola.id = sp.id_unit_kerja
                and pengelola.waktu_dihapus is null
            left join (
                select pp.id, string_agg(jop.nama_output, ', ') as jenis_output_penelitian
                from litabmas.pengajuan_pendanaan pp
                join litabmas.pengajuan_pendanaan_output_penelitian ppop on ppop.id_pengajuan_pendanaan = pp.id
                    and ppop.waktu_dihapus is null
                join litabmas.jenis_output_penelitian jop on jop.id = ppop.id_jenis_output_penelitian
                    and jop.waktu_dihapus is null
                where pp.waktu_dihapus is null
                group by pp.id
            ) as output on output.id = pp.id
            WHERE pp.waktu_dihapus is null
                and pp.id = :id
            ";

        $select = DB::select($sql, ['id' => $id]);

        if (!isset($select[0])) {
            return new Error('Data tidak ditemukan.', 404);
        }

        $data = Collection::make($select[0]);

        // format persentase, jika exactly number maka tidak perlu decimal
        $data['penilaian_index_similarity'] = Format::removeTrailingZeroes($data['penilaian_index_similarity']);
        $data['penilaian_index_ai'] = Format::removeTrailingZeroes($data['penilaian_index_ai']);

        // cek apabila data ada
        if (Error::isError($data)) {
            abort(404);
        }

        return $data;
    }

    public function storePenilaianAspekProposal(int $id, array $data)
    {
        DB::beginTransaction();

        try {
            $pengajuanPendanaanReviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $id)
                ->where('id_biodata', auth()->user()->biodata->id)
                ->first();

            $pengajuanPendanaanReviewer->update([
                'rekomendasi_anggaran' => str_replace('.', '', $data['usulan_anggaran']),
            ]);

            foreach ($data['nilai'] as $idAspek => $nilai) {
                PenilaianReviewerKomposisiProposal::updateOrCreate(
                    [
                        'id_aspek_penilaian_komposisi_proposal' => $idAspek,
                        'id_pengajuan_pendanaan_reviewer' => $pengajuanPendanaanReviewer->id,
                    ],
                    [
                        'skala_nilai_komposisi_proposal' => $nilai,
                    ]
                );
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();
    }

    public function storePenilaianAspekPresentasi(int $id, array $data)
    {
        DB::beginTransaction();

        try {
            $pengajuanPendanaanReviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $id)
                ->where('id_biodata', auth()->user()->biodata->id)
                ->first();

            foreach ($data['nilai'] as $idAspek => $nilai) {
                PenilaianReviewerPresentasiProposal::updateOrCreate(
                    [
                        'id_aspek_penilaian_presentasi_proposal' => $idAspek,
                        'id_pengajuan_pendanaan_reviewer' => $pengajuanPendanaanReviewer->id,
                    ],
                    [
                        'skala_nilai_presentasi_proposal' => $nilai,
                    ]
                );
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();
    }

    public function getPenilaianReviewerKomposisiProposal($idReviewer = null)
    {
        $data = PenilaianReviewerKomposisiProposal::when($idReviewer, function ($query) use ($idReviewer) {
            return $query->where('id_pengajuan_pendanaan_reviewer', $idReviewer);
        })->get();

        // group by id_pengajuan_pendanaan_reviewer
        if (!$idReviewer) {
            $data = $data->groupBy('id_pengajuan_pendanaan_reviewer');

            $data = $data->map(function ($item) {
                return $item->pluck('skala_nilai_komposisi_proposal', 'id_aspek_penilaian_komposisi_proposal');
            });
        } else {
            $data = $data->pluck('skala_nilai_komposisi_proposal', 'id_aspek_penilaian_komposisi_proposal');
        }

        $data = $data->toArray();

        return $data;
    }

    public function getPenilaianReviewerPresentasiProposal($idReviewer = null)
    {
        $data = PenilaianReviewerPresentasiProposal::when($idReviewer, function ($query) use ($idReviewer) {
            return $query->where('id_pengajuan_pendanaan_reviewer', $idReviewer);
        })->get();

        // group by id_pengajuan_pendanaan_reviewer
        if (!$idReviewer) {
            $data = $data->groupBy('id_pengajuan_pendanaan_reviewer');

            $data = $data->map(function ($item) {
                return $item->pluck('skala_nilai_presentasi_proposal', 'id_aspek_penilaian_presentasi_proposal');
            });
        } else {
            $data = $data->pluck('skala_nilai_presentasi_proposal', 'id_aspek_penilaian_presentasi_proposal');
        }

        $data = $data->toArray();

        return $data;
    }

    public function getPenilaianReviewerPresentasi($idReviewer)
    {
        return PenilaianReviewerPresentasiProposal::where('id_pengajuan_pendanaan_reviewer', $idReviewer)->get();
    }

    public function getWaktuMulaiSelesaiAgendaKegiatan($idPengajuanPendanaan, $reviewer)
    {
        $agenda = [];
        if ($reviewer->apakah_review_proposal) {
            $agenda[] = AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
        }

        if ($reviewer->apakah_review_luaran) {
            $agenda[] = AgendaKegiatan::STEP_PENILAIAN_LUARAN;
        }

        if ($reviewer->apakah_review_antara) {
            $agenda[] = AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA;
        }

        $data = DB::table('litabmas.pengajuan_pendanaan as pp')
            ->join('litabmas.klaster_pendanaan as kp', 'pp.id_klaster_pendanaan', '=', 'kp.id')
            ->join('litabmas.klaster_pendanaan_agenda_kegiatan as kk', 'kk.id_klaster_pendanaan', '=', 'kp.id')
            ->join('litabmas.agenda_kegiatan as ak', 'kk.id_agenda_kegiatan', '=', 'ak.id')
            ->join('litabmas.pengajuan_pendanaan_reviewers as ppr', 'ppr.id_pengajuan_pendanaan', '=', 'pp.id')
            ->where('pp.id', $idPengajuanPendanaan)
            ->where('ppr.id', $reviewer->id)
            ->whereIn('ak.kode_agenda', $agenda)
            ->select('ak.kode_agenda', 'kk.waktu_mulai', 'kk.waktu_selesai')
            ->get();

        return $data;
    }

    public function storePenilaianLaporanAntara($idPengajuanPendanaan, $idReviewer, $data)
    {
        DB::beginTransaction();

        try {
            $currentReviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
                ->where('id_biodata', auth()->user()->biodata->id)
                ->first();

            $grouped = [];

            foreach ($data['feedback'] as $jenis => $value) {
                if (!isset($grouped[$data['status'][$jenis]])) {
                    $grouped[$data['status'][$jenis]] = 1;
                } else {
                    $grouped[$data['status'][$jenis]]++;
                }

                $dataLapProgres = PengajuanPendanaanLaporanProgres::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
                    ->where('jenis_laporan_progres', $jenis)
                    ->first();

                PenilaianReviewerLaporanProgres::updateOrCreate(
                    [
                        'id_pengajuan_pendanaan_reviewer' => $idReviewer,
                        'id_pengajuan_pendanaan_laporan_progres' => $dataLapProgres->id,
                    ],
                    [
                        'feedback_laporan_progres' => $value,
                        'status_laporan_progres_reviewer' => $data['status'][$jenis],
                    ]
                );
            }

            // sort by highest value
            arsort($grouped);

            // get key
            if (isset($grouped[PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI])
                && isset($grouped[PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI])
                && $grouped[PenilaianReviewerLaporanProgres::STATUS_LAP_DISETUJUI] === $grouped[PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI]) {
                $currentStatus = PenilaianReviewerLaporanProgres::STATUS_LAP_DIREVISI;
            } else {
                $currentStatus = key($grouped);
            }

            $currentReviewer->update([
                'komentar_umum_reviewer_progress_report' => $data['komentar_umum'],
                'status_penilaian_progress_report' => $currentStatus,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(message: "Anda dapat memberikan feedback setelah peneliti mengunggah dokumen laporan antara");
        }

        DB::commit();
    }

    public function storeFeedback($idPengajuanPendanaan, $dataIsianPenilaianProposal, $records)
    {
        DB::beginTransaction();
        try {
            $proposal = PengajuanPendanaan::find($idPengajuanPendanaan);

            if (in_array($proposal->status_agenda_kegiatan, [PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI])) {
                $proposal->update([
                    'status_agenda_kegiatan' => PengajuanPendanaanStatus2::LEVEL7_PENINJAUAN_PROPOSAL,
                ]);
            }

            $reviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
                ->where('id_biodata', auth()->user()->biodata->id)
                ->first();

            foreach ($dataIsianPenilaianProposal as $key => $value) {
                $data = [
                    'id_pengajuan_pendanaan_isian_proposal' => $value->id,
                    'id_pengajuan_pendanaan_reviewer' => $reviewer->id,
                    'feedback_isian_proposal' => $records[$value->id],
                ];

                PenilaianReviewerIsianProposal::updateOrCreate(
                    [
                        'id_pengajuan_pendanaan_isian_proposal' => $value->id,
                        'id_pengajuan_pendanaan_reviewer' => $reviewer->id,
                    ],
                    $data
                );
            }

            $reviewer->update([
                'status_penilaian_isian_proposal' => 'sudah_dinilai',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }
        DB::commit();

        return true;
    }

    public function storePenilaianLuaran(int $idProposalPendanaan, int $idReviewer, array $data)
    {
        DB::beginTransaction();

        try {
            $reviewer = PengajuanPendanaanReviewer::find($idReviewer);

            $grouped = [];
            foreach ($data['status'] as $id => $status) {
                if (!isset($grouped[$status])) {
                    $grouped[$status] = 1;
                } else {
                    $grouped[$status]++;
                }

                PenilaianReviewerOutput::updateOrCreate(
                    [
                        'id_pengajuan_pendanaan_reviewer' => $idReviewer,
                        'id_pengajuan_pendanaan_output_penelitian' => $id,
                    ],
                    [
                        'status_penilaian_output' => $status,
                        'feedback_output' => $data['feedback'][$id] ?? null,
                    ]
                );
            }

            // sort by highest value
            arsort($grouped);

            // get key
            if (isset($grouped[PenilaianReviewerOutput::STATUS_DISETUJUI])
                && isset($grouped[PenilaianReviewerOutput::STATUS_DIREVISI])
                && $grouped[PenilaianReviewerOutput::STATUS_DISETUJUI] === $grouped[PenilaianReviewerOutput::STATUS_DIREVISI]) {
                $currentStatus = PenilaianReviewerOutput::STATUS_DIREVISI;
            } else {
                $currentStatus = key($grouped);
            }

            // update status penilaian output
            $reviewer->update([
                'komentar_umum_reviewer_output' => $data['komentar_umum'] ?? null,
                'status_penilaian_output' => $currentStatus,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return true;
    }

    public function storePenilaianLuaranBersama(int $idProposalPendanaan, int $idReviewer, array $data)
    {
        DB::beginTransaction();

        try {
            $dataOld = PenilaianReviewerOutputBersama::where('id_pengajuan_pendanaan', $idProposalPendanaan)
                ->get();
            foreach ($data as $key => $idJawaban) {
                $raw = explode('[', $key);
                $raw = explode(']', $raw[1]);
                $idPertanyaan = $raw[0];

                $oldData = $dataOld->where('id_aspek_penilaian_output_pertanyaan', $idPertanyaan)->first();

                $payload = [];
                if (!$oldData) {
                    $payload['id_pengajuan_pendanaan'] = $idProposalPendanaan;
                    $payload['id_pengajuan_pendanaan_reviewer_pembuat'] = $idReviewer;
                    $payload['id_pengajuan_pendanaan_reviewer_pengubah'] = $idReviewer;
                } else {
                    $payload['id_pengajuan_pendanaan_reviewer_pengubah'] = $idReviewer;
                }

                $payload['id_aspek_penilaian_output_pertanyaan'] = $idPertanyaan;

                $payload['id_aspek_penilaian_output_jawaban'] = $idJawaban;

                PenilaianReviewerOutputBersama::updateOrCreate(
                    [
                        'id_pengajuan_pendanaan' => $idProposalPendanaan,
                        'id_aspek_penilaian_output_pertanyaan' => $idPertanyaan,
                    ],
                    $payload
                );
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return true;
    }

    public function getDataJawabanBersamaDanAspek($id, $idsAspekLuaran) {
        $listOutputBersama = PenilaianReviewerOutputBersama::where('id_pengajuan_pendanaan', $id)
            ->whereIn('id_aspek_penilaian_output_pertanyaan', $idsAspekLuaran)
            ->get();

        $listJawaban = AspekPenilaianOutputJawaban::whereIn('id_aspek_penilaian_output_pertanyaan', $idsAspekLuaran)
            ->select('jawaban_penilaian_output', 'id', 'id_aspek_penilaian_output_pertanyaan')
            ->get()
            ->toArray();

        $listJawaban = collect($listJawaban)->groupBy('id_aspek_penilaian_output_pertanyaan')->toArray();

        return [$listOutputBersama, $listJawaban];
    }

    public function getCurrentReviewer($idBiodata, $idProposalPendanaan)
    {
        $reviewer = PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $idProposalPendanaan)
            ->where('id_biodata', $idBiodata)
            ->first();

        if (!$reviewer) {
            abort(404);
        }

        return $reviewer;
    }
}
