<?php

namespace Modules\Litabmas\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Format;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Shared\RoleInternal;
use Modules\DMS\Models\Dokumen;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanAktivitasPenelitian;

class BimbinganService
{
    /**
     * @var PengajuanPendanaanAktivitasPenelitian
     */
    protected $model = PengajuanPendanaanAktivitasPenelitian::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanAktivitasPenelitian;
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
        $table = (new PengajuanPendanaan())->getTable();

        $sql = "SELECT pp.id, pp.judul_penelitian, pp.status_agenda_kegiatan, pp.kode_registrasi,
                kp.nama_klaster, sp.nama_sumber_pendanaan,
                sp.id_periode_pendanaan, ppp.id_dokumen_sk,
                fp.tahun as tahun_periode_pendanaan,
                b.id as id_ketua, b.nama as nama_ketua, ppp.status_bimbingan_logbook
            FROM $table pp
            join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan and kp.waktu_dihapus is null
            join litabmas.sumber_pendanaan sp on sp.id = pp.id_sumber_pendanaan and sp.waktu_dihapus is null
            join litabmas.periode_pendanaan fp on fp.id = sp.id_periode_pendanaan and fp.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan_anggota ppa on ppa.id_pengajuan_pendanaan = pp.id
                and ppa.apakah_ketua = true
                and ppa.waktu_dihapus is null
            join core.biodata b on b.id = ppa.id_biodata and b.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan_pembimbing ppp on ppp.id_pengajuan_pendanaan = pp.id
                and ppp.waktu_dihapus is null
            left join dms.dokumen d on d.id = ppp.id_dokumen_sk and d.waktu_dihapus is null
            ";
        $defaultFilter = "pp.waktu_dihapus IS NULL";

        $bindings = [];
        if (auth()->user()?->kode_role !== RoleInternal::CODE_V2_SUPERADMIN) {
            $idBiodata = auth()->user()?->biodata?->id;
            $defaultFilter .= " AND ppp.id_biodata = :id_biodata";
            $bindings = [
                'id_biodata' => $idBiodata
            ];
        }

        $fieldMap = [
            'tahun_periode_pendanaan' => 'fp.tahun',
            'nama_ketua' => 'b.nama',
            'id_dokumen_sk' => 'd.nama_dokumen',
            'id_sumber_pendanaan' => 'sp.id',
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

        $result = Pagination::create($sql, $bindings, $page, $perPage, bindingUsingName: true);
        $result->items = $this->processIndexDocuments($result->items);

        return $result;
    }

    /**
     * Data yang biasanya digunakan untuk info header resource bimbingan.
     *
     * @param int $idPengajuanPendanaan
     * @return Collection|Error
     */
    public function getInfoHeader(int $idPengajuanPendanaan)
    {
        $pengajuanPendanaanColumns = 'pp.id, pp.kode_registrasi, pp.judul_penelitian, pp.status_agenda_kegiatan,
            pp.nominal_anggaran_diajukan, pp.nominal_anggaran_disetujui, pp.mata_uang, pp.penilaian_index_similarity,
            pp.penilaian_index_ai';
        $periodePendanaanColumns = 'fp.tahun as nama_periode_pendanaan';
        $sumberPendanaanColumns = 'sp.nama_sumber_pendanaan, sp.id_periode_pendanaan';
        $klasterPendanaanColumns = 'kp.nama_klaster';
        $ketuaColumns = 'ketua.nama as nama_ketua';
        $anggotaColumns = 'anggota.daftar_anggota';
        $pembimbingColumns = 'ppp.id_dokumen_sk';

        $allColumns = $pengajuanPendanaanColumns . ', ' . $periodePendanaanColumns . ', ' . $sumberPendanaanColumns
            . ', ' . $klasterPendanaanColumns . ', ' . $ketuaColumns . ', ' . $anggotaColumns . ', ' . $pembimbingColumns;
        $sql = "SELECT $allColumns
            FROM litabmas.pengajuan_pendanaan pp
            join litabmas.klaster_pendanaan kp on kp.id = pp.id_klaster_pendanaan and kp.waktu_dihapus is null
            join litabmas.sumber_pendanaan sp on sp.id = pp.id_sumber_pendanaan and sp.waktu_dihapus is null
            join litabmas.periode_pendanaan fp on fp.id = sp.id_periode_pendanaan and fp.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan_pembimbing ppp on ppp.id_pengajuan_pendanaan = pp.id and ppp.waktu_dihapus is null
            join litabmas.pengajuan_pendanaan_anggota ppa on ppa.id_pengajuan_pendanaan = pp.id AND ppa.apakah_ketua = true
                and ppa.waktu_dihapus is null
            join core.biodata ketua on ketua.id = ppa.id_biodata and ketua.waktu_dihapus is null
            left join (
                select pp.id as id_pengajuan_pendanaan, string_agg(b.nama, ', ') as daftar_anggota
                from litabmas.pengajuan_pendanaan pp
                join litabmas.pengajuan_pendanaan_anggota ppa2 on ppa2.id_pengajuan_pendanaan = pp.id and ppa2.waktu_dihapus is null
                join core.biodata b on b.id = ppa2.id_biodata and b.waktu_dihapus is null
                where ppa2.apakah_ketua = false
                group by pp.id
            ) anggota on anggota.id_pengajuan_pendanaan = pp.id
            WHERE pp.waktu_dihapus IS NULL
                AND pp.id = :id
            ";

        $bindings = ['id' => $idPengajuanPendanaan];
        if (auth()->user()?->kode_role !== RoleInternal::CODE_V2_SUPERADMIN) {
            $sql .= " AND ppp.id_biodata = :id_biodata";
            $bindings['id_biodata'] = auth()->user()?->biodata?->id;
        }

        $select = DB::select($sql, $bindings);
        if (!isset($select[0])) {
            return new Error('Data tidak ditemukan.', 404);
        }

        $data = Collection::make($select[0]);

        // proses daftar anggota jadi & utk yg terakhir jika lebih dari satu
        $daftarAnggota = explode(', ', $data['daftar_anggota']);
        if (count($daftarAnggota) > 1) {
            $last = array_pop($daftarAnggota);
            $data['daftar_anggota'] = implode(', ', $daftarAnggota) . ' & ' . $last;
        }

        // format persentase, jika exactly number maka tidak perlu decimal
        $data['penilaian_index_similarity'] = Format::removeTrailingZeroes($data['penilaian_index_similarity']);
        $data['penilaian_index_ai'] = Format::removeTrailingZeroes($data['penilaian_index_ai']);

        return $data;
    }

    /*** --- [START] PRIVATE METHOD--- ***/
    /**
     * Kebutuhan utk dapetin detail document dari dms.
     *
     * @param array $items
     * @return array|mixed[]
     */
    private function processIndexDocuments(array $items)
    {
        $dokumenSkIds = collect($items)->pluck('id_dokumen_sk')->filter()->toArray();
        if (empty($dokumenSkIds)) {
            return $items;
        }

        // get daftar dokumen SK dari DMS
        $documents = Dokumen::whereIn('id', $dokumenSkIds)
            ->select('id', 'nama_dokumen', 'extension_versi_terbaru', 'alamat_versi_terbaru')
            ->get();

        // proses dapetin nama, asset_url & temp_url
        return collect($items)->map(function ($item) use ($documents) {
            $document = $documents->where('id', $item['id_dokumen_sk'])->first();
            if (empty($document)) {
                return $item;
            }

            $ext = $document->extension_versi_terbaru;
            $item['nama_dokumen'] = $document->nama_dokumen;
            $item['asset_url'] = $ext ? asset("images/$ext-solid.svg") : null;
            $item['temp_url'] = $document->lastVersionTemporaryUrl();
            return $item;
        })->toArray();
    }
    /*** --- [END] PRIVATE METHOD--- ***/
}
