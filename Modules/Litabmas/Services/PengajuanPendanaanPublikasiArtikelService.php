<?php

namespace Modules\Litabmas\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PengajuanPendanaanPublikasiArtikel;

class PengajuanPendanaanPublikasiArtikelService extends PengajuanPendanaanPublikasiManagementService
{
    /**
     * @var PengajuanPendanaanPublikasiArtikel
     */
    protected $model = PengajuanPendanaanPublikasiArtikel::class;

    /**
     * Init service.
     */
    public function __construct() {
        $this->model = new PengajuanPendanaanPublikasiArtikel;
    }

    /**
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "select pp.judul_penelitian, ppd.tahun as tahun_periode_pendanaan, pp.kode_jenis_pendanaan,
                pppa.id, pppa.id_pengajuan_pendanaan, pppa.judul_artikel, pppa.situs_publikasi_jurnal,
                pppa.volume_dan_nomor_terbitan, pppa.url_artikel, pppa.status_publikasi,pppa.waktu_dibuat
            from $table as pppa
            join litabmas.pengajuan_pendanaan pp on pp.id = pppa.id_pengajuan_pendanaan and pp.waktu_dihapus is null
            join litabmas.sumber_pendanaan sp on sp.id = pp.id_sumber_pendanaan and sp.waktu_dihapus is null
            join litabmas.periode_pendanaan ppd on ppd.id = sp.id_periode_pendanaan and ppd.waktu_dihapus is null
            ";

        $idBiodata = auth()->user()?->biodata?->id;
        $roleUser = auth()->user()?->kode_role;
        if ($roleUser === Role::ROLE_DOSEN || $roleUser === Role::ROLE_DOSEN_EKSTERNAL) {
            // dia ketua atau bagian dari anggota
            $sql .= " join litabmas.pengajuan_pendanaan_anggota ppa on ppa.id_pengajuan_pendanaan = pppa.id_pengajuan_pendanaan
                and ppa.waktu_dihapus is null
                and ppa.id_biodata = :id_biodata
                and (ppa.apakah_undangan_diterima is null or ppa.apakah_undangan_diterima = true)
                ";
        }

        $bindings = [];
        if ($roleUser === Role::ROLE_DOSEN || $roleUser === Role::ROLE_DOSEN_EKSTERNAL) {
            $bindings['id_biodata'] = $idBiodata;
        }

        $fieldMap = [
            'tahun_periode_pendanaan' => 'ppd.tahun',
        ];

        $defaultFilter = "pppa.waktu_dihapus is null";

        // default order ketika tidak ada dari parameter
        if (empty($order)) {
            $defaultOrder = [['field' => 'pppa.id', 'direction' => 'desc']];
        }

        [$sql, $params] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            defaultOrder: $defaultOrder ?? [],
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            bindingUsingName: true
        );

        return Pagination::create($sql, $params, $page, $perPage, bindingUsingName: true);
    }

    /**
     * Menampilkan list data per pengajuan pendanaan.
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     * @param int|null $idPengajuanPendanaan
     * @return mixed
     */
    public function indexPerPengajuanPendanaan(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = [], int $idPengajuanPendanaan = null): mixed
    {
        $idPengajuanPendanaan ??= request()->route()->parameter('pengajuan_pendanaan') ?? null;
        if (empty($idPengajuanPendanaan)) {
            return new Error('Pengajuan Pendanaan tidak ditemukan.', 404);
        }

        $table = $this->model->getTable();
        $sql = "select pppa.id, pppa.id_jenis_outcome_penelitian, jop.nama_outcome, pppa.id_pengajuan_pendanaan, pppa.judul_artikel, pppa.situs_publikasi_jurnal,
                pppa.volume_dan_nomor_terbitan, pppa.url_artikel, pppa.status_publikasi, pppa.waktu_dibuat
            from $table as pppa
            join litabmas.jenis_outcome_penelitian jop on jop.id = pppa.id_jenis_outcome_penelitian and jop.waktu_dihapus is null";

        $bindings = ['idPengajuanPendanaan' => $idPengajuanPendanaan];
        $defaultFilter = "pppa.waktu_dihapus is null
            and pppa.id_pengajuan_pendanaan = :idPengajuanPendanaan";

        [$sql, $params] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            bindingUsingName: true
        );

        return Pagination::create($sql, $params, $page, $perPage, bindingUsingName: true);
    }
}
