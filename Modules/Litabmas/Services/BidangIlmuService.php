<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Litabmas\Models\BidangIlmu;
use Modules\Core\Helpers\Pagination;

class BidangIlmuService
{
    /**
     * @var BidangIlmu
     */
    protected $model = BidangIlmu::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new BidangIlmu;
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
        $table = $this->model->getTable();

        $sql = "select bi.id, bi.nama_bidang_ilmu
            from ".$table." bi";

        $order = [
            ['field' => 'info_left', 'direction' => 'asc'],
        ];

        $defaultFilter = "bi.waktu_dihapus IS NULL";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return BidangIlmu|Error
     */
    public function show(int $id): BidangIlmu|Error
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Sinkronisasi data Rumpun Bidang Ilmu dari modul Kepegawaian aplikasi SIAKAD V1.
     *
     * @return array
     */
    public function syncBidangIlmuFromHRSiakadV1()
    {
        $siakadV1Connection = DB::connection('siakadv1');
        $sql = "select kodebidang, namabidang, parentbidang, level, infoleft, inforight from hr.ms_bidang";

        try {
            $result = $siakadV1Connection->select($sql);
            $bidangIlmu = json_decode(json_encode($result), true);
        } catch (Exception $e) {
            return [true, $e->getMessage()];
        }

        // mapping column
        $pkBidangIlmu = ['kodebidang'];
        $mapping = [
            'namabidang' => ['column' => 'nama_bidang_ilmu'],
            'parentbidang' => ['column' => 'info_parent', 'default' => null],
            'level' => ['column' => 'info_level'],
            'infoleft' => ['column' => 'info_left'],
            'inforight' => ['column' => 'info_right'],
        ];

        list($err, $msg) = SyncSiakad::sync($mapping, $bidangIlmu, new BidangIlmu, $pkBidangIlmu);

        if (!$err) { // clear cache jika berhasil
            BidangIlmu::clearAllCache();
        }

        return [$err, $msg];
    }
}
