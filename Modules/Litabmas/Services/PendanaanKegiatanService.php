<?php

namespace Modules\Litabmas\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Core\Helpers\Pagination;

class PendanaanKegiatanService
{
    /**
     * @var SumberPendanaan
     */
    protected $model = SumberPendanaan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SumberPendanaan;
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
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "select sp.id, pp.tahun as periode, sp.nama_sumber_pendanaan, u.nama_unit as pengelola_bantuan, sp.total_pendanaan,
                    round(sp.total_pendanaan - sp.sisa_anggaran, 2) as dana_diberikan,
                    sp.sisa_anggaran as dana_tersisa,
                    count(pnp.*) as total_proposal
                    from litabmas.sumber_pendanaan sp
                join litabmas.periode_pendanaan pp on sp.id_periode_pendanaan = pp.id
                join core.unit_kerja u on sp.id_unit_kerja = u.id
                join litabmas.pengajuan_pendanaan pnp on pnp.id_sumber_pendanaan = sp.id and pnp.nominal_anggaran_disetujui is not null";

        $groupBy = 'sp.id, u.nama_unit, sp.nama_sumber_pendanaan, pp.tahun, sp.total_pendanaan, sp.sisa_anggaran, sp.sisa_anggaran';

        $fieldMap = [
            'periode' => 'pp.tahun',
            'nama_sumber_pendanaan' => 'sp.nama_sumber_pendanaan',
            'pengelola_bantuan' => 'u.nama_unit',
            'total_pendanaan' => 'sp.total_pendanaan',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            // bindings: $bindings,
            order: $order,
            filter: $filter,
            fieldMap: $fieldMap,
            groupBy: $groupBy,
            bindingUsingName: true,
        );

        return Pagination::create($sql, $bindings, $page, $perPage, bindingUsingName: true);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $idSumberPendanaan
     * @return Collection|ModelNotFoundException
     */
    public function show(int $idSumberPendanaan): Collection|ModelNotFoundException
    {
        $select = DB::select("select sp.id, pp.tahun as periode, sp.nama_sumber_pendanaan, u.nama_unit as pengelola_bantuan, sp.total_pendanaan,
                    round(sp.total_pendanaan - sp.sisa_anggaran, 2) as dana_diberikan,
                    sp.sisa_anggaran as dana_tersisa,
                    count(pnp.*) as total_proposal
                    from litabmas.sumber_pendanaan sp
                join litabmas.periode_pendanaan pp on sp.id_periode_pendanaan = pp.id
                join core.unit_kerja u on sp.id_unit_kerja = u.id
                left join litabmas.pengajuan_pendanaan pnp on pnp.id_sumber_pendanaan = sp.id and pnp.nominal_anggaran_disetujui is not null
                where sp.id = :id
                group by sp.id, u.nama_unit, sp.nama_sumber_pendanaan, pp.tahun, sp.total_pendanaan, sp.sisa_anggaran, sp.sisa_anggaran", ['id' => $idSumberPendanaan]);

        if (empty($select)) {
            throw new ModelNotFoundException('Data tidak ditemukan.');
        }

        return Collection::make($select[0]);
    }

    public function getDaftarProposal(int $idSumberPendanaan, int $idKlasterPendanaan = null): Collection
    {
        $filter['id_sumber_pendanaan'] = $idSumberPendanaan;

        if ($idKlasterPendanaan) {
            $filter['id_klaster_pendanaan'] = $idKlasterPendanaan;
        }

        $select = DB::select("select pnp.id, pnp.judul_penelitian as judul_proposal, kp.nama_klaster,
                anggota.nama_anggota, pnp.nominal_anggaran_disetujui, pnp.apakah_afirmasi, pnp.kode_registrasi
            from litabmas.pengajuan_pendanaan pnp
            join litabmas.klaster_pendanaan kp on pnp.id_klaster_pendanaan = kp.id
            join (
                select ppa.id_pengajuan_pendanaan as id,
                    string_agg(
                        case when ppa.apakah_ketua is true then c.nama || ' (Ketua)'
                        else c.nama end, ', '
                    ) as nama_anggota
                from litabmas.pengajuan_pendanaan_anggota ppa
                join core.biodata c on c.id = ppa.id_biodata
                group by ppa.id_pengajuan_pendanaan
            ) as anggota on anggota.id = pnp.id
            where pnp.id_sumber_pendanaan = :id_sumber_pendanaan
            " . ($idKlasterPendanaan ? "and pnp.id_klaster_pendanaan = :id_klaster_pendanaan" : "") . "
            and pnp.nominal_anggaran_disetujui is not null", $filter);

        return Collection::make($select);
    }
}
