<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Gate\Models\Modul;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Helpers\FolderStructure;
use Modules\Litabmas\Models\KlasterPendanaanBidangIlmu;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Core\Helpers\Error as HelpersError;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;

class KlasterPendanaanService
{
    /**
     * @var KlasterPendanaan
     */
    protected $model = KlasterPendanaan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new KlasterPendanaan;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();

        $sql = "select
            fc.id,
            fc.nama_klaster,
            fc.mata_uang,
            fc.maksimal_anggaran,
            fc.maksimal_anggota,
            sp.nama_sumber_pendanaan as nama_sumber_pendanaan,
            x.nama_output,
            pp.tahun as nama_periode_pendanaan,
            fc.apakah_sudah_publikasi,
            fc.kode_jenis_pendanaan,
            ak.waktu_mulai as waktu_mulai_pendaftaran,
            ak.waktu_selesai as waktu_selesai_pendaftaran
        from " . $table . " fc
        join litabmas.klaster_pendanaan_agenda_kegiatan ak on fc.id = ak.id_klaster_pendanaan
        join litabmas.agenda_kegiatan k on k.id = ak.id_agenda_kegiatan and k.kode_agenda = 'pendaftaran'
        left join litabmas.sumber_pendanaan sp on sp.id = fc.id_sumber_pendanaan and sp.waktu_dihapus is null
        left join litabmas.periode_pendanaan pp on pp.id = sp.id_periode_pendanaan and pp.waktu_dihapus is null
        left join (
            select kpop.id_klaster_pendanaan, string_agg(jop.nama_output, ', ') as nama_output, kpop.apakah_wajib
            from litabmas.klaster_pendanaan_output_penelitian kpop
            join litabmas.jenis_output_penelitian jop on jop.id = kpop.id_jenis_output_penelitian and jop.waktu_dihapus is null
            where kpop.waktu_dihapus is null and apakah_wajib is true
            group by kpop.id_klaster_pendanaan, kpop.apakah_wajib
        ) x on x.id_klaster_pendanaan = fc.id
        ";

        $defaultFilter = "fc.waktu_dihapus IS NULL";

        $fieldMap = [
            'nama_klaster' => 'fc.nama_klaster',
            'nama_sumber_pendanaan' => 'sp.nama_sumber_pendanaan',
            'maksimal_anggaran' => 'CAST(fc.maksimal_anggaran AS text)',
            'nama_output' => 'x.nama_output',
            'nama_periode_pendanaan' => 'pp.tahun',
            'apakah_sudah_publikasi' => "CASE WHEN fc.apakah_sudah_publikasi = true THEN 'publikasi' ELSE 'draft' END",
            'waktu_mulai_pendaftaran' => 'CAST(ak.waktu_mulai AS text)',
            'waktu_selesai_pendaftaran' => 'CAST(ak.waktu_selesai AS text)',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function apakahPendaftaranDitutup(int $idKlasterPendanaan)
    {
        $detailAgendaKlaster = $this->getAktifAgendaKegiatanByIdKlasterPendanaan($idKlasterPendanaan);

        // get pendaftaran by kode_agenda
        $pendaftaran = collect($detailAgendaKlaster)
            ->where('kode_agenda', AgendaKegiatan::STEP_PENDAFTARAN)->first();

        // cek apakah pendaftaran sudah selesai
        return $pendaftaran->waktu_selesai < now()->format('Y-m-d');
    }

    //get list pengumuman
    public function getListPengumumanKlaster($limit, $filterNama = null, $filterJenis = null)
    {
        $results = DB::table('litabmas.klaster_pendanaan as kp')
            ->select([
                'kp.id',
                'kp.nama_klaster',
                'uk.nama_unit as pengelola',
                'sp.nama_sumber_pendanaan',
                'kp.maksimal_anggaran',
                'kp.kode_jenis_pendanaan',
                'kp.kategori_klaster',
                'kpak.waktu_mulai as mulai_pendaftaran',
                'kpak.waktu_selesai as akhir_pendaftaran',
                DB::raw('COUNT(pp.id) as total_pengajuan')
            ])
            ->join('litabmas.sumber_pendanaan as sp', 'sp.id', '=', 'kp.id_sumber_pendanaan')
            ->join('core.unit_kerja as uk', 'uk.id', '=', 'sp.id_unit_kerja')
            ->join('litabmas.klaster_pendanaan_agenda_kegiatan as kpak', 'kpak.id_klaster_pendanaan', '=', 'kp.id')
            ->join('litabmas.agenda_kegiatan as ak', function ($join) {
                $join->on('kpak.id_agenda_kegiatan', '=', 'ak.id')
                    ->where('ak.kode_agenda', '=', 'pendaftaran');
            })
            ->leftJoin('litabmas.pengajuan_pendanaan as pp', function ($join) {
                $join->on('pp.id_klaster_pendanaan', '=', 'kp.id')
                    ->where('pp.waktu_dihapus', '=', null)
                    ->where('pp.status_agenda_kegiatan', '<>', 'draft');
            })
            ->when($filterJenis, function ($query, $filterJenis) {
                return $query->where('kp.kode_jenis_pendanaan', $filterJenis);
            })
            ->when($filterNama, function ($query, $filterNama) {
                return $query->where('kp.nama_klaster', 'ilike', '%' . $filterNama . '%');
            })
            ->where('kp.apakah_sudah_publikasi', '=', '1')
            ->whereDate('kpak.waktu_selesai', '>=', now())
            ->whereNull('kp.waktu_dihapus')
            ->groupBy('kp.id', 'kp.nama_klaster', 'uk.nama_unit', 'sp.nama_sumber_pendanaan', 'kp.maksimal_anggaran', 'kp.kategori_klaster', 'kpak.waktu_mulai', 'kpak.waktu_selesai')
            ->orderBy('kpak.waktu_mulai', 'desc')
            ->limit($limit)
            ->get();

        return $results;
    }

    /**
     * Cek apakah sudah masuk masa reivew administrasi.
     *
     * @param int $idKlasterPendanaan
     * @return array
     */
    public function getInfoReviewAdministrasi(int $idKlasterPendanaan)
    {
        return $this->getInfoAgenda($idKlasterPendanaan, AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI, 'sudah_masuk_masa_review_administrasi');
    }

    public function getHeaderKlasterPenelitian(int $id)
    {
        $sql = "
            select
                kp.id,
                kp.nama_klaster,
                kp.minimal_anggota,
                kp.maksimal_anggota,
                kp.maksimal_anggaran,
                kp.kategori_klaster,
                kp.kode_jenis_pendanaan,
                sp.nama_sumber_pendanaan
            from litabmas.klaster_pendanaan kp
            join litabmas.sumber_pendanaan sp on kp.id_sumber_pendanaan = sp.id
            where kp.id = :id
        ";

        $klaster = DB::select($sql, ['id' => $id])[0];

        return $klaster;
    }
    /**
     * Cek apakah sudah masuk masa review proposal.
     *
     * @param int $idKlasterPendanaan
     * @return array
     */
    public function getInfoFeedbackReviewer(int $idKlasterPendanaan)
    {
        return $this->getInfoAgenda($idKlasterPendanaan, AgendaKegiatan::STEP_FEEDBACK_REVIEWER, 'sudah_masuk_masa_feedback_reviewer');
    }

    /**
     * Cek apakah sudah masuk masa review proposal.
     *
     * @param int $idKlasterPendanaan
     * @return array
     */
    public function getInfoPenilaianHasilPresentasi(int $idKlasterPendanaan)
    {
        return $this->getInfoAgenda($idKlasterPendanaan, AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI, 'sudah_masuk_masa_penilaian_hasil_presentasi');
    }

    public function getInfoSumberWajibPresentasiByIdKlaster(int $idKlasterPendanaan)
    {
        $klaster = DB::table('litabmas.klaster_pendanaan as kp')
            ->join('litabmas.sumber_pendanaan_agenda_kegiatan as spak', 'spak.id_sumber_pendanaan', '=', 'kp.id_sumber_pendanaan')
            ->join('litabmas.agenda_kegiatan as ak', function ($join) {
                $join->on('ak.id', '=', 'spak.id_agenda_kegiatan')
                    ->where('ak.kode_agenda', '=', AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI);
            })
            ->leftJoin('litabmas.klaster_pendanaan_agenda_kegiatan as kpak', function ($join) {
                $join->on('kpak.id_klaster_pendanaan', '=', 'kp.id')
                    ->on('kpak.id_agenda_kegiatan', '=', 'ak.id')
                    ->whereNull('kpak.waktu_dihapus');
            })
            ->where('kp.id', $idKlasterPendanaan)
            ->whereNull('kp.waktu_dihapus')
            ->select('kp.id_sumber_pendanaan', 'spak.id', 'spak.apakah_aktif', 'kpak.waktu_mulai', 'kpak.waktu_selesai')
            ->toRawSql();

        return $klaster;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return KlasterPendanaan
     */
    public function show(int $id): KlasterPendanaan
    {
        $data = $this->model->findOrFail($id);
        $sumberPendanaan = SumberPendanaan::find($data->id_sumber_pendanaan);
        $data->nama_periode_pendanaan = PeriodePendanaan::find($sumberPendanaan->id_periode_pendanaan)->tahun;
        return $data;
    }

    /**
     * Menampilkan informasi anggota & anggaran klaster pendanaan.
     *
     * @param int $id
     * @return KlasterPendanaan
     */
    public function getInfoAnggotaDanAnggaran(int $id): KlasterPendanaan
    {
        return $this->model->select(
            'minimal_anggota',
            'maksimal_anggota',
            'kategori_klaster',
            'apakah_butuh_approve_semua_anggota',
            'maksimal_anggaran',
            'mata_uang'
        )
            ->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return KlasterPendanaan|Error
     */
    public function store(array $data): KlasterPendanaan|Error
    {
        $dataCluster = Arr::only($data, [
            'nama_klaster',
            'maksimal_anggaran',
            'minimal_anggota',
            'maksimal_anggota',
            'id_sumber_pendanaan',
            'kode_jenis_pendanaan',
            'kategori_klaster',
            'apakah_butuh_approve_semua_anggota',
            'apakah_sudah_publikasi',
            'id_dokumen_template_rab',
            'apakah_bisa_multi_ajuan',
            'maksimal_ajuan_per_user'
        ]);

        $dataBidangIlmuDanTema = Arr::only($data, ['_bidang_ilmu_dan_tema_kegiatan']);

        $dataOutput = Arr::only($data, ['jenis_output_penelitian']);

        $dataOutcome = Arr::only($data, ['jenis_outcome_penelitian']);

        $dataAgendaKegiatan = Arr::only($data, ['agenda_kegiatan']);

        DB::beginTransaction();

        //upload db
        try {

            $klasterPendanaan = $this->saveCluster($dataCluster);

            if (HelpersError::isError($klasterPendanaan)) {
                return new Error(message: $klasterPendanaan->message);
            }

            // update or create klaster pendanaan bidang ilmu dan tema kegiatan
            $this->saveKlasterBidangIlmuDanTema($klasterPendanaan, $dataBidangIlmuDanTema);

            // update or create klaster pendanaan jenis output penelitian
            $this->saveKlasterOutputPenelitian($klasterPendanaan, $dataOutput);

            // update or create klaster pendanaan jenis outcome penelitian
            $this->saveKlasterOutcomePenelitian($klasterPendanaan, $dataOutcome);

            // update or create klaster pendanaan agenda kegiatan
            $this->saveKlasterAgendaKegiatan($klasterPendanaan, $dataAgendaKegiatan);
        } catch (Exception $e) {

            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return new Error(exception: $e);
        }

        DB::commit();

        return $klasterPendanaan;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return KlasterPendanaan|Error
     */
    public function update(array $data, int $id): KlasterPendanaan|Error
    {
        $dataCluster = Arr::only($data, [
            'nama_klaster',
            'maksimal_anggaran',
            'minimal_anggota',
            'maksimal_anggota',
            'id_sumber_pendanaan',
            'kode_jenis_pendanaan',
            'kategori_klaster',
            'apakah_butuh_approve_semua_anggota',
            'apakah_sudah_publikasi',
            'id_dokumen_template_rab',
            'apakah_bisa_multi_ajuan',
            'maksimal_ajuan_per_user'
        ]);

        $dataBidangIlmuDanTema = Arr::only($data, ['_bidang_ilmu_dan_tema_kegiatan']);

        $dataOutput = Arr::only($data, ['jenis_output_penelitian']);

        $dataOutcome = Arr::only($data, ['jenis_outcome_penelitian']);

        $dataAgendaKegiatan = Arr::only($data, ['agenda_kegiatan']);

        DB::beginTransaction();

        try {
            $klasterPendanaan = $this->saveCluster($dataCluster, $id);

            if (HelpersError::isError($klasterPendanaan)) {
                return new Error(message: $klasterPendanaan->message);
            }

            // update or create klaster pendanaan bidang ilmu dan tema kegiatan
            $this->saveKlasterBidangIlmuDanTema($klasterPendanaan, $dataBidangIlmuDanTema);

            // update or create klaster pendanaan output type mapping
            $this->saveKlasterOutputPenelitian($klasterPendanaan, $dataOutput);

            // update or create klaster pendanaan jenis outcome penelitian mapping
            $this->saveKlasterOutcomePenelitian($klasterPendanaan, $dataOutcome);

            // update or create klaster pendanaan agenda kegiatan mapping
            $this->saveKlasterAgendaKegiatan($klasterPendanaan, $dataAgendaKegiatan);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return new Error(exception: $e);
        }

        DB::commit();

        return $klasterPendanaan;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        DB::beginTransaction();

        try {
            $klasterPendanaan = $this->model->findOrFail($id);

            if (PengajuanPendanaan::where('id_klaster_pendanaan', $id)->exists()) {
                return new Error(message: 'Klaster pendanaan tidak bisa dihapus karena sudah ada pengajuan proposal.');
            }

            // hapus data yg ada di table mappingan klaster pendanaan dengan bidang ilmu dan tema
            $klasterPendanaanMajorMappings = $klasterPendanaan->pivotBidangIlmu()->get();
            foreach ($klasterPendanaanMajorMappings as $klasterPendanaanMajorMapping) {
                $klasterPendanaanMajorMapping->pivotTemaKegiatan()->delete(); // tema nya harus lebih dulu
                $klasterPendanaanMajorMapping->delete(); // bidang ilmu nya
            }

            // hapus data yg ada di table mappingan klaster pendanaan dengan output type
            $klasterPendanaan->pivotJenisOutputPenelitian()->delete();

            // hapus data yg ada di table mappingan klaster pendanaan dengan jenis outcome penelitian
            $klasterPendanaan->pivotJenisOutcomePenelitian()->delete();

            // hapus data yg ada di table mappingan klaster pendanaan dengan agenda kegiatan
            $klasterPendanaan->pivotAgendaKegiatan()->delete();

            // hapus klaster pendanaan
            $klasterPendanaan->delete();
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return null;
    }

    /**
     * Get bidang ilmu dan tema berdasarkan id klaster pendanaan.
     *
     * @param int $id
     * @return Collection
     */
    public function getBidangIlmuDanTemaByIdKlasterPendanaan(int $id, $withMapped = false)
    {
        $table = $this->model->getTable();

        $sql = "
        with pengajuan_count as (
            select
                pp.id_bidang_ilmu,
                pp.id_tema_kegiatan,
                pp.id_klaster_pendanaan,
                count(0) as total_pengajuan
            from
                litabmas.pengajuan_pendanaan pp
            where
                pp.id_klaster_pendanaan = " . $id . "
            group by
                pp.id_bidang_ilmu,
                pp.id_klaster_pendanaan,
                pp.id_tema_kegiatan
        )

        select
            fcm.id_bidang_ilmu,
            bi.nama_bidang_ilmu,
            fcmt.id_tema_kegiatan,
            tk.nama_tema,
            coalesce(pc.total_pengajuan, 0) as total_pengajuan
        from
            " . $table . " fc
            join litabmas.klaster_pendanaan_bidang_ilmu fcm on fcm.id_klaster_pendanaan = fc.id and fcm.waktu_dihapus is null
            join litabmas.klaster_pendanaan_bidang_ilmu_tema fcmt on fcmt.id_klaster_pendanaan_bidang_ilmu = fcm.id and fcmt.waktu_dihapus is null
            join litabmas.bidang_ilmu bi on bi.id = fcm.id_bidang_ilmu and bi.waktu_dihapus is null
            join litabmas.tema_kegiatan tk on tk.id = fcmt.id_tema_kegiatan and tk.waktu_dihapus is null
            left join pengajuan_count pc on pc.id_bidang_ilmu = fcm.id_bidang_ilmu and pc.id_tema_kegiatan = fcmt.id_tema_kegiatan and pc.id_klaster_pendanaan = fc.id
        where
            fc.waktu_dihapus is null
            and fc.id = ?;

        ";

        if (!$withMapped) {
            return collect(DB::select($sql, [$id]));
        }

        $raw = collect(DB::select($sql, [$id]));

        //mapping bidang ilmu
        $bidangIlmuMapped = [];
        foreach ($raw as $key => $value) {
            $bidangIlmuMapped[$value->id_bidang_ilmu]['nama_bidang_ilmu'] = $value->nama_bidang_ilmu;
            $bidangIlmuMapped[$value->id_bidang_ilmu]['tema'][$value->id_tema_kegiatan]['nama_tema'] = $value->nama_tema;
        }

        return $bidangIlmuMapped;
    }

    /**
     * get total pendanaan by id klaster pendanaan.
     */
    public function getTotalPendanaanByIdKlasterPendanaan(int $id)
    {
        $sql = "select count(0) as total_pendanaan from litabmas.pengajuan_pendanaan pp
                join litabmas.klaster_pendanaan kp on kp.id  = pp.id_klaster_pendanaan
                where pp.waktu_dihapus is null and kp.id = ? and pp.status_agenda_kegiatan <> '" . PengajuanPendanaanStatus2::LEVEL1_DRAFT . "'";

        // ubah result menjadi collection
        return collect(DB::select($sql, [$id]))->first();
    }

    /**
     * Get output yang wajib berdasarkan id klaster pendanaan.
     *
     * @param int $id
     * @return Collection
     */
    public function getOutputWajibByIdKlasterPendanaan(int $id)
    {
        $table = $this->model->getTable();

        $sql = "select jop.id as id_jenis_output_penelitian, jop.nama_output
            from " . $table . " fc
            join litabmas.klaster_pendanaan_output_penelitian kpop on kpop.id_klaster_pendanaan = fc.id and kpop.waktu_dihapus is null
            join litabmas.jenis_output_penelitian jop on jop.id = kpop.id_jenis_output_penelitian and jop.waktu_dihapus is null
            where fc.waktu_dihapus is null
                and fc.id = ?
                and kpop.apakah_wajib = true";

        // ubah result menjadi collection
        return collect(DB::select($sql, [$id]));
    }

    /**
     * Get outcome yang wajib berdasarkan id klaster pendanaan.
     *
     * @param int $id
     * @return Collection
     */
    public function getOutcomeWajibByIdKlasterPendanaan(int $id)
    {
        $table = $this->model->getTable();

        $sql = "select jop.id as id_jenis_outcome_penelitian, jop.nama_outcome, jop.batas_pengumpulan_outcome
        from " . $table . " fc
        join litabmas.klaster_pendanaan_outcome_penelitian kpop on kpop.id_klaster_pendanaan = fc.id
            and kpop.waktu_dihapus is null
        join litabmas.jenis_outcome_penelitian jop on jop.id = kpop.id_jenis_outcome_penelitian
            and jop.waktu_dihapus is null
        where fc.waktu_dihapus is null
            and fc.id = ?
            and kpop.apakah_wajib = true";

        // ubah result menjadi collection
        return collect(DB::select($sql, [$id]));
    }

    /**
     * Get agenda kegiatan berdasarkan id klaster pendanaan.
     *
     * @param int $id
     * @return Collection
     */
    public function getAktifAgendaKegiatanByIdKlasterPendanaan(int $id)
    {
        $table = $this->model->getTable();

        $sql = "select ak.id as id_agenda_kegiatan, ak.kode_agenda, ak.nama_agenda, kpak.waktu_mulai, kpak.waktu_selesai
            from " . $table . " fc
            join litabmas.klaster_pendanaan_agenda_kegiatan kpak on kpak.id_klaster_pendanaan = fc.id and kpak.waktu_dihapus is null
            join litabmas.agenda_kegiatan ak on ak.id = kpak.id_agenda_kegiatan and ak.waktu_dihapus is null
            where fc.waktu_dihapus is null
                and fc.id = ?
            order by ak.urutan";

        // ubah result menjadi collection
        return collect(DB::select($sql, [$id]));
    }

    public function getAgendaMapping($id)
    {
        $agendaMapping = $this->getAktifAgendaKegiatanByIdKlasterPendanaan($id);
        $timezone = config('app.timezone');

        return $agendaMapping->map(function ($item) use ($timezone) {
            $waktuMulai = $item->waktu_mulai ?
                Carbon::parse($item->waktu_mulai, $timezone)->locale(app()->getLocale())->translatedFormat('d F Y')
                : null;
            $waktuSelesai = $item->waktu_selesai
                ? Carbon::parse($item->waktu_selesai, $timezone)->locale(app()->getLocale())->translatedFormat('d F Y')
                : null;

            if ($waktuMulai == $waktuSelesai) {
                $agendaDate = $waktuMulai;
            } elseif (empty($waktuMulai)) {
                $agendaDate = $waktuSelesai;
            } elseif (empty($waktuSelesai)) {
                $agendaDate = $waktuMulai;
            } else {
                $agendaDate = "{$waktuMulai} - {$waktuSelesai}";
            }

            $item->tanggal_agenda = $agendaDate;
            return $item;
        });
    }

    public function getDanaDiberikanByIdKlaster(int $id)
    {
        $table = $this->model->getTable();

        $sql = "select sum(pp.nominal_anggaran_disetujui) as total_dana_diberikan from $table kp
                join litabmas.pengajuan_pendanaan pp on pp.id_klaster_pendanaan = kp.id
                where kp.id = ?";

        // ubah result menjadi collection
        return collect(DB::select($sql, [$id]));
    }

    /*** --- [START] PRIVATE METHOD--- ***/
    /**
     * Proses penyimapan klaster pendanaan.
     *
     * @param array $data
     * @param int|null $id
     * @return mixed
     * @throws ValidationException
     */
    private function saveCluster(array $data, int $id = null)
    {
        // cari mata_uang berdasarkan id_sumber_pendanaan dari $data
        $sumberPendanaan = SumberPendanaan::findOrFail($data['id_sumber_pendanaan']);
        $mataUang = $sumberPendanaan->mata_uang ?? config('money.defaults.currency');
        $totalBudgetFundSource = $sumberPendanaan->total_pendanaan;
        $convert = $mataUang !== config('money.defaults.currency');
        $humanTotalBudget = money($totalBudgetFundSource, $mataUang, $convert);

        // validasi maksimal_anggaran $data tidak boleh lebih dari total_pendanaan funding_source
        Validator::make($data, [
            'maksimal_anggaran' => "lte:$totalBudgetFundSource"
        ], [
            'maksimal_anggaran.lte' => __('litabmas::klaster_pendanaan.maksimal_anggaran') . " melampaui total budget Sumber Pendanaan ($humanTotalBudget)"
        ])->validate();

        // validasi maksimal_anggota tidak boleh lebih dari minimal_anggota jika kelompok
        if ($data['kategori_klaster'] === KlasterPendanaan::KATEGORI_KELOMPOK) {
            Validator::make($data, [
                'minimal_anggota' => 'required|integer',
                'maksimal_anggota' => 'required|integer|gte:minimal_anggota'
            ], [
                'minimal_anggota.required' => __('litabmas::klaster_pendanaan.minimal_anggota') . ' tidak boleh kosong',
                'maksimal_anggota.required' => __('litabmas::klaster_pendanaan.maksimal_anggota') . ' tidak boleh kosong',
                'maksimal_anggota.gte' => __('litabmas::klaster_pendanaan.maksimal_anggota') . ' harus lebih besar dari Minimal Anggota'
            ])->validate();
        } elseif ($data['kategori_klaster'] === KlasterPendanaan::KATEGORI_INDIVIDU) { // set default null utk individu
            $data['minimal_anggota'] = null;
            $data['maksimal_anggota'] = null;
            $data['apakah_butuh_approve_semua_anggota'] = false;
        }

        /**
         * Jika bisa multi ajuan, ambil nilai maksimal dari Periode Pendanaan
         */
        if (isset($data['apakah_bisa_multi_ajuan']) && $data['apakah_bisa_multi_ajuan'] == true) {
            $data['maksimal_ajuan_per_user'] = null;
        } else {
            $data['maksimal_ajuan_per_user'] = 1;
        }

        // set mata_uang berdasarkan sumber pendanannya
        $data['mata_uang'] = $mataUang;

        $model = null;
        if (!empty($id)) {
            $model = $this->model->findOrFail($id);
        }

        if (!empty($data['id_dokumen_template_rab'])) {
            $uploaded = $this->processUploadDocument(
                $data,
                'id_dokumen_template_rab',
                FolderStructure::LITABMAS_KLASTER_PENDANAAN_TEMPLATE_RAB,
                $model
            );

            if (!empty($uploaded) && Error::isError($uploaded->getError())) {
                return $uploaded->getError();
            }

            $data['id_dokumen_template_rab'] = $uploaded->get()->id;
        } else {
            unset($data['id_dokumen_template_rab']);
        }

        DB::beginTransaction();

        try {
            // update
            if (!empty($id)) {
                $klasterPendanaan = $this->model->findOrFail($id);
                $klasterPendanaan->update($data);
            } else {
                // create
                $klasterPendanaan = $this->model->create($data);
            }

            // execute upload ke s3
            if (!empty($uploaded)) {
                $uploaded->executeUpload();
                if (Error::isError($uploaded->getError())) {
                    DB::rollBack();
                    return $uploaded->getError();
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }
        DB::commit();

        return $klasterPendanaan;
    }

    /**
     * Proses penyimpanan mapping klaster pendanaan dengan bidang ilmu dan tema.
     *
     * @param KlasterPendanaan $klasterPendanaan
     * @param array $data
     * @return void
     */
    private function saveKlasterBidangIlmuDanTema(KlasterPendanaan $klasterPendanaan, array $data)
    {
        $bidangIlmuDanTemaKegiatan = $data['_bidang_ilmu_dan_tema_kegiatan'] ?? [];

        // [Start] Proses penyimpanan mapping klaster pendanaan dengan bidang ilmu
        $IdsBidangIlmu = array_keys($bidangIlmuDanTemaKegiatan);

        // [Delete] bidang ilmu lama
        KlasterPendanaanBidangIlmu::where('id_klaster_pendanaan', $klasterPendanaan->id)->delete();

        // [Create] bidang ilmu baru
        foreach ($IdsBidangIlmu as $id) {
            KlasterPendanaanBidangIlmu::create([
                'id_klaster_pendanaan' => $klasterPendanaan->id,
                'id_bidang_ilmu' => $id
            ]);
        }

        // [Start] Proses penyimpanan mapping klaster pendanaan dengan tema
        $klasterPendanaanMajorMappings = $klasterPendanaan->pivotBidangIlmu()->get();

        // olah data $klasterPendanaanMajorMappings dan $bidangIlmuDanTemaKegiatan utk menjadi $record yg digunakan sync dibawah.
        $record = [];
        foreach ($klasterPendanaanMajorMappings as $klasterPendanaanMajorMapping) {
            $temaKegiatans = $bidangIlmuDanTemaKegiatan[$klasterPendanaanMajorMapping->id_bidang_ilmu] ?? [];
            $record[$klasterPendanaanMajorMapping->id] = $temaKegiatans;
        }

        // terus di looping baru relasi ke temaKegiatan()
        foreach ($klasterPendanaanMajorMappings as $klasterPendanaanMajorMapping) {
            $klasterPendanaanMajorMapping->temaKegiatan()->sync($record[$klasterPendanaanMajorMapping->id], false);
        }
        // [End] Proses penyimpanan mapping klaster pendanaan dengan tema
    }

    /**
     * Proses penyimpanan mapping klaster pendanaan dengan output type.
     *
     * @param KlasterPendanaan $klasterPendanaan
     * @param array $data
     * @return void
     */
    private function saveKlasterOutputPenelitian(KlasterPendanaan $klasterPendanaan, array $data)
    {
        $jenisOutputPenelitian = $data['jenis_output_penelitian'] ?? [];

        $record = [];
        foreach ($jenisOutputPenelitian as $idJenisOutputPenelitian => $isRequired) {
            $record[$idJenisOutputPenelitian] = [
                'apakah_wajib' => $isRequired
            ];
        }

        // detach true karena bisa dihapus dan tidak memiliki child nantinya, hanya sebagai table bantuan utk get yg output required dari klaster
        $klasterPendanaan->jenisOutputPenelitian()->sync($record);
    }

    /**
     * Proses penyimpanan mapping klaster pendanaan dengan jenis outcome penelitian.
     *
     * @param KlasterPendanaan $klasterPendanaan
     * @param array $data
     * @return void
     */
    private function saveKlasterOutcomePenelitian(KlasterPendanaan $klasterPendanaan, array $data)
    {
        $jenisOutcomePenelitian = $data['jenis_outcome_penelitian'] ?? [];

        $record = [];
        foreach ($jenisOutcomePenelitian as $IdJenisOutcomePenelitian => $isRequired) {
            $record[$IdJenisOutcomePenelitian] = [
                'apakah_wajib' => $isRequired
            ];
        }

        // detach true karena bisa dihapus dan tidak memiliki child nantinya, hanya sebagai table bantuan utk get yg outcome required dari klaster
        $klasterPendanaan->jenisOutcomePenelitian()->sync($record);
    }

    /**
     * Proses penyimpanan mapping klaster pendanaan dengan agenda kegiatan.
     *
     * @param KlasterPendanaan $klasterPendanaan
     * @param array $data
     * @return void
     */
    private function saveKlasterAgendaKegiatan(KlasterPendanaan $klasterPendanaan, array $data)
    {
        $agendaKegiatan = $data['agenda_kegiatan'] ?? [];

        $record = [];
        foreach ($agendaKegiatan as $idAgendaKegiatan => $agenda) {
            $record[$idAgendaKegiatan] = [
                'waktu_mulai' => $agenda['waktu_mulai'] ?? null,
                'waktu_selesai' => $agenda['waktu_selesai'] ?? null
            ];
        }

        $klasterPendanaan->agendaKegiatan()->sync($record);
    }

    /**
     * Proses upload document ke DMS.
     *
     * @param array $data
     * @param string $field
     * @param string $folderCode
     * @param KlasterPendanaan|null $model
     * @return UploadDokumen|null|Error
     */
    private function processUploadDocument(array $data, string $field, string $folderCode, KlasterPendanaan $model = null): UploadDokumen|null|Error
    {
        $document = $data[$field] ?? null;
        if (empty($document)) {
            return null;
        }

        $docName = $document->getClientOriginalName();
        $docName = pathinfo($docName, PATHINFO_FILENAME);
        $note = 'Dokumen Template RAB Klaster ' . $data['nama_klaster'] ?? null;

        $upload = new UploadDokumen();
        if (empty($model->{$field})) {
            $document = $upload->upload(
                file: $document,
                name: $docName,
                folderCode: $folderCode,
                note: $note,
                moduleCode: Modul::CODE_LITABMAS,
                withTransaction: false,
            );
        } else {
            $document = $upload->update(
                data: [
                    'file' => $document,
                    'name' => $docName,
                    'note' => $note,
                ],
                id: $model->{$field},
                withTransaction: false,
                isReplace: true
            );
        }

        if ($document instanceof Error) {
            return new Error($document->message);
        }

        return $document;
    }
    /*** --- [END] PRIVATE METHOD--- ***/

    public function getFormattedOutcomeAndBidangIlmu($klasterPendanaan, $outcomeMapping, $bidangIlmuTemaMapping)
    {
        // Mengambil periode pendanaan aktif
        $periodeAktif = $klasterPendanaan->sumberPendanaan->periodePendanaan;

        // Proses mapping outcome
        foreach ($outcomeMapping as $outcome) {
            $outcome->format_batas_pengumpulan_outcome = null;
            $startDatePeriod = Carbon::parse($periodeAktif->tanggal_mulai);

            // Tambahkan batas pengumpulan outcome dalam satuan tahun ke tanggal mulai
            $formatedCollectionLimit = $startDatePeriod->addYears((int) $outcome->batas_pengumpulan_outcome)
                ->translatedFormat('d F Y') . ' (' . $outcome->batas_pengumpulan_outcome . ' tahun)';

            $outcome->format_batas_pengumpulan_outcome = $formatedCollectionLimit; // contoh: 12 November 2026 (2 tahun)
        }

        // Mapping bidang ilmu dan tema
        $mappedBidangIlmuTema = [];
        foreach ($bidangIlmuTemaMapping as $lists) {
            foreach ($lists as $tema) {
                $mappedBidangIlmuTema[$tema->nama_bidang_ilmu][$tema->id_tema_kegiatan] = $tema->nama_tema;
            }
        }

        // Mengembalikan data yang sudah diformat
        return [$outcomeMapping, $mappedBidangIlmuTema];
    }
}
