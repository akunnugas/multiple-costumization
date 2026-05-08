<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Format;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\Mahasiswa;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Services\DokumenManagementService;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Enums\StatusAgendaKegiatanEnum;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PenilaianPembimbingAktivitasPenelitian;
use Modules\Litabmas\Models\PengajuanPendanaanOutputPenelitian;
use Modules\Litabmas\Models\PengajuanPendanaanIsianProposal;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;
use Modules\Litabmas\Models\PengajuanPendanaanPembimbing;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;
use Modules\Litabmas\Models\PenilaianReviewerLaporanProgres;

class PengajuanPendanaanService
{
    /**
     * @var PengajuanPendanaan
     */
    protected $model = PengajuanPendanaan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaan;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = [], $kodeJenisProposal = null): mixed
    {
        $agendaKegiatan = (new AgendaKegiatanService())->getListCache();
        $idPengumumanAdministrasi = $agendaKegiatan->where('kode_agenda', AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI)->first()?->id;
        $idPengumumanNominasi = $agendaKegiatan->where('kode_agenda', AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI)->first()?->id;
        $idPengumumanPendanaan = $agendaKegiatan->where('kode_agenda', AgendaKegiatan::STEP_PENGUMUMAN_PENDANAAN)->first()?->id;

        $table = $this->model->getTable();

        $sql = "SELECT pp.id, pp.judul_penelitian, pp.kode_registrasi,
                kp.nama_klaster, sp.nama_sumber_pendanaan,
                sp.id_periode_pendanaan,
                fp.tahun as tahun_periode_pendanaan,
                b.id as id_ketua, b.nama as nama_ketua, administrasi.waktu_mulai as pengumuman_administrasi,
                nominasi.waktu_mulai as pengumuman_nominasi, pendanaan.waktu_mulai as pengumuman_pendanaan,
                pp.status_agenda_kegiatan as status,
                CASE WHEN pp.nominal_anggaran_disetujui IS NOT NULL THEN true ELSE false END as status_pendanaan
            FROM $table pp
            join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan and kp.waktu_dihapus is null
            join litabmas.sumber_pendanaan sp on sp.id = pp.id_sumber_pendanaan and sp.waktu_dihapus is null
            join litabmas.periode_pendanaan fp on fp.id = sp.id_periode_pendanaan and fp.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan_anggota ppa on ppa.id_pengajuan_pendanaan = pp.id
                and ppa.apakah_ketua = true
                and ppa.waktu_dihapus is null
            join core.biodata b on b.id = ppa.id_biodata and b.waktu_dihapus is null
            left join litabmas.klaster_pendanaan_agenda_kegiatan administrasi on administrasi.id_klaster_pendanaan = kp.id
                and administrasi.id_agenda_kegiatan = :idPengumumanAdministrasi
                and administrasi.waktu_dihapus is null
            left join litabmas.klaster_pendanaan_agenda_kegiatan nominasi on nominasi.id_klaster_pendanaan = kp.id
                and nominasi.id_agenda_kegiatan = :idPengumumanNominasi
                and nominasi.waktu_dihapus is null
            left join litabmas.klaster_pendanaan_agenda_kegiatan pendanaan on pendanaan.id_klaster_pendanaan = kp.id
                and pendanaan.id_agenda_kegiatan = :idPengumumanPendanaan
                and pendanaan.waktu_dihapus is null
            ";
        $defaultFilter = "pp.waktu_dihapus IS NULL";

        $idBiodata = auth()->user()?->biodata?->id;
        $roleUser = auth()->user()->kode_role;
        
        $id_user = Auth()->user()->id;

        //mengatasi bug id_user double
        $idBiodata = Biodata::where('id_user', $id_user)->pluck('id')->toArray();
        $idBiodataIn = implode(',', $idBiodata);

        $bindings = [];
        if ($roleUser === Role::ROLE_DOSEN || $roleUser === Role::ROLE_DOSEN_EKSTERNAL) {
            // dia ketua atau bagian dari anggota
            $sql .= "join litabmas.pengajuan_pendanaan_anggota ppa2 on ppa2.id_pengajuan_pendanaan = pp.id
                AND ppa2.waktu_dihapus is null
                AND ppa2.id_biodata IN ($idBiodataIn)
                AND (
                    ppa2.apakah_undangan_diterima is null or ppa2.apakah_undangan_diterima = true
                )
                AND (ppa2.apakah_ketua = true OR pp.status_agenda_kegiatan != '" . PengajuanPendanaanStatus2::LEVEL1_DRAFT . "')
                ";
        } else {
            // ignore draft
            $defaultFilter .= " AND pp.status_agenda_kegiatan != '" . PengajuanPendanaanStatus2::LEVEL1_DRAFT . "'";
        }

        if ($kodeJenisProposal) {
            $defaultFilter .= " AND kp.kode_jenis_pendanaan = :kodeJenisProposal";
            $bindings['kodeJenisProposal'] = $kodeJenisProposal;
        }

        $bindings['idPengumumanAdministrasi'] = $idPengumumanAdministrasi;
        $bindings['idPengumumanNominasi'] = $idPengumumanNominasi;
        $bindings['idPengumumanPendanaan'] = $idPengumumanPendanaan;

        $fieldMap = [
            'tahun_periode_pendanaan' => 'fp.tahun',
            'nama_ketua' => 'b.nama',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            bindingUsingName: true
        );

        return Pagination::create($sql, $bindings, $page, $perPage, bindingUsingName: true);
    }

    /**
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getIdPeriodeDanKodeJenisPendanaan(int $idPengajuanPendanaan)
    {
        return $this->model->join('litabmas.sumber_pendanaan as sp', 'sp.id', '=', 'id_sumber_pendanaan')
            ->select('sp.id_periode_pendanaan', 'kode_jenis_pendanaan')
            ->findOrFail($idPengajuanPendanaan);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return Collection|Error
     */
    public function show(int $id): Collection|Error
    {
        $table = $this->model->getTable();

        $pengajuanPendanaanColumns = 'pp.*';
        $periodePendanaanColumns = 'fp.tahun as nama_periode_pendanaan';
        $sumberPendanaanColumns = 'sp.nama_sumber_pendanaan, sp.id_periode_pendanaan, sp.total_pendanaan, sp.sisa_anggaran';
        $klasterPendanaanColumns = 'kp.nama_klaster, kp.maksimal_anggaran';
        $pengelolaColumns = 'pengelola.nama_unit as nama_pengelola_bantuan';
        $outputColumns = 'output.jenis_output_penelitian';
        $bidangIlmuColumns = 'bi.nama_bidang_ilmu';
        $temaKegiatanColumns = 'tk.nama_tema';
        $ketuaColumns = 'b.id as id_ketua, b.nama as nama_ketua';
        $pembimbingColumns = 'bimbing.id as id_pembimbing, ppb.id_dokumen_sk as id_sk_pembimbing, bimbing.nama as nama_pembimbing';
        $apakahApprove = 'kp.apakah_butuh_approve_semua_anggota';

        $allColumns = $pengajuanPendanaanColumns . ', ' . $periodePendanaanColumns . ', ' . $sumberPendanaanColumns
            . ', ' . $klasterPendanaanColumns . ', ' . $pengelolaColumns . ', ' . $outputColumns . ', '
            . $bidangIlmuColumns . ', ' . $temaKegiatanColumns . ', ' . $ketuaColumns . ',' . $pembimbingColumns . ', ' . $apakahApprove;
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
            left join litabmas.pengajuan_pendanaan_pembimbing ppb on ppb.id_pengajuan_pendanaan = pp.id
                and ppb.waktu_dihapus is null
            left join core.biodata bimbing on bimbing.id = ppb.id_biodata
                and bimbing.waktu_dihapus is null
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

        // cek apakah data ada
        if (Error::isError($data)) {
            abort(404);
        }

        return $data;
    }

    /**
     * Get info detail pengajuan pendanaan (butuh utk detail pengajuan pendanaan & detail penilaian administrasi)
     *
     * @param int $idPengajuanPendanaan
     * @return array
     */
    public function getDetailPengajuanPendanaan(int $idPengajuanPendanaan)
    {
        // isian proposal section
        $isianProposal = $this->getDetailIsianProposalByIdPengajuanPendanaan($idPengajuanPendanaan);

        // peneliti section
        $peneliti = $this->getDetailPenelitiByIdPengajuanPendanaan($idPengajuanPendanaan);
        $penelitiDosen = $peneliti->whereIn(
            'jenis_anggota',
            [
                PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL
            ]
        );
        $penelitiMahasiswa = $peneliti->where('jenis_anggota', PengajuanPendanaanAnggota::JENIS_MAHASISWA);

        // dokumen section
        $dokumen = $this->getDetailDokumen($idPengajuanPendanaan);

        return [
            'isian_proposal' => $isianProposal,
            'dokumen' => $dokumen,
            'peneliti' => [
                'dosen' => $penelitiDosen,
                'mahasiswa' => $penelitiMahasiswa
            ],
        ];
    }

    public function hitungTotalNilai($dataPenilaian, $dataReviewerReviewProposal, $dataNilaiProposal, $bobotKey)
    {
        $totalNilai = 0;
        foreach ($dataPenilaian as $item) {
            $totalNilaiAspek = 0;
            foreach ($dataReviewerReviewProposal as $reviewer) {
                $skala = $dataNilaiProposal[$reviewer->id][$item->id] ?? null;
                if ($skala) {
                    $skala *= 100;
                    $nilai = $skala * $item->$bobotKey / 100;
                    $totalNilaiAspek += $nilai;
                }
            }
            $totalNilai += $totalNilaiAspek;
        }
        return $totalNilai;
    }

    /**
     * Pengecekan apakah user yg login adalah ketua dari pengajuan pendanaan.
     *
     * @param int $id
     * @param int|null $idBiodata
     * @return mixed
     */
    public function apakahKetuaPengajuanPendanaan(int $id, int $idBiodata = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;
        return PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
            ->where('id_biodata', $idBiodata)->where('apakah_ketua', true)
            ->exists();
    }

    /**
     * Cek apakah user termasuk bagian dari peneliti suatu pengajuan pendanaan.
     *
     * @param int $id
     * @param int|null $idBiodata
     * @return mixed
     */
    public function apakahPenelitiPengajuanPendanaan(int $id, int $idBiodata = null)
    {
        $idBiodata ??= auth()->user()?->biodata?->id;
        return PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
            ->where('id_biodata', $idBiodata)
            ->exists();
    }

    public function apakahPendaftaranDitutup(int $id)
    {
        $idKlasterPendanaan = $this->model->select('id_klaster_pendanaan')
            ->findOrFail($id)->id_klaster_pendanaan;
        return (new KlasterPendanaanService())->apakahPendaftaranDitutup($idKlasterPendanaan);
    }

    public function storeAndUpdateValidation($data)
    {
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return PengajuanPendanaan|Error
     */
    public function store(array $data): PengajuanPendanaan|Error
    {
        $result = $this->storeAndUpdateValidation($data);
        if (Error::isError($result)) {
            return $result;
        }

        $dataPengajuanPendanaan = Arr::only($data, [
            'id_periode_pendanaan',
            'kode_jenis_pendanaan',
            'judul_penelitian',
            'id_bidang_ilmu',
            'id_tema_kegiatan',
            'id_sumber_pendanaan',
            'id_klaster_pendanaan',
            'apakah_berkontribusi_bidang_ilmu',
            'nominal_anggaran_diajukan',
            'nama_pemilik_rekening',
            'nama_bank',
            'nomor_rekening',
            'cabang_bank',
            'status_agenda_kegiatan',
            'waktu_snk_disetujui'
        ]);

        $dataOutput = Arr::only($data, [
            'jenis_output_penelitian'
        ]);

        // filter! hanya get yg sesuai dengan kode_jenis_pendanaan: isian_proposal__{kode_jenis_pendanaan}__*
        $dataIsianProposal = array_filter($data, function ($key) use ($dataPengajuanPendanaan) {
            return str_contains($key, 'isian_proposal__' . $dataPengajuanPendanaan['kode_jenis_pendanaan'] . '__');
        }, ARRAY_FILTER_USE_KEY);

        $dataAnggota = Arr::only($data, [
            '_anggota_penelitian',
            'id_biodata_leader'
        ]);

        $isDrafting = $data['is_draft'] ?? false; // cek dari data request apakah draft

        DB::beginTransaction();

        try {
            // upload document
            $documents = $this->prosesUploadDokumenKeDB([
                [$data['id_dokumen_proposal'] ?? null, 'id_dokumen_proposal', FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_PROPOSAL],
                [$data['id_dokumen_rab'] ?? null, 'id_dokumen_rab', FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_RAB],
                [$data['id_foto_tabungan'] ?? null, 'id_foto_tabungan', FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_BUKU_TABUNGAN]
            ]);
            $proposalDocument = $documents['id_dokumen_proposal'] ?? null;
            $rabDocument = $documents['id_dokumen_rab'] ?? null;
            $photoOfFrontPageDocument = $documents['id_foto_tabungan'] ?? null;

            // get id dokumen dari dms nya
            $dataPengajuanPendanaan['id_dokumen_proposal'] = $proposalDocument?->get()->id ?? null;
            $dataPengajuanPendanaan['id_dokumen_rab'] = $rabDocument?->get()->id ?? null;
            $dataPengajuanPendanaan['id_foto_tabungan'] = $photoOfFrontPageDocument?->get()->id ?? null;

            // save pengajuan pendanaan
            $pengajuanPendanaan = $this->saveAndValidatePengajuanPendanaan($dataPengajuanPendanaan, isDrafting: $isDrafting);

            $this->saveAndValidateOutputPenelitian($pengajuanPendanaan, $dataOutput['jenis_output_penelitian'] ?? []);

            $this->saveAndValidateIsianProposal($pengajuanPendanaan, $dataIsianProposal);

            $resultAnggota = $this->saveAndValidateAnggota(
                $pengajuanPendanaan,
                $dataAnggota['_anggota_penelitian'] ?? [],
                $dataAnggota['id_biodata_leader'] ?? null,
                $isDrafting
            );
            if (Error::isError($resultAnggota)) {
                return $resultAnggota;
            }

            // execute upload ke s3
            $this->prosessUploadDokumenKeS3([$proposalDocument, $rabDocument, $photoOfFrontPageDocument]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return new Error(exception: $e);
        }

        DB::commit();

        return $pengajuanPendanaan;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return PengajuanPendanaan|Error
     */
    public function update(array $data, int $id): PengajuanPendanaan|Error
    {
        $result = $this->storeAndUpdateValidation($data, $id);
        if (Error::isError($result)) {
            return $result;
        }

        $dataPengajuanPendanaan = Arr::only($data, [
            'id_periode_pendanaan',
            'kode_jenis_pendanaan',
            'judul_penelitian',
            'id_bidang_ilmu',
            'id_tema_kegiatan',
            'id_sumber_pendanaan',
            'id_klaster_pendanaan',
            'apakah_berkontribusi_bidang_ilmu',
            'nominal_anggaran_diajukan',
            'nama_pemilik_rekening',
            'nama_bank',
            'nomor_rekening',
            'cabang_bank',
            'status_agenda_kegiatan',
            'waktu_snk_disetujui'
        ]);

        $dataOutput = Arr::only($data, [
            'jenis_output_penelitian'
        ]);

        // filter! hanya get yg sesuai dengan kode_jenis_pendanaan: isian_proposal__{kode_jenis_pendanaan}__*
        $dataIsianProposal = array_filter($data, function ($key) use ($dataPengajuanPendanaan) {
            return str_contains($key, 'isian_proposal__' . $dataPengajuanPendanaan['kode_jenis_pendanaan'] . '__');
        }, ARRAY_FILTER_USE_KEY);

        $dataAnggota = Arr::only($data, [
            '_anggota_penelitian',
            'id_biodata_leader'
        ]);

        $isDrafting = $data['is_draft'] ?? false; // cek dari data request apakah draft

        DB::beginTransaction();

        try {
            // get pengajuan pendanaan
            $modelPengajuanPendanaan = $this->model->findOrFail($id);

            // upload dokumen proposal, rab, dan foto tabungan ke db dms
            $documents = $this->prosesUploadDokumenKeDB([
                [$data['id_dokumen_rab'] ?? null, 'id_dokumen_rab', FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_RAB],
                [$data['id_dokumen_proposal'] ?? null, 'id_dokumen_proposal', FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_PROPOSAL],
                [$data['id_foto_tabungan'] ?? null, 'id_foto_tabungan', FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_BUKU_TABUNGAN]
            ], $modelPengajuanPendanaan);

            $proposalDocument = $documents['id_dokumen_proposal'] ?? null;
            $rabDocument = $documents['id_dokumen_rab'] ?? null;
            $photoOfFrontPageDocument = $documents['id_foto_tabungan'] ?? null;

            // TODO: mikirin gimana ketika kalo casenya hapus dokumen, kan null tuh
            // validasi hasil tiap uploadan dokumen, jika kosong maka dianggap tidak diubah/update dokumen nya
            if (!empty($proposalDocument)) {
                $dataPengajuanPendanaan['id_dokumen_proposal'] = $proposalDocument->get()->id ?? null;
            }
            if (!empty($rabDocument)) {
                $dataPengajuanPendanaan['id_dokumen_rab'] = $rabDocument->get()->id ?? null;
            }
            if (!empty($photoOfFrontPageDocument)) {
                $dataPengajuanPendanaan['id_foto_tabungan'] = $photoOfFrontPageDocument->get()->id ?? null;
            }

            // update data pengajuan pendanaan
            $pengajuanPendanaan = $this->saveAndValidatePengajuanPendanaan($dataPengajuanPendanaan, $modelPengajuanPendanaan, $isDrafting);

            $this->saveAndValidateOutputPenelitian($pengajuanPendanaan, $dataOutput['jenis_output_penelitian'] ?? []);

            $this->saveAndValidateIsianProposal($pengajuanPendanaan, $dataIsianProposal);

            $resultAnggota = $this->saveAndValidateAnggota(
                $modelPengajuanPendanaan,
                $dataAnggota['_anggota_penelitian'] ?? [],
                $dataAnggota['id_biodata_leader'] ?? null,
                $isDrafting
            );
            if (Error::isError($resultAnggota)) {
                return $resultAnggota;
            }

            // execute upload ke s3
            $this->prosessUploadDokumenKeS3([$proposalDocument, $rabDocument, $photoOfFrontPageDocument]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return new Error(exception: $e);
        }

        DB::commit();

        return $pengajuanPendanaan;
    }

    /**
     * Aksi ketika proposal yg sebeleumnya draft langsung diajukan.
     *
     * @param array $data
     * @param int $id
     * @return Error|PengajuanPendanaan
     */
    public function ajukanProposal(int $id)
    {
        // [Start] custom validation
        $pengajuanPendanaan = $this->model->findOrFail($id);

        DB::beginTransaction();

        try {
            $apakahButuhApproveSemuaAnggota = $pengajuanPendanaan->klasterPendanaan->apakah_butuh_approve_semua_anggota;
            $pengajuanPendanaan->status_agenda_kegiatan = $apakahButuhApproveSemuaAnggota
                ? PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA
                : PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN;

            // jika tidak butuh approve (berdasarkan setingan klaster pendanaan), maka set semua anggota diterima
            if (!$apakahButuhApproveSemuaAnggota) {
                PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
                    ->update(['apakah_undangan_diterima' => PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA]);
            } else {
                $apakahSemuaAnggotaMenerimaUndangan = (new PengajuanPendanaanAnggotaService())->apakahSemuaAnggotaMenerimaUndangan($id);
                if ($apakahSemuaAnggotaMenerimaUndangan) {
                    $pengajuanPendanaan->status_agenda_kegiatan = PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN;
                }
            }

            $pengajuanPendanaan->kode_registrasi = $this->generateKodeRegistrasi($pengajuanPendanaan);
            $pengajuanPendanaan->save();
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $pengajuanPendanaan;
    }

    public function terimaUndanganAnggota(int $id, int $idBiodata, $timelineActive = null)
    {
        $pengajuanPendanaan = $this->model->findOrFail($id);

        $otherDosen = DB::table('litabmas.pengajuan_pendanaan_anggota')
            ->where('id_pengajuan_pendanaan', $id)
            ->where('apakah_ketua', false)
            ->where('jenis_anggota', '!=', PengajuanPendanaanAnggota::JENIS_MAHASISWA)
            ->where('id_biodata', '!=', $idBiodata)
            ->get()->toArray();

        $otherDosen = array_filter($otherDosen, function ($item) {
            return $item->apakah_undangan_diterima === null || $item->apakah_undangan_diterima === false;
        });

        DB::beginTransaction();

        try {
            PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
                ->where('id_biodata', $idBiodata)
                ->update(['apakah_undangan_diterima' => PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA]);

            if ($timelineActive && $timelineActive->kode_agenda === AgendaKegiatan::STEP_PENDAFTARAN) {
                // jika semua anggota non-mahasiswa sudah menerima undangan, maka set status pengajuan pendanaan menjadi diajukan
                if (empty($otherDosen)) {
                    PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
                        ->where('jenis_anggota', PengajuanPendanaanAnggota::JENIS_MAHASISWA)
                        ->update(['apakah_undangan_diterima' => PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA]);

                    $pengajuanPendanaan->status_agenda_kegiatan = PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN;
                    $pengajuanPendanaan->save();
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $pengajuanPendanaan;
    }

    public function tolakUndanganAnggota(int $id, int $idBiodata, $timelineActive = null)
    {
        $pengajuanPendanaan = $this->model->findOrFail($id);

        DB::beginTransaction();

        try {
            PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
                ->where('id_biodata', $idBiodata)
                ->update(['apakah_undangan_diterima' => PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITOLAK]);

            if ($timelineActive && $timelineActive->kode_agenda === AgendaKegiatan::STEP_PENDAFTARAN) {
                $pengajuanPendanaan->status_agenda_kegiatan = PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA;
                $pengajuanPendanaan->save();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $pengajuanPendanaan;
    }

    public function batalkanUndanganAnggota(int $id, int $idBiodata, $timelineActive = null)
    {
        $pengajuanPendanaan = $this->model->findOrFail($id);

        DB::beginTransaction();

        try {
            PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $id)
                ->where('id_biodata', $idBiodata)
                ->update(['apakah_undangan_diterima' => PengajuanPendanaanAnggota::STATUS_UNDANGAN_MENUNGGU]);

            if ($timelineActive && $timelineActive->kode_agenda === AgendaKegiatan::STEP_PENDAFTARAN) {
                $pengajuanPendanaan->status_agenda_kegiatan = PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA;
                $pengajuanPendanaan->save();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $pengajuanPendanaan;
    }

    /**
     * Membatalkan pengajuan proposal, mengubah statusnya menjadi draft.
     *
     * @param array $data
     * @param int $id
     * @return Error|PengajuanPendanaan
     */
    public function batalkanProposal(int $id)
    {
        try {
            $pengajuanPendanaan = $this->model->findOrFail($id);
            $pengajuanPendanaan->status_agenda_kegiatan = StatusAgendaKegiatanEnum::DRAFT;
            $pengajuanPendanaan->save();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $pengajuanPendanaan;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        DB::beginTransaction();

        try {
            $pengajuanPendanaan = $this->model->findOrFail($id);

            // pengecekan hanya yg statusnya draft yg bisa dihapus
            if ($pengajuanPendanaan['status_agenda_kegiatan'] !== StatusAgendaKegiatanEnum::DRAFT) {
                return new Error('Status selain Draft, tidak bisa dihapus.');
            }

            // pengecekan hanya ketua yg bisa menghapus
            $isKetua = $this->apakahKetuaPengajuanPendanaan($id);
            if (!$isKetua) {
                return new Error('Anda tidak memiliki akses untuk menghapus data ini.');
            }

            // hapus file dokumen proposal, rab, dan foto tabungan dari dms
            if (!empty($pengajuanPendanaan->id_dokumen_proposal)) {
                (new DokumenManagementService)->destroy($pengajuanPendanaan->id_dokumen_proposal);
            }
            if (!empty($pengajuanPendanaan->id_dokumen_rab)) {
                (new DokumenManagementService)->destroy($pengajuanPendanaan->id_dokumen_rab);
            }
            if (!empty($pengajuanPendanaan->id_foto_tabungan)) {
                (new DokumenManagementService)->destroy($pengajuanPendanaan->id_foto_tabungan);
            }

            // hapus data yang berhubungan dengan pengajuan pendanaan
            PengajuanPendanaanOutputPenelitian::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();

            PengajuanPendanaanIsianProposal::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();

            PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();

            PengajuanPendanaanStatus2::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();

            // hapus pengajuan pendanaan
            $pengajuanPendanaan->delete();
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return null;
    }

    /**
     * Cek apakah user bisa mengajukan proposal pada klaster tertentu
     * berdasarkan konfigurasi multi-submission per klaster
     *
     * @param int $idKlasterPendanaan
     * @param int $idBiodata
     * @param int|null $idPengajuanPendanaan ID proposal yang sedang diedit (jika edit)
     * @return array ['can_submit' => bool, 'message' => string, 'current_count' => int, 'max_count' => int]
     */
    public function checkMultiSubmissionEligibility(int $idKlasterPendanaan, int $idBiodata, int $idPengajuanPendanaan = null): array
    {
        // Konfigurasi klaster
        $klaster = KlasterPendanaan::find($idKlasterPendanaan);

        if (!$klaster) {
            return [
                'can_submit' => false,
                'message' => 'Klaster pendanaan tidak ditemukan',
                'current_count' => 0,
                'max_count' => 0
            ];
        }

        // Hitung jumlah pengajuan user saat ini di klaster ini (exclude draft dan yang sedang diedit)
        $currentCountQuery = PengajuanPendanaan::where('id_klaster_pendanaan', $idKlasterPendanaan)
            ->whereHas('anggota', function ($query) use ($idBiodata) {
                $query->where('id_biodata', $idBiodata)
                    ->where('apakah_ketua', true);
            })
            ->where('status_agenda_kegiatan', '!=', PengajuanPendanaanStatus2::LEVEL1_DRAFT);

        // Jika sedang edit, exclude ID proposal yang sedang diedit
        if ($idPengajuanPendanaan) {
            $currentCountQuery->where('id', '!=', $idPengajuanPendanaan);
        }

        $currentCount = $currentCountQuery->count();

        // Cek batas maksimal pengajuan per klaster
        if ($klaster->maksimal_ajuan_per_user && $currentCount >= $klaster->maksimal_ajuan_per_user) {
            return [
                'can_submit' => false,
                'message' => "Anda sudah memiliki pengajuan di klaster ini. Klaster ini tidak mengizinkan pengajuan lebih dari {$klaster->maksimal_ajuan_per_user} proposal.",
                // 'message' => "Anda sudah mencapai batas maksimal pengajuan untuk klaster ini ({$currentCount} dari {$klaster->maksimal_ajuan_per_user} proposal).",
                'current_count' => $currentCount,
                'max_count' => $klaster->maksimal_ajuan_per_user
            ];
        }

        return [
            'can_submit' => true,
            'message' => 'Anda dapat mengajukan proposal di klaster ini',
            'current_count' => $currentCount,
            'max_count' => $klaster->maksimal_ajuan_per_user ?? null
        ];
    }

    /**
     * Get daftar user yang sudah mengajukan pendanaan pada periode pendanaan tertentu.
     *
     * @param int $idPeriodePendanaan
     * @param int|null $idPengajuanPendanaan
     * @return mixed
     * // FIXME: nama method perlu disesuaikan
     */
    public function getIdBiodataYangBolehMengajukanPendanaan(int $idPeriodePendanaan, int $idPengajuanPendanaan = null)
    {
        return $this->model->where('sp.id_periode_pendanaan', $idPeriodePendanaan)
            // exclude pengajuan pendanaan yang sedang diedit/dipilih
            ->when($idPengajuanPendanaan, function ($query) use ($idPengajuanPendanaan) {
                return $query->where('litabmas.pengajuan_pendanaan.id', '<>', $idPengajuanPendanaan);
            })
            // exclude pengajuan pendanaan yang tidak lolos administrasi, nominasi, dan pendanaan
            // anggotanya boleh mengajukan pendanaan lagi
            ->whereNotIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                StatusAgendaKegiatanEnum::TIDAK_LOLOS_ADMINISTRASI,
                StatusAgendaKegiatanEnum::TIDAK_LOLOS_NOMINASI,
                StatusAgendaKegiatanEnum::TIDAK_LOLOS_PENDANAAN,
            ])
            ->join('litabmas.klaster_pendanaan as kp', 'kp.id', 'litabmas.pengajuan_pendanaan.id_klaster_pendanaan')
            ->join('litabmas.sumber_pendanaan as sp', 'sp.id', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
            ->join('litabmas.pengajuan_pendanaan_anggota as ppa', 'ppa.id_pengajuan_pendanaan', 'litabmas.pengajuan_pendanaan.id')
            ->pluck('ppa.id_biodata')
            ->unique()
            ->toArray();
    }

    /**
     * Get output by id pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getOutputByIdPengajuanPendanaan(int $idPengajuanPendanaan): mixed
    {
        return PengajuanPendanaanOutputPenelitian::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->select('id_pengajuan_pendanaan', 'id_jenis_output_penelitian')
            ->get();
    }

    /**
     * Get isian proposal by id pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getIsianProposalByIdPengajuanPendanaan(int $idPengajuanPendanaan): mixed
    {
        return PengajuanPendanaanIsianProposal::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->select('id_pengajuan_pendanaan', 'id_aspek_penilaian_isian_proposal', 'isian_proposal')
            ->get();
    }

    /**
     * Get detail isian proposal untuk keperluan detail pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getDetailIsianProposalByIdPengajuanPendanaan(int $idPengajuanPendanaan): mixed
    {
        $pengajuanPendanaan = $this->model->join('litabmas.klaster_pendanaan as kp', 'kp.id', 'litabmas.pengajuan_pendanaan.id_klaster_pendanaan')
            ->join('litabmas.sumber_pendanaan as sp', 'sp.id', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
            ->select('litabmas.pengajuan_pendanaan.kode_jenis_pendanaan', 'sp.id_periode_pendanaan')
            ->findOrFail($idPengajuanPendanaan);

        $refIsianProposal = (new AspekPenilaianIsianProposalService())
            ->getByJenisPendanaanDanPeriodePendanaan($pengajuanPendanaan->kode_jenis_pendanaan, $pengajuanPendanaan->id_periode_pendanaan);
        $pendanaanIsianProposal = $this->getIsianProposalByIdPengajuanPendanaan($idPengajuanPendanaan);

        // olah data, walaupun isian proposal di pendanaannya masih kosong, tetep munculkan judul isian proposal nya
        $result = [];
        foreach ($refIsianProposal as $item) {
            $isianProposal = $pendanaanIsianProposal->where('id_aspek_penilaian_isian_proposal', $item->id)->first();
            $result[] = [
                'id_aspek_penilaian_isian_proposal' => $item->id,
                'judul_isian_proposal' => $item->nama_isian_proposal,
                'isian_proposal' => $isianProposal?->isian_proposal ?? null
            ];
        }

        return $result;
    }

    /**
     * Get anggota pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getAnggotaPengajuanPendanaan(int $idPengajuanPendanaan): mixed
    {
        $anggota = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->join('core.biodata as b', 'b.id', '=', 'litabmas.pengajuan_pendanaan_anggota.id_biodata')
            ->leftJoin('core.pegawai as p', 'p.id_biodata', '=', 'b.id')
            ->leftJoin('core.mahasiswa as m', 'm.id_biodata', '=', 'b.id')
            ->select(
                'pengajuan_pendanaan_anggota.id_biodata',
                'b.nama',
                'm.nim',
                DB::raw('COALESCE(p.nip, m.nim) as kustom_kode'),
                'pengajuan_pendanaan_anggota.apakah_ketua',
                'pengajuan_pendanaan_anggota.jenis_anggota',
                'pengajuan_pendanaan_anggota.apakah_undangan_diterima',
                DB::raw('(
                    SELECT
                        CONCAT(jp.kode_jenjang, \'-\', uk.nama_unit)
                    FROM
                        core.unit_kerja uk
                        JOIN core.jenjang_pendidikan jp ON uk.id_jenjang_pendidikan = jp.id
                    WHERE
                        uk.id = COALESCE(p.id_homebase_dosen, m.id_unit)
                        AND uk.waktu_dihapus IS NULL
                    LIMIT
                        1
                ) as nama_unit_kerja')
            )
            ->where('id_pengajuan_pendanaan', '=', $idPengajuanPendanaan)
            ->where('apakah_ketua', '=', false)
            ->orderBy('pengajuan_pendanaan_anggota.jenis_anggota', 'asc')
            ->orderBy(DB::raw('kustom_kode'))
            ->get();

        $anggota->transform(function ($item) {
            if (empty($item->kustom_kode)) {
                return $item;
            }

            if (!empty($item->nim)) {
                $item->id_biodata = $item->nim;
            }

            $item->nama_user = $item->kustom_kode . ' - ' . $item->nama;
            return $item;
        });

        return $anggota;
    }

    /**
     * Get detail peneliti untuk keperluan detail pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return mixed
     */
    public function getDetailPenelitiByIdPengajuanPendanaan(int $idPengajuanPendanaan)
    {
        // Query data peneliti
        $sql = "select * from (
            select distinct on (b.id) b.id, b.nama, COALESCE(p.nip, de.nip, m.nim) as kustom_kode,
            ppa.apakah_ketua, ppa.jenis_anggota, ppa.apakah_undangan_diterima,
            ptl.nama_pt as nama_pt_luar, p.id_homebase_dosen, m.id_unit as id_unit_mahasiswa
            from litabmas.pengajuan_pendanaan_anggota as ppa
            join core.biodata as b on b.id = ppa.id_biodata and b.waktu_dihapus is null
            left join core.pegawai as p on p.ref_key_siakad = b.ref_key_pegawai and b.waktu_dihapus is null
            left join litabmas.dosen_eksternal as de on de.id_biodata = b.id and de.waktu_dihapus is null
            left join core.perguruan_tinggi as ptl on ptl.id = de.id_perguruan_tinggi_luar and ptl.waktu_dihapus is null
            left join core.mahasiswa as m on m.id_biodata = b.id and m.waktu_dihapus is null
            where ppa.waktu_dihapus is null
                and ppa.id_pengajuan_pendanaan = :id_pengajuan_pendanaan
                and (p.nip is not null or de.nip is not null or m.nim is not null)
            order by b.id
        ) as peneliti
        order by apakah_ketua desc, jenis_anggota, kustom_kode";

        $peneliti = DB::select($sql, ['id_pengajuan_pendanaan' => $idPengajuanPendanaan]);
        $peneliti = Collection::make($peneliti);

        // Query semua unit kerja yang dibutuhkan
        $unitKerjaIds = $peneliti->pluck('id_homebase_dosen')->filter();
        $unitKerjaMhsIds = $peneliti->pluck('id_unit_mahasiswa')->filter();
        $unitKerjaIds = $unitKerjaIds->merge($unitKerjaMhsIds)->unique();
        $unitKerjaList = UnitKerja::whereIn('id', $unitKerjaIds)->get()->keyBy('id');

        // Query semua jenjang pendidikan yang dibutuhkan
        $jenjangPendidikanIds = $unitKerjaList->pluck('id_jenjang_pendidikan')->unique();
        $jenjangList = JenjangPendidikan::whereIn('id', $jenjangPendidikanIds)->get()->keyBy('id');

        $currentUniv = UnitKerja::optionByType([UnitKerja::UNIVERSITY], false);
        $currentUniv = reset($currentUniv); // get first array

        // Transform data tanpa query dalam loop
        $peneliti->transform(function ($item) use ($currentUniv, $unitKerjaList, $jenjangList) {
            // set nama_pt_luar
            $item->nama_pt = empty($item->nama_pt_luar) ? $currentUniv : $item->nama_pt_luar;

            // set kustom kode to nama_user
            if (!empty($item->kustom_kode)) {
                $item->nama_user = $item->kustom_kode . ' - ' . $item->nama;
            }

            // homebase
            if ($item->id_homebase_dosen) {
                $homebase = $unitKerjaList->get($item->id_homebase_dosen);
                if ($homebase) {
                    $jenjang = $jenjangList->get($homebase->id_jenjang_pendidikan);
                    $item->unit_kerja = $jenjang ? $jenjang->kode_jenjang . ' - ' . $homebase->nama_unit : '-';
                } else {
                    $item->unit_kerja = '-';
                }
            } else {
                $unit = $unitKerjaList->get($item->id_unit_mahasiswa);
                if ($unit) {
                    $jenjang = $jenjangList->get($unit->id_jenjang_pendidikan);
                    $item->unit_kerja = $jenjang ? $jenjang->kode_jenjang . ' - ' . $unit->nama_unit : '-';
                } else {
                    $item->unit_kerja = '-';
                }
            }

            return $item;
        });

        return $peneliti;
    }

    /**
     * Get daftar id dokumen berdasarkan id pengajuan pendanaan tertentu.
     *
     * @param int $id
     * @return array
     */
    public function getDocumentIds(int $id): array
    {
        return $this->model->select('id_dokumen_proposal', 'id_dokumen_rab', 'id_foto_tabungan')
            ->findOrFail($id)->toArray();
    }

    /**
     * Get detail dokumen berdasarkan id pengajuan pendanaan.
     *
     * @param int $id
     * @return array
     */
    public function getDetailDokumen(int $id)
    {
        $pengajuanPendanaanDocumentIds = $this->getDocumentIds($id);

        $dmsDocuments = Dokumen::whereIn('id', $pengajuanPendanaanDocumentIds)
            ->get()->map(function ($doc) use ($pengajuanPendanaanDocumentIds) {
                $doc->ukuran_file = Format::formatBytes($doc->last_version_size);
                $doc->temp_url = $doc->lastVersionTemporaryUrl();
                $doc->terakhir_diubah = Carbon::parse($doc->waktu_diubah)->diffForHumans();

                // set assetUrl berdasarkan ekstensi file
                $ext = $doc->extension_versi_terbaru;
                if ($ext == 'doc' || $ext == 'docx') {
                    $ext = 'doc';
                }

                $doc->asset_url = asset("images/$ext-solid.svg");
                if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
                    $doc->asset_url = $doc->lastVersionTemporaryUrl();
                }

                return $doc;
            })->toArray();

        // nampung di dalam result, agar walaupun tidak ada di dmsDocuments, tetep tampil daftar dokumen berdasarkan jenis_dokumen
        $result = [];
        foreach ($pengajuanPendanaanDocumentIds as $field => $value) {
            $doc = array_values(array_filter($dmsDocuments, fn($doc) => $doc['id'] == $value))[0] ?? [];
            $result[] = [
                'id' => $doc['id'] ?? null,
                'ukuran_file' => $doc['ukuran_file'] ?? null,
                'temp_url' => $doc['temp_url'] ?? null,
                'terakhir_diubah' => $doc['terakhir_diubah'] ?? null,
                'asset_url' => $doc['asset_url'] ?? null,
                'nama_dokumen' => $doc['nama_dokumen'] ?? null,
                'extension_versi_terbaru' => $doc['extension_versi_terbaru'] ?? null,
                'jenis_dokumen' => __('litabmas::pengajuan_pendanaan.' . $field)
            ];
        }

        return $result;
    }

    /**
     * Get daftar agenda kegiatan berdasarkan id pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return array
     */
    public function getAgendaKegiatanByIdPengajuanPendanaan(int $idPengajuanPendanaan, $kode_agenda = null)
    {
        $sql = "select ak.id, ak.kode_agenda, ak.nama_agenda, kpak.waktu_mulai, kpak.waktu_selesai
            from litabmas.agenda_kegiatan ak
            join litabmas.klaster_pendanaan_agenda_kegiatan kpak on kpak.id_agenda_kegiatan = ak.id and kpak.waktu_dihapus is null
            join litabmas.klaster_pendanaan kp on kp.id = kpak.id_klaster_pendanaan and kp.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan pp on pp.id_klaster_pendanaan = kp.id and pp.waktu_dihapus is null
            where ak.waktu_dihapus is null
                and pp.id = :id
            " . ($kode_agenda ? "and ak.kode_agenda = :kode_agenda" : "") . "
            order by ak.urutan
            ";

        $bindings = ['id' => $idPengajuanPendanaan];
        if ($kode_agenda) {
            $bindings['kode_agenda'] = $kode_agenda;
        }

        return DB::select($sql, $bindings);
    }

    /**
     * Get daftar timeline status agenda kegiatan berdasarkan id pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return array
     */
    public function getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan(int $idPengajuanPendanaan, $kode_agenda = null)
    {
        // agenda kegiatan
        $agendaKegiatan = $this->getAgendaKegiatanByIdPengajuanPendanaan($idPengajuanPendanaan, $kode_agenda);

        $now = Carbon::now();
        // $isActive = false;
        foreach ($agendaKegiatan as $agenda) {
            $agenda->completed = false;
            $agenda->waktu_mulai = !empty($agenda->waktu_mulai)
                ? Carbon::parse($agenda->waktu_mulai) : null;
            $agenda->waktu_selesai = !empty($agenda->waktu_selesai)
                ? Carbon::parse($agenda->waktu_selesai) : null;

            if ($agenda->waktu_mulai && $agenda->waktu_selesai) {
                $temp = $now->between($agenda->waktu_mulai->startOfDay(), $agenda->waktu_selesai->endOfDay());
                // if ($temp && !$isActive) {
                if ($temp) {
                    // $isActive = true;
                    $agenda->active = true;
                } else {
                    $agenda->active = false;
                }
            } else {
                $agenda->active = false;

                if ($agenda->waktu_mulai) {
                    $waktuMulai = $agenda->waktu_mulai->startOfDay();
                    $waktuSelesai = $waktuMulai->copy()->endOfDay();

                    $temp = $now->between($waktuMulai, $waktuSelesai);

                    // if ($temp && !$isActive) {
                    if ($temp) {
                        // $isActive = true;
                        $agenda->active = true;
                    }
                }

                if ($agenda->waktu_selesai) {
                    $waktuSelesai = $agenda->waktu_selesai->endOfDay();
                    $waktuMulai = $waktuSelesai->copy()->startOfDay();

                    $temp = $now->between($waktuMulai, $waktuSelesai);

                    // if ($temp && !$isActive) {
                    if ($temp) {
                        // $isActive = true;
                        $agenda->active = true;
                    }
                }
            }

            // jika waktu selesai sudah lewat, maka dianggap sudah selesai
            $endCheck = $agenda->waktu_selesai ?? $agenda->waktu_mulai;
            if ($endCheck && $now->gt($endCheck->endOfDay())) {
                $agenda->completed = true;
            }
        }

        return $agendaKegiatan;
    }

    /**
     * Get informasi similarity dan ai berdasarkan id pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return array
     */
    public function getInfoSimilarityDanAI(int $idPengajuanPendanaan)
    {
        $pengajuanPendanaan = $this->model->select(
            'penilaian_index_similarity',
            'id_dokumen_penilaian_similarity',
            'penilaian_index_ai',
            'id_dokumen_penilaian_ai',
            'id_sumber_pendanaan'
        )
            ->findOrFail($idPengajuanPendanaan);
        $sumberPendanaan = SumberPendanaan::select('maksimal_toleransi_similarity', 'maksimal_toleransi_ai')
            ->findOrFail($pengajuanPendanaan->id_sumber_pendanaan);

        // get info dokumen penilaian similarity dan ai dari DMS
        $documents = Dokumen::select('id', 'nama_dokumen', 'extension_versi_terbaru')
            ->whereIn('id', [$pengajuanPendanaan->id_dokumen_penilaian_similarity, $pengajuanPendanaan->id_dokumen_penilaian_ai])
            ->get();

        $documentSimilarity = $documents->where('id', $pengajuanPendanaan->id_dokumen_penilaian_similarity)->first();
        if (!empty($documentSimilarity)) {
            $extDocSimilarity = $documentSimilarity->extension_versi_terbaru;
            $extDocSimilarity = ($extDocSimilarity === 'docx') ? 'doc' : $extDocSimilarity;
            $assetUrlSimilarity = asset("images/$extDocSimilarity-solid.svg");
        }

        $documentAI = $documents->where('id', $pengajuanPendanaan->id_dokumen_penilaian_ai)->first();
        if (!empty($documentAI)) {
            $extDocAI = $documentAI->extension_versi_terbaru;
            $extDocAI = ($extDocAI === 'docx') ? 'doc' : $extDocAI;
            $assetUrlAI = asset("images/$extDocAI-solid.svg");
        }

        // set result
        $result['similarity'] = [
            'nama_aspek' => __('litabmas::pengajuan_pendanaan.nama_penilaian_index_similarity'),
            'nilai' => $pengajuanPendanaan->penilaian_index_similarity,
            'id_dokumen' => $pengajuanPendanaan->id_dokumen_penilaian_similarity,
            'nama_dokumen' => $documentSimilarity->nama_dokumen ?? null,
            'ekstensi_dokumen' => $extDocSimilarity ?? null,
            'aset_dokumen' => $assetUrlSimilarity ?? null,
            'melebihi_toleransi' => $pengajuanPendanaan->penilaian_index_similarity > $sumberPendanaan->maksimal_toleransi_similarity
        ];

        $result['ai'] = [
            'nama_aspek' => __('litabmas::pengajuan_pendanaan.nama_penilaian_index_ai'),
            'nilai' => $pengajuanPendanaan->penilaian_index_ai,
            'id_dokumen' => $pengajuanPendanaan->id_dokumen_penilaian_ai,
            'nama_dokumen' => $documentAI->nama_dokumen ?? null,
            'ekstensi_dokumen' => $extDocAI ?? null,
            'aset_dokumen' => $assetUrlAI ?? null,
            'melebihi_toleransi' => $pengajuanPendanaan->penilaian_index_ai > $sumberPendanaan->maksimal_toleransi_ai
        ];

        return $result;
    }

    /**
     * Info apakah anggota sesuai aturan klaster atau tidak berdasarkan pengajuan pendanaan.
     *
     * @param int $idPengajuanPendanaan
     * @return bool[]
     */
    public function apakahJumlahAnggotaSesuaiAturanKlaster(int $idPengajuanPendanaan): array
    {
        // get minimal & maksimal anggota dari aturan klaster
        $pengajuanPendanaan = PengajuanPendanaan::select('id_klaster_pendanaan')->findOrFail($idPengajuanPendanaan);
        $klasterPendanaan = (new KlasterPendanaanService())->getInfoAnggotaDanAnggaran($pengajuanPendanaan->id_klaster_pendanaan);

        // get jumlah anggota pengajuan pendanaan yg menerima undangan
        $anggota = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->where('apakah_undangan_diterima', PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA)
            ->where('apakah_ketua', false)
            ->count();

        $melebihiMaksimalAnggota = $anggota > $klasterPendanaan->maksimal_anggota;
        $kurangDariMinimalAnggota = $anggota < $klasterPendanaan->minimal_anggota;

        return [
            'sesuai' => !$melebihiMaksimalAnggota && !$kurangDariMinimalAnggota,
            'melebihi_maksimal_anggota' => $melebihiMaksimalAnggota,
            'kurang_dari_minimal_anggota' => $kurangDariMinimalAnggota
        ];
    }

    //get header pengajuan
    public function getHeaderPengajuan($idPengajuan = null, $idKlaster = null, $idSumber = null)
    {
        $isCreate = false;
        if (empty($idPengajuan)) {
            $isCreate = true;
        }

        if ($isCreate) {
            $sql = DB::table('litabmas.klaster_pendanaan')
                ->join('litabmas.sumber_pendanaan', 'klaster_pendanaan.id_sumber_pendanaan', '=', 'sumber_pendanaan.id')
                ->join('core.unit_kerja', 'sumber_pendanaan.id_unit_kerja', '=', 'unit_kerja.id')
                ->where('klaster_pendanaan.id', $idKlaster)
                ->where('sumber_pendanaan.id', $idSumber)
                ->select(
                    'klaster_pendanaan.id as id_klaster_pendanaan',
                    'sumber_pendanaan.id as id_sumber_pendanaan',
                    'klaster_pendanaan.kode_jenis_pendanaan',
                    'klaster_pendanaan.nama_klaster',
                    'klaster_pendanaan.kode_jenis_pendanaan',
                    'sumber_pendanaan.nama_sumber_pendanaan',
                    'unit_kerja.nama_unit as nama_pengelola_bantuan'
                );
        } else {
            $sql = DB::table('litabmas.pengajuan_pendanaan')
                ->join('litabmas.klaster_pendanaan', 'pengajuan_pendanaan.id_klaster_pendanaan', '=', 'klaster_pendanaan.id')
                ->join('litabmas.sumber_pendanaan', 'pengajuan_pendanaan.id_sumber_pendanaan', '=', 'sumber_pendanaan.id')
                ->join('core.unit_kerja', 'sumber_pendanaan.id_unit_kerja', '=', 'unit_kerja.id')
                ->where('pengajuan_pendanaan.id', $idPengajuan)
                ->select(
                    'klaster_pendanaan.id as id_klaster_pendanaan',
                    'sumber_pendanaan.id as id_sumber_pendanaan',
                    'klaster_pendanaan.kode_jenis_pendanaan',
                    'klaster_pendanaan.nama_klaster',
                    'klaster_pendanaan.kode_jenis_pendanaan',
                    'sumber_pendanaan.nama_sumber_pendanaan',
                    'unit_kerja.nama_unit as nama_pengelola_bantuan'
                );
        }

        return $sql->first();
    }

    /*** --- [START] PRIVATE METHOD--- ***/
    /**
     * Generate kode registrasi pengajuan pendanaan.
     * ex: 2401001004001
     *
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @param bool $regenerate
     * @return string
     */
    private function generateKodeRegistrasi(PengajuanPendanaan $pengajuanPendanaan, bool $regenerate = false)
    {
        // jika sudah ada kode registrasinya dan tidak generate ulang maka jangan diubah kode registrasinya
        if (!empty($pengajuanPendanaan->kode_registrasi) && !$regenerate) {
            return $pengajuanPendanaan->kode_registrasi;
        }

        // {tahun}-{jenis_pendanaan}-{sumber_pendanaan}-{klaster}-{nomor_urut}
        $tahun = substr(Carbon::now()->format('Y'), -2); // tahun 2 digit
        $jenisPendanaan = ($pengajuanPendanaan->kode_jenis_pendanaan === JenisPendanaanEnum::CODE_PENELITIAN) ? '01' : '02';
        $sumberPendanaan = str_pad($pengajuanPendanaan->sumberPendanaan->id, 3, '0', STR_PAD_LEFT);
        $klaster = str_pad($pengajuanPendanaan->klasterPendanaan->id, 3, '0', STR_PAD_LEFT);
        $dataUrut = PengajuanPendanaan::where('kode_registrasi', 'ilike', "$tahun$jenisPendanaan$sumberPendanaan$klaster%")
            ->orderBy('kode_registrasi', 'desc')->first();
        $nomorUrut = $dataUrut ? (int) substr($dataUrut->kode_registrasi, -3) + 1 : 1;
        $nomorUrut = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT); // nomor urut 3 digit

        return "$tahun$jenisPendanaan$sumberPendanaan$klaster$nomorUrut";
    }

    /**
     * Proses upload dokumen pengajuan pendanaan ke database.
     *
     * @param array $data (isinya document, field, dan folder code)
     * @param PengajuanPendanaan|null $model
     * @return array
     * @throws Exception
     */
    private function prosesUploadDokumenKeDB(array $data, PengajuanPendanaan $model = null)
    {
        $documents = [];
        foreach ($data as $item) {
            $document = $item[0] ?? null;
            $field = $item[1] ?? null;
            $folderCode = $item[2] ?? null;

            // ketika tidak ada document dianggap tidak ada update/perubahan jadi skip
            if (empty($document)) {
                continue;
            }

            // field tdk boleh kosong
            if (empty($field)) {
                throw new Exception('Field dokumen tidak boleh kosong');
            }

            // folder code yg diperbolehkan
            $allowedFolder = [
                FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_PROPOSAL,
                FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_RAB,
                FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_BUKU_TABUNGAN
            ];
            if (!in_array($folderCode, $allowedFolder)) {
                throw new Exception('Folder code tidak diperbolehkan');
            }

            $docName = explode('.', $document?->getClientOriginalName())[0];
            $docName = pathinfo($docName, PATHINFO_FILENAME);

            $upload = new UploadDokumen();
            $document = $upload->upload(
                file: $document,
                name: $docName,
                folderCode: $folderCode,
                note: null,
                moduleCode: Modul::CODE_LITABMAS,
                withTransaction: false,
            );

            if ($document instanceof Error) {
                // throw exception aja soalnya sekali gagal upload maka gagal semua, dan biar masuk catch
                throw new Exception($document->message);
            }

            $documents[$field] = $document;
        }

        return $documents;
    }

    /**
     * Proses upload dokumen pengajuan pendanaan ke S3.
     *
     * @param array $documents
     * @return void
     * @throws Exception
     */
    private function prosessUploadDokumenKeS3(array $documents)
    {
        foreach ($documents as $document) {
            if (!empty($document)) {
                $document->executeUpload();
                if (Error::isError($document->getError())) { // jika ada error saat proses dms
                    throw new Exception($document->getError());
                }
            }
        }
    }

    /**
     * Proses penyimapan dan validasi pengajuan pendanaan.
     *
     * @param array $data
     * @param PengajuanPendanaan|null $model
     * @param bool $isDrafting
     * @return mixed
     * @throws ValidationException
     */
    private function saveAndValidatePengajuanPendanaan(array $data, PengajuanPendanaan $model = null, bool $isDrafting = false): PengajuanPendanaan
    {
        // cari mata_uang berdasarkan id_klaster_pendanaan dari $data
        $klasterPendanaan = KlasterPendanaan::findOrFail($data['id_klaster_pendanaan']);
        $mataUang = $klasterPendanaan->mata_uang ?? config('money.defaults.currency');
        $totalBudgetFundSource = $klasterPendanaan->maksimal_anggaran;
        $convert = $mataUang !== config('money.defaults.currency');
        $humanTotalBudget = money($totalBudgetFundSource, $mataUang, $convert);

        // validasi nominal_anggaran_diajukan $data tidak boleh lebih dari total_pendanaan klaster_pendanaan
        if (!empty($data['nominal_anggaran_diajukan'])) {
            Validator::make($data, [
                'nominal_anggaran_diajukan' => "lte:$totalBudgetFundSource"
            ], [
                'nominal_anggaran_diajukan.lte' => __('litabmas::pengajuan_pendanaan.nominal_anggaran_diajukan') . " melampaui total budget Klaster Pendanaan ($humanTotalBudget)"
            ])->validate();
        }

        // set mata_uang berdasarkan id_klaster_pendanaan
        $data['mata_uang'] = $mataUang;

        // make data who has string empty '' to null
        $data = array_map(fn($item) => $item === '' ? null : $item, $data);

        // Save or update the model
        $model = $model ? tap($model)->update($data) : $this->model->create($data);

        // jika bukan draft maka generate kode registrasi
        if (!$isDrafting) {
            // jika tidak butuh approve (berdasarkan setingan klaster pendanaan), maka set semua anggota diterima
            if ($data['status_agenda_kegiatan'] === PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA) {
                if (!$klasterPendanaan->apakah_butuh_approve_semua_anggota) {
                    PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $model->id)
                        ->update(['apakah_undangan_diterima' => PengajuanPendanaanAnggota::STATUS_UNDANGAN_DITERIMA]);
                } else {
                    $apakahSemuaAnggotaMenerimaUndangan = (new PengajuanPendanaanAnggotaService())->apakahSemuaAnggotaMenerimaUndangan($model->id);
                    if ($apakahSemuaAnggotaMenerimaUndangan) {
                        $model->update(['status_agenda_kegiatan' => PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN]);
                    }
                }
            }

            // generate kode registrasi
            $model->update(['kode_registrasi' => $this->generateKodeRegistrasi($model)]);
        }

        return $model;
    }

    /**
     * Proses penyimpanan mapping output penelitian.
     *
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @param array $dataOutput
     * @return void
     */
    private function saveAndValidateOutputPenelitian(PengajuanPendanaan $pengajuanPendanaan, array $dataOutput = []): void
    {
        $result = (new KlasterPendanaanService())->getOutputWajibByIdKlasterPendanaan($pengajuanPendanaan->id_klaster_pendanaan);
        $requiredOutput = $result->isEmpty() ? [] : $result->toArray();

        // merge antara dataOutput dengan requiredOutput
        $record = [];
        foreach ($dataOutput as $idJenisOutputPenelitian => $output) {
            if (empty($output)) {
                continue;
            }

            $record[$idJenisOutputPenelitian] = [
                'id_pengajuan_pendanaan' => $pengajuanPendanaan->id,
                'id_jenis_output_penelitian' => $idJenisOutputPenelitian,
                'waktu_dibuat' => now(),
                'waktu_diubah' => now(),
            ];
        }
        foreach ($requiredOutput as $output) {
            $record[$output->id_jenis_output_penelitian] = [
                'id_pengajuan_pendanaan' => $pengajuanPendanaan->id,
                'id_jenis_output_penelitian' => $output->id_jenis_output_penelitian,
                'waktu_dibuat' => now(),
                'waktu_diubah' => now(),
            ];
        }

        // delete lalu insert
        PengajuanPendanaanOutputPenelitian::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();
        PengajuanPendanaanOutputPenelitian::insert($record);
    }

    /**
     * Proses penyimpanan mapping isian proposal.
     *
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @param array $dataIsianProposal
     * @return void
     */
    private function saveAndValidateIsianProposal(PengajuanPendanaan $pengajuanPendanaan, array $dataIsianProposal): void
    {
        if (empty($dataIsianProposal)) {
            return;
        }

        $record = [];
        foreach ($dataIsianProposal as $customKey => $value) {
            // split $kodeJenisPendanaanDanIdIsianProposal: isian_proposal__penelitian__1 => [penelitian, 1]
            $kodeJenisPendanaanDanIdIsianProposal = explode('__', $customKey);
            $idAspekPenilaianIsianProposal = $kodeJenisPendanaanDanIdIsianProposal[2];

            // regex script
            $regexScript = '/<script\b[^>]>(.?)<\/script>/is';
            $value = preg_replace($regexScript, '', $value);

            // Pengecekan jika kosong atau <p></p> tag yang tidak ada textnya
            $regexNull = '/(<([^>]+)>)/i';
            $hasText = (bool) preg_replace($regexNull, '', $value);
            if (!$hasText) {
                $value = null;
            }

            $record[] = [
                'id_pengajuan_pendanaan' => $pengajuanPendanaan->id,
                'id_aspek_penilaian_isian_proposal' => $idAspekPenilaianIsianProposal,
                'isian_proposal' => $value,
                'waktu_dibuat' => now(),
                'waktu_diubah' => now(),
            ];
        }

        // delete lalu insert
        PengajuanPendanaanIsianProposal::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();
        PengajuanPendanaanIsianProposal::insert($record);
    }

    /**
     * Proses penyimpanan mapping anggota pengajuan pendanaan.
     *
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @param array $dataAnggota
     * @param int|null $idBiodataLeader
     * @param bool $isDrafting
     * @return true|Error
     * @throws Exception
     */
    private function saveAndValidateAnggota(PengajuanPendanaan $pengajuanPendanaan, array $dataAnggota, int $idBiodataLeader = null, bool $isDrafting = false): true|Error
    {
        $idBiodataLeader ??= auth()->user()?->biodata?->id; // record diri sendiri

        // init record anggota (termasuk leader)
        $record = [];
        $record[] = [
            'id_pengajuan_pendanaan' => $pengajuanPendanaan->id,
            'id_biodata' => $idBiodataLeader,
            'apakah_ketua' => true,
            'jenis_anggota' => PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
            'apakah_undangan_diterima' => true,
            'waktu_dibuat' => now(),
            'waktu_diubah' => now(),
        ];

        // get klaster
        $klasterPendanaan = $pengajuanPendanaan->klasterPendanaan;
        $kategoriKlaster = $klasterPendanaan->kategori_klaster;
        $apakahButuhApproveSemuaAnggota = $klasterPendanaan->apakah_butuh_approve_semua_anggota;

        // cek kategori klaster
        if ($kategoriKlaster === KlasterPendanaan::KATEGORI_KELOMPOK) {
            if (!$isDrafting) { // jika bukan draft maka validasi khusus
                // 1. cek apakah ada anggota yang tidak memiliki kuota mendaftar
                $idPeriodePendanaan = $pengajuanPendanaan->sumberPendanaan->id_periode_pendanaan;
                $idPengajuanPendanaan = $pengajuanPendanaan->id;
                $biodataIdsAnggotaTidakMemilikiKuota = (new PengajuanPendanaanAnggotaService())->getDaftarIdBiodataAnggotaByKuotaMendaftar($idPeriodePendanaan, $idPengajuanPendanaan, false);
                $notEnoughtKuota = [];
                foreach ($dataAnggota as $anggota) {
                    if (in_array($anggota['id_biodata'], $biodataIdsAnggotaTidakMemilikiKuota)) {
                        $notEnoughtKuota[$anggota['id_biodata']] = $anggota['name'];
                    }
                }

                if (!empty($notEnoughtKuota)) {
                    $names = implode(', ', $notEnoughtKuota);
                    return new Error("Anggota $names tidak memiliki kuota mendaftar pada periode pendanaan ini.");
                }

                // 2. cek jika ada anggota yg sudah terdaftar sebagai reviewer
                $idSumberPendanaan = $pengajuanPendanaan->id_sumber_pendanaan;
                $reviewerBiodataIds = (new PengajuanPendanaanReviewerService())->getDaftarIdBiodataReviewerBySumberPendanaan($idSumberPendanaan);
                $reviewerBiodataIds = array_unique($reviewerBiodataIds);

                $errorReviewer = [];
                foreach ($dataAnggota as $anggota) {
                    if (in_array($anggota['id_biodata'], $reviewerBiodataIds)) {
                        $errorReviewer[$anggota['id_biodata']] = $anggota['name'];
                    }
                }

                if (!empty($errorReviewer)) {
                    $names = implode(', ', $errorReviewer);
                    return new Error("Anggota $names sudah terdaftar sebagai reviewer pada sumber pendanaan terpilih.");
                }

                // 3. cek minimal dan maksimal anggota sesuai aturan klaster
                $minimalAnggota = $klasterPendanaan->minimal_anggota;
                $maximalAnggota = $klasterPendanaan->maksimal_anggota;

                $jmlAnggota = count($dataAnggota);
                if ($jmlAnggota < $minimalAnggota) { // cek minimal anggota
                    return new Error("Berdasarkan aturan Klaster yang Anda pilih, minimal anggota peneliti adalah $minimalAnggota");
                }

                if ($jmlAnggota > $maximalAnggota) { // cek maksimal anggota
                    return new Error("Berdasarkan aturan Klaster yang Anda pilih, maksimal anggota peneliti adalah $maximalAnggota");
                }
            }

            // set record anggota
            $errorUserNotFound = [];
            foreach ($dataAnggota as $anggota) {
                $allowedMemberType = [
                    PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                    PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL,
                    PengajuanPendanaanAnggota::JENIS_MAHASISWA,
                ];
                if (!in_array($anggota['memberType'], $allowedMemberType)) {
                    throw new Exception('Jenis anggota tidak diperbolehkan');
                }

                // pastikan ada user nya
                if ($anggota['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
                    $mahasiswa = Mahasiswa::where('nim', $anggota['id_biodata'])->first();
                    $biodata = Biodata::find($mahasiswa?->id_biodata);
                } else {
                    $biodata = Biodata::find($anggota['id_biodata']);
                    if (empty($biodata)) {
                        $errorUserNotFound[$anggota['id_biodata']] = $anggota['name'];
                    }
                }

                // set record anggota
                $apakahUndanganDiterima = $apakahButuhApproveSemuaAnggota
                    ? ($anggota['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA ? true : null) // mahasiswa auto approve
                    : true;
                $record[] = [
                    'id_pengajuan_pendanaan' => $pengajuanPendanaan->id,
                    'id_biodata' => $biodata->id,
                    'apakah_ketua' => false,
                    'jenis_anggota' => $anggota['memberType'], // 'Dosen Internal', 'Dosen Eksternal', 'Mahasiswa'
                    'apakah_undangan_diterima' => $apakahUndanganDiterima,
                    'waktu_dibuat' => now(),
                    'waktu_diubah' => now(),
                ];
            }

            // jika ada user yang tidak ditemukan
            if (!empty($errorUserNotFound)) {
                $names = implode(', ', $errorUserNotFound);
                throw new Exception("Akun user $names tidak ditemukan.");
            }

            // get id yg undangan diterima nya true (data sebelumnya)
            $idBiodataYangMenerimaUndangan = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)
                ->where('apakah_undangan_diterima', true)->pluck('id_biodata')->toArray();
            $idBiodataYangMenolakUndangan = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)
                ->where('apakah_undangan_diterima', false)->pluck('id_biodata')->toArray();

            // update apakah_undangan_diterima
            foreach ($record as &$item) {
                // khusus mahasiswa always true (karena cuma numpang nama)
                $isJenisAnggotaMhs = $item['jenis_anggota'] === PengajuanPendanaanAnggota::JENIS_MAHASISWA;
                if ($isJenisAnggotaMhs) {
                    $item['apakah_undangan_diterima'] = true;
                    continue;
                }

                // jika tidak ada di user yang menerima undangan ataupun yang menolak, maka set null
                if (!in_array($item['id_biodata'], $idBiodataYangMenerimaUndangan) && !in_array($item['id_biodata'], $idBiodataYangMenolakUndangan)) {
                    continue;
                }

                // set true jika sudah ada di user yang menerima undangan
                if (in_array($item['id_biodata'], $idBiodataYangMenerimaUndangan)) {
                    $item['apakah_undangan_diterima'] = true;
                    continue;
                }

                // set null jika sudah ada di user yang menolak undangan
                if (in_array($item['id_biodata'], $idBiodataYangMenolakUndangan)) {
                    $item['apakah_undangan_diterima'] = false;
                }
            }
        }

        // delete
        PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $pengajuanPendanaan->id)->delete();

        // setelah di delete, insert baru
        PengajuanPendanaanAnggota::insert($record);

        return true;
    }

    /**
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @return array|true
     */
    private function sectionIsianProposalValidation(PengajuanPendanaan $pengajuanPendanaan)
    {
        $dataIsianProposal = $this->getIsianProposalByIdPengajuanPendanaan($pengajuanPendanaan->id);

        foreach ($dataIsianProposal as $isianProposal) {
            $regexScript = '/<script\b[^>]>(.?)<\/script>/is';
            $value = $isianProposal->isian_proposal ?? null;
            $value = preg_replace($regexScript, '', $value);

            // Pengecekan jika kosong atau <p></p> tag yang tidak ada textnya
            $regexNull = '/(<([^>]+)>)/i';
            $hasText = (bool) preg_replace($regexNull, '', $value);

            if (empty($value) || !$hasText) {
                return [
                    'error' => true,
                    'message' => 'Isian proposal tidak boleh ada yang kosong.'
                ];
            }
        }

        return true;
    }

    /**
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @return array|true
     */
    private function sectionAnggotaValidation(PengajuanPendanaan $pengajuanPendanaan)
    {
        $dataAnggota = $this->getAnggotaPengajuanPendanaan($pengajuanPendanaan->id);
        $klasterPendanaan = $pengajuanPendanaan->klasterPendanaan;

        if ($klasterPendanaan->kategori_klaster === KlasterPendanaan::KATEGORI_KELOMPOK) {
            // 1. cek apakah ada anggota yang tidak memiliki kuota mendaftar
            $idPeriodePendanaan = $pengajuanPendanaan->sumberPendanaan->id_periode_pendanaan;
            $idPengajuanPendanaan = $pengajuanPendanaan->id;
            $biodataIdsAnggotaTidakMemilikiKuota = (new PengajuanPendanaanAnggotaService())->getDaftarIdBiodataAnggotaByKuotaMendaftar($idPeriodePendanaan, $idPengajuanPendanaan, false);
            $notEnoughtKuota = [];
            foreach ($dataAnggota as $anggota) {
                if (in_array($anggota['id_biodata'], $biodataIdsAnggotaTidakMemilikiKuota)) {
                    $notEnoughtKuota[$anggota['id_biodata']] = $anggota['nama_user'];
                }
            }

            if (!empty($notEnoughtKuota)) {
                $names = implode(', ', $notEnoughtKuota);
                return [
                    'error' => true,
                    'message' => "Anggota $names tidak memiliki kuota mendaftar pada periode pendanaan ini."
                ];
            }

            // 2. cek jika ada anggota yg sudah terdaftar sebagai reviewer
            $idSumberPendanaan = $pengajuanPendanaan->id_sumber_pendanaan;
            $reviewerBiodataIds = (new PengajuanPendanaanReviewerService())->getDaftarIdBiodataReviewerBySumberPendanaan($idSumberPendanaan);
            $reviewerBiodataIds = array_unique($reviewerBiodataIds);

            $errorReviewer = [];
            foreach ($dataAnggota as $anggota) {
                if (in_array($anggota['id_biodata'], $reviewerBiodataIds)) {
                    $errorReviewer[$anggota['id_biodata']] = $anggota['name'];
                }
            }

            if (!empty($errorReviewer)) {
                $names = implode(', ', $errorReviewer);
                return [
                    'error' => true,
                    'message' => "Anggota $names sudah terdaftar sebagai reviewer pada sumber pendanaan terpilih."
                ];
            }

            // 3. cek minimal dan maksimal anggota sesuai aturan klaster
            $minimalAnggota = $klasterPendanaan->minimal_anggota;
            $maximalAnggota = $klasterPendanaan->maksimal_anggota;
            $jmlAnggota = $dataAnggota->count();

            if ($jmlAnggota < $minimalAnggota) {
                return [
                    'error' => true,
                    'message' => "Berdasarkan aturan Klaster yang Anda pilih, minimal anggota peneliti
                    adalah $minimalAnggota."
                ];
            }

            if ($jmlAnggota > $maximalAnggota) {
                return [
                    'error' => true,
                    'message' => "Berdasarkan aturan Klaster yang Anda pilih, maksimal anggota peneliti
                    adalah $maximalAnggota."
                ];
            }
        }

        return true;
    }

    /**
     * @param PengajuanPendanaan $pengajuanPendanaan
     * @return array|true
     */
    private function sectionRekeningValidation(PengajuanPendanaan $pengajuanPendanaan)
    {
        $dataPengajuanPendanaan = $pengajuanPendanaan->toArray();

        $requiredFields = [
            'nominal_anggaran_diajukan',
            'nama_pemilik_rekening',
            'nomor_rekening',
            'nama_bank',
        ];

        foreach ($requiredFields as $field) {
            if (empty($dataPengajuanPendanaan[$field])) {
                return [
                    'error' => true,
                    'message' => 'Informasi keuangan yang wajib tidak boleh kosong.'
                ];
            }
        }

        return true;
    }

    public function updateSimilarity(array $data, $idProposalPendanaan)
    {
        $dataSumberPendanaan = SumberPendanaan::find(DB::table('litabmas.pengajuan_pendanaan')
            ->where('id', $idProposalPendanaan)->value('id_sumber_pendanaan'));

        $keyDocument = array_filter(array_keys($data), function ($item) {
            return strpos($item, 'id_') !== false;
        });

        $file = null;
        if ($keyDocument) {
            $keyDocument = array_values($keyDocument)[0];

            $file = $data[$keyDocument] ?? null;

            unset($data[$keyDocument]);
        }

        try {
            DB::beginTransaction();

            DB::table('litabmas.pengajuan_pendanaan')
                ->where('id', $idProposalPendanaan)
                ->update($data);

            $proposal = DB::table('litabmas.pengajuan_pendanaan')
                ->where('id', $idProposalPendanaan)
                ->first();

            if ($proposal->penilaian_index_similarity && $proposal->penilaian_index_ai) {
                $is_lolos = true;

                if ($proposal->penilaian_index_similarity > $dataSumberPendanaan['maksimal_toleransi_similarity']) {
                    $is_lolos = false;
                }

                if ($proposal->penilaian_index_ai > $dataSumberPendanaan['maksimal_toleransi_ai']) {
                    $is_lolos = false;
                }

                $newData['status_similarity'] = $is_lolos ? PengajuanPendanaan::LOLOS_SIMILARITY_AI : PengajuanPendanaan::TIDAK_LOLOS_SIMILARITY_AI;

                $newData['status_agenda_kegiatan'] = $is_lolos ? PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI : PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI;

                DB::table('litabmas.pengajuan_pendanaan')
                    ->where('id', $idProposalPendanaan)
                    ->update($newData);
            }

            if ($file && !is_numeric($file)) {
                $fileName = explode('.', $file?->getClientOriginalName())[0];

                $folderCode = $keyDocument === 'id_dokumen_penilaian_similarity' ?
                    UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_SIMILARITY :
                    UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_AI;

                $upload = new UploadDokumen();
                $upload = $upload->upload(
                    file: $file,
                    name: $fileName,
                    folderCode: $folderCode,
                    moduleCode: Modul::CODE_LITABMAS,
                    note: null,
                    withTransaction: false
                );

                if (Error::isError($upload->getError())) {
                    return new Error($upload->getError());
                }

                // Execute upload
                $upload->executeUpload();

                DB::table('litabmas.pengajuan_pendanaan')
                    ->where('id', $idProposalPendanaan)
                    ->update([
                        $keyDocument => $upload->get()?->id
                    ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return true;
    }


    public function getAspekOutputPertanyaan(int $idProposalPendanaan)
    {
        $result = DB::table('litabmas.pengajuan_pendanaan')
            ->where('pengajuan_pendanaan.id', $idProposalPendanaan)
            ->join('litabmas.sumber_pendanaan as sp', 'sp.id', '=', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
            ->join('litabmas.periode_pendanaan as pp', 'pp.id', '=', 'sp.id_periode_pendanaan')
            ->join('litabmas.aspek_penilaian_output_pertanyaan as apop', 'apop.id_periode_pendanaan', '=', 'pp.id')
            ->select('apop.id', 'apop.pertanyaan_penilaian_output', 'apop.no')
            ->orderBy('apop.no')
            ->get();

        return $result;
    }

    public function updateStatusAgendaKegiatan(int $idProposalPendanaan, int $idAgendaKegiatan, $status, $dataAnggaran = null)
    {
        $proposalPendanaan = PengajuanPendanaan::where('id', $idProposalPendanaan)->first();
        $klasterPendanaan = KlasterPendanaan::where('id', $proposalPendanaan->id_klaster_pendanaan)->first();
        $sumberPendanaan = SumberPendanaan::where('id', $proposalPendanaan->id_sumber_pendanaan)->first();

        // pengecekan anggota klaster
        // UPDATE: Tambahkan pengecekan apakah klaster butuh approval semua anggota
        if (
            $status === PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI &&
            $klasterPendanaan->kategori_klaster === KlasterPendanaan::KATEGORI_KELOMPOK &&
            $klasterPendanaan->apakah_butuh_approve_semua_anggota // <-- Tambahan kondisi ini
        ) {
            $anggotaAccepted = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $idProposalPendanaan)
                ->where('apakah_ketua', false)
                ->where('apakah_undangan_diterima', true)
                ->count();

            if ($klasterPendanaan->minimal_anggota > $anggotaAccepted) {
                return new Error('Tandai Dokumen Lengkap gagal, proposal dapat di proses administrasi setelah <b>' . $klasterPendanaan->minimal_anggota . ' anggota</b> mengkonfirmasi undangan.');
            }
        }

        DB::beginTransaction();

        try {
            PengajuanPendanaan::where('id', $idProposalPendanaan)->update([
                'status_agenda_kegiatan' => $status
            ]);

            if ($status === PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN) {
                if (empty($dataAnggaran['nominal_anggaran_disetujui'])) {
                    return new Error('Nominal anggaran disetujui tidak boleh kosong.');
                }

                if ($dataAnggaran['nominal_anggaran_disetujui'] > $klasterPendanaan['maksimal_anggaran']) {
                    return new Error('Nominal anggaran disetujui tidak boleh melebihi maksimal anggaran klaster Rp' . number_format($klasterPendanaan['maksimal_anggaran'], 0, ',', '.'));
                }

                if ($dataAnggaran['nominal_anggaran_disetujui'] > $sumberPendanaan->sisa_anggaran) {
                    return new Error('Nominal anggaran disetujui tidak boleh melebihi sisa sumber pendanaan Rp' . number_format($sumberPendanaan->sisa_anggaran, 0, ',', '.'));
                }

                $sumberPendanaan->update([
                    'sisa_anggaran' => $sumberPendanaan->sisa_anggaran - $dataAnggaran['nominal_anggaran_disetujui']
                ]);

                PengajuanPendanaan::where('id', $idProposalPendanaan)->update([
                    'nominal_anggaran_disetujui' => $dataAnggaran['nominal_anggaran_disetujui'],
                    'apakah_afirmasi' => $dataAnggaran['is_afirmasi'],
                ]);
            }

            if ($status === PengajuanPendanaanStatus2::LEVEL10_BELUM_PENENTUAN_PENDANAAN) {
                $sumberPendanaan->update([
                    'sisa_anggaran' => $sumberPendanaan->sisa_anggaran + $proposalPendanaan->nominal_anggaran_disetujui
                ]);

                PengajuanPendanaan::where('id', $idProposalPendanaan)->update([
                    'nominal_anggaran_disetujui' => null,
                    'apakah_afirmasi' => false,
                ]);
            }

            PengajuanPendanaanStatus2::updateOrCreate([
                'id_pengajuan_pendanaan' => $idProposalPendanaan,
                'id_agenda_kegiatan' => $idAgendaKegiatan,
            ], [
                'status' => $status
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return true;
    }

    public function clearSimilarityReviewer(int $idProposalPendanaan)
    {
        DB::beginTransaction();

        try {
            PengajuanPendanaan::where('id', $idProposalPendanaan)->update([
                'penilaian_index_similarity' => null,
                'id_dokumen_penilaian_similarity' => null,
                'penilaian_index_ai' => null,
                'id_dokumen_penilaian_ai' => null,
                'status_similarity' => null,
            ]);

            PengajuanPendanaanReviewer::where('id_pengajuan_pendanaan', $idProposalPendanaan)->delete();
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return true;
    }

    public function updateDokumenSK(int $idProposalPendanaan, $file)
    {
        if ($file) {
            $fileName = explode('.', $file?->getClientOriginalName())[0];

            $upload = new UploadDokumen();
            $upload = $upload->upload(
                file: $file,
                name: $fileName,
                folderCode: UploadDokumen::LITABMAS_PENILAIAN_ADMINISTRASI_REVIEWER_SK,
                moduleCode: Modul::CODE_LITABMAS,
                note: null,
                withTransaction: false
            );

            if (Error::isError($upload->getError())) {
                return new Error($upload->getError());
            }

            // Execute upload
            $upload->executeUpload();

            $idDokumenSK = $upload->get()?->id;
        } else {
            $idDokumenSK = null;
        }

        DB::table('litabmas.pengajuan_pendanaan')
            ->where('id', $idProposalPendanaan)
            ->update([
                'id_dokumen_sk_peneliti' => $idDokumenSK
            ]);
    }

    public function getListIdProposalTerlibat($idBiodata = null, $month = null)
    {
        $query = DB::table('litabmas.pengajuan_pendanaan_anggota as ppa')
            ->join('litabmas.pengajuan_pendanaan as pp', 'pp.id', '=', 'ppa.id_pengajuan_pendanaan');

        $listIdProposalByReviewer = [];
        if ($idBiodata) {
            $query->where('ppa.id_biodata', $idBiodata);

            $queryReviewer = DB::table('litabmas.pengajuan_pendanaan_reviewers as ppr')
                ->join('litabmas.pengajuan_pendanaan as pp', 'pp.id', '=', 'ppr.id_pengajuan_pendanaan')
                ->where('ppr.id_biodata', $idBiodata);

            if ($month) {
                list($m, $y) = explode(' ', $month);
                $queryReviewer->whereRaw("EXTRACT(MONTH FROM pp.waktu_dibuat) = $m AND EXTRACT(YEAR FROM pp.waktu_dibuat) = $y");
            }

            $listIdProposalByReviewer = $queryReviewer->pluck('ppr.id_pengajuan_pendanaan')->toArray();
        }

        if ($month) {
            list($m, $y) = explode(' ', $month);
            $query->whereRaw("EXTRACT(MONTH FROM pp.waktu_dibuat) = $m AND EXTRACT(YEAR FROM pp.waktu_dibuat) = $y");
        }

        $listIdProposal = $query->groupBy('pp.id')->pluck('pp.id')->toArray();

        $merge = array_merge($listIdProposal, $listIdProposalByReviewer);

        return array_unique($merge);
    }

    public function getListIdKlasterProposalDiajukan($idBiodata)
    {
        return PengajuanPendanaanAnggota::join('litabmas.pengajuan_pendanaan as pp', 'pp.id', '=', 'litabmas.pengajuan_pendanaan_anggota.id_pengajuan_pendanaan')
            ->where('id_biodata', $idBiodata)->pluck('pp.id_klaster_pendanaan')->toArray();
    }

    public function getDraftPengajuanByBiodata(int $idKlasterPendanaan, int $idBiodata)
    {
        return PengajuanPendanaan::where('id_klaster_pendanaan', $idKlasterPendanaan)
            ->join('litabmas.pengajuan_pendanaan_anggota as ppa', 'ppa.id_pengajuan_pendanaan', '=', 'litabmas.pengajuan_pendanaan.id')
            ->where('ppa.id_biodata', $idBiodata)
            ->where('ppa.apakah_ketua', true)
            ->where('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', PengajuanPendanaanStatus2::LEVEL1_DRAFT)
            ->first();
    }

    public function getKeanggotaanProposal(int $idProposalPendanaan, int $idBiodata)
    {

        $id_user = Auth()->user()->id;

        //mengatasi bug id_user double
        $idBiodata = Biodata::where('id_user', $id_user)->pluck('id')->toArray();

        $anggota = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $idProposalPendanaan)
            ->whereIn('id_biodata', $idBiodata)
            ->join('core.biodata as b', 'b.id', '=', 'litabmas.pengajuan_pendanaan_anggota.id_biodata')
            ->select('b.id as id_biodata', 'b.nama as nama_user', 'litabmas.pengajuan_pendanaan_anggota.*')
            ->first();
        if (!$anggota) {
            return null;
        }

        return $anggota->toArray();
    }

    public function countProposalDiajukan()
    {
        return [
            JenisPendanaanEnum::CODE_PENELITIAN => PengajuanPendanaan::where('kode_jenis_pendanaan', JenisPendanaanEnum::CODE_PENELITIAN)
                ->whereIn('status_agenda_kegiatan', [PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA, PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN])
                ->count(),
            JenisPendanaanEnum::CODE_PENGABDIAN => PengajuanPendanaan::where('kode_jenis_pendanaan', JenisPendanaanEnum::CODE_PENGABDIAN)
                ->whereIn('status_agenda_kegiatan', [PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA, PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN])
                ->count(),
        ];
    }

    public function updateAktivitasPenelitian($idProposalPendanan, $data)
    {
        $payload = [];
        if ($data['id_dokumen'] && !is_numeric($data['id_dokumen'])) {
            $file = $data['id_dokumen'];
            $fileName = explode('.', $file?->getClientOriginalName())[0];

            $upload = new UploadDokumen();
            $upload = $upload->upload(
                file: $file,
                name: $fileName,
                folderCode: UploadDokumen::LITABMAS_AKTIVITAS_PENELITIAN_PENDUKUNG_FEEDBACK,
                moduleCode: Modul::CODE_LITABMAS,
                note: null,
                withTransaction: false
            );

            if (Error::isError($upload->getError())) {
                return new Error($upload->getError());
            }

            // Execute upload
            $upload->executeUpload();

            $idDokumen = $upload->get()?->id;

            $payload['id_dokumen_feedback_logbook'] = $idDokumen;
        }

        $payload['feedback_logbook'] = $data['feedback'];

        PenilaianPembimbingAktivitasPenelitian::updateOrCreate([
            'id_pengajuan_pendanaan_pembimbing' => PengajuanPendanaanPembimbing::where('id_pengajuan_pendanaan', $idProposalPendanan)->value('id'),
            'id_pengajuan_pendanaan_aktivitas_penelitian' => $data['id_aktivitas_penelitian'],
        ], $payload);
    }

    public function getTimelineActive($timelines)
    {
        $now = now();
        $timelineActive = null;
        $isBypassDisabled = false;

        // ambil timeline yang aktif
        foreach ($timelines as $timeline) {
            if ($timeline->active) {
                $timelineActive = $timeline;
                break;
            }
        }

        // jika tidak ada timeline aktif, ambil timeline berikutnya setelah yang paling baru
        if (!$timelineActive) {
            foreach ($timelines as $key => $timeline) {
                $waktu = $timeline->waktu_selesai ?? $timeline->waktu_mulai;

                if ($now->gt(Carbon::parse($waktu))) {
                    continue;
                }

                if (
                    in_array($timeline->kode_agenda, [
                        AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI,
                        AgendaKegiatan::STEP_PENENTUAN_NOMINASI,
                        AgendaKegiatan::STEP_PENENTUAN_PENDANAAN
                    ])
                ) {
                    $timelineActive = $timeline;
                    $isBypassDisabled = true;
                    break;
                }
            }
        }

        // jika sudah tidak ada agenda action, pure ambil agenda setelahnya
        if (!$timelineActive) {
            foreach ($timelines as $key => $timeline) {
                if ($timeline->completed === false) {
                    $timelineActive = $timeline;
                    $isBypassDisabled = true;
                    break;
                }
            }
        }

        return [$timelineActive, $isBypassDisabled];
    }

    public function getDataIsianProposal($id)
    {
        return DB::table('litabmas.pengajuan_pendanaan_isian_proposal as pi')
            ->join('litabmas.aspek_penilaian_isian_proposal as a', 'a.id', '=', 'pi.id_aspek_penilaian_isian_proposal')
            ->where('pi.id_pengajuan_pendanaan', $id)
            ->select('a.nama_isian_proposal', 'pi.isian_proposal')
            ->get();
    }

    public function getFinalScore($total, $dataReviewer)
    {
        return count($dataReviewer) > 0 ? $total / count($dataReviewer) : null;
    }

    public function getTxtDataPeneliti($dataPenelitiDosen, $dataPenelitiMahasiswa)
    {
        $mergePeneliti = array_merge($dataPenelitiDosen, $dataPenelitiMahasiswa);

        $txtKetua = '';
        $txtAnggota = '<div>';
        $indexAnggpta = 0;
        foreach ($mergePeneliti as $index => $data) {
            if ($data->apakah_ketua) {
                $txtKetua = $data->nama_user;
                unset($mergePeneliti[$index]);
            } else {
                $txtAnggota .= ++$indexAnggpta . '. ' . $data->nama_user . '<br/>';
            }
        }
        $txtAnggota .= '</div>';

        if (empty($mergePeneliti)) {
            $txtAnggota = '-';
        }

        $mergePeneliti = array_values($mergePeneliti);

        return [$txtKetua, $txtAnggota];
    }

    public function getStateStatus($id)
    {
        return PengajuanPendanaanStatus2::where('id_pengajuan_pendanaan', $id)
            ->whereIn('status', [
                PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI,
                PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI,
                PengajuanPendanaanStatus2::LEVEL7_PENINJAUAN_PROPOSAL,
                PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI,
                PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI,
                PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN,
                PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN
            ])
            ->pluck('status')
            ->toArray();
    }

    public function getDataOutputPenelitian($id)
    {
        return DB::table('litabmas.pengajuan_pendanaan_output_penelitian as a')
            ->join('litabmas.pengajuan_pendanaan as p', 'p.id', '=', 'a.id_pengajuan_pendanaan')
            ->join('litabmas.klaster_pendanaan_output_penelitian as op', function ($join) {
                $join->on('op.id_klaster_pendanaan', '=', 'p.id_klaster_pendanaan')
                    ->on('a.id_jenis_output_penelitian', '=', 'op.id_jenis_output_penelitian');
            })
            ->join('litabmas.jenis_output_penelitian as j', 'j.id', '=', 'a.id_jenis_output_penelitian')
            ->leftJoin('litabmas.pengajuan_pendanaan_laporan_output as o', 'o.id_pengajuan_pendanaan_output_penelitian', '=', 'a.id')
            ->where('a.id_pengajuan_pendanaan', $id)
            ->select('a.*', 'j.nama_output', 'o.id_dokumen_output', 'op.apakah_wajib')
            ->get();
    }

    public function getMappingLaporanProgresAntara($id)
    {
        $dataLaporanAntara[PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES] = PengajuanPendanaanLaporanProgres::where('id_pengajuan_pendanaan', $id)
            ->where('jenis_laporan_progres', PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES)
            ->first();

        $dataLaporanAntara[PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA] = PengajuanPendanaanLaporanProgres::where('id_pengajuan_pendanaan', $id)
            ->where('jenis_laporan_progres', PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA)
            ->first();

        $dataPenilaianReviewerOutput = PenilaianReviewerLaporanProgres::join('litabmas.pengajuan_pendanaan_laporan_progres as l', 'l.id', '=', 'litabmas.penilaian_reviewer_laporan_progres.id_pengajuan_pendanaan_laporan_progres')
            ->where('l.id_pengajuan_pendanaan', $id)
            ->select('litabmas.penilaian_reviewer_laporan_progres.*', 'l.jenis_laporan_progres')
            ->get();

        return [$dataLaporanAntara, $dataPenilaianReviewerOutput];
    }
}
