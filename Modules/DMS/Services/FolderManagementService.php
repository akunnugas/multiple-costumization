<?php

namespace Modules\DMS\Services;

use Auth;
use DB;
use Exception;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\PaginationMultiple;
use Modules\DMS\Helpers\RoleManager;
use Modules\DMS\Models\Folder;
use Illuminate\Support\Str;
use Modules\Core\Helpers\Error;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\Modul;

class FolderManagementService
{
    /**
     * @var Folder
     */
    protected $model = Folder::class;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->model = new Folder;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    public function myFilesIndex(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "SELECT
                    f.*,
                    COALESCE(u.nama_user, '—') AS created_by_name,
                    'folder' as datatype
                FROM dms.folder f
                LEFT JOIN gate.user u ON u.id = f.dibuat_oleh 
                    AND f.waktu_dihapus IS NULL
                LEFT JOIN dms.folder p ON p.id = f.id_parent
                    AND p.waktu_dihapus IS NULL";
        $defaultFilter = "f.waktu_dihapus IS NULL AND
                          p.kode_folder = ? AND
                          f.dibuat_oleh IS NOT NULL";
        $bindings = [Modul::CODE_DMS];

        if (!RoleManager::isRootRole()) {
            $defaultFilter .= " AND (f.id_pemilik = ? OR f.id_unit_kerja = ?)";
            $bindings[] = Auth::user()->id;
            $bindings[] = Auth::user()->id_unit;
        }

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: ['waktu_diubah' => 'f.waktu_diubah', 'nama' => 'f.nama_folder'],
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function indexWithDokumen(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $dokumenService = new DokumenManagementService();

        $sqlFolders = "SELECT
                            f.*,
                            COALESCE(u.nama_user, '—') AS created_by_name,
                            COALESCE(u.email_user, '—') AS created_by_email,
                            'folder' as datatype
                       FROM dms.folder f
                       LEFT JOIN gate.user u ON u.id = f.dibuat_oleh AND f.waktu_dihapus IS NULL";
        $sqlDokumens = "SELECT
                            d.*,
                            COALESCE(u.nama_user, '—') AS created_by_name,
                            COALESCE(u.email_user, '—') AS created_by_email,
                            'file' as datatype
                        FROM dms.dokumen d
                        LEFT JOIN gate.user u ON u.id = d.dibuat_oleh AND u.waktu_dihapus IS NULL";

        $defaultFilters = ['f.waktu_dihapus IS NULL', 'd.waktu_dihapus IS NULL'];
        $filter = $dokumenService->prepareFilter($filter);

        $queries = PaginationMultiple::buildQuery(
            queries: [$sqlFolders, $sqlDokumens],
            orders: [
                in_array($order['field'], ['waktu_diubah', 'nama_folder'])
                    ? $order
                    : null,
                $order
            ],
            filters: [
                $this->getFolderFilterFromDokumens($filter),
                $filter
            ],
            defaultFilters: $defaultFilters,
            fieldMaps: [
                ['waktu_diubah' => 'f.waktu_diubah', 'nama' => 'f.nama_folder'],
                ['waktu_diubah' => 'd.waktu_diubah', 'nama' => 'd.nama_dokumen']
            ],
        );

        return PaginationMultiple::create($queries, $page, $perPage);
    }

    private function getFolderFilterFromDokumens($filter)
    {
        $index = array_search('id_folder', array_column($filter, 'field'));
        if ($index === false) return [];

        $filtered = array_filter($filter, fn ($item) => in_array($item['field'], ['waktu_diubah']));

        return array_merge([
            ['field' => 'id_parent', 'value' => $filter[$index]['value']],
        ], $filtered);
    }

    /**
     * Filter untuk list dokumen
     *
     * @return array[]
     */
    public function getFilter()
    {
        // semua extension file
        $extensions = Dokumen::query()->distinct('extension_versi_terbaru')
            ->get('extension_versi_terbaru')
            ->pluck('extension_versi_terbaru', 'extension_versi_terbaru')
            ->map(fn ($val) => Str::upper($val))
            ->prepend('Semua jenis file', '')
            ->toArray();
        // $extensions = DB::table('dms.dokumen')
        //     ->select('extension_versi_terbaru')
        //     ->distinct('extension_versi_terbaru')
        //     ->whereNull('waktu_dihapus')
        //     ->get()
        //     ->pluck('extension_versi_terbaru', 'extension_versi_terbaru')
        //     ->map(fn($val) => Str::upper($val))
        //     ->prepend('Semua jenis file', '')
        //     ->toArray();

        return [
            'extension_versi_terbaru' => ['options' => $extensions, 'label' => 'Jenis File'],
            'waktu_diubah' => ['options' => [
                '' => 'Semua tanggal',
                'td' => 'Hari ini',
                'lw' => '1 Minggu Terakhir',
                'lm' => '1 Bulan Terakhir',
            ], 'label' => 'Dimodifikasi']
        ];
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Folder
     */
    public function show(int $id): Folder
    {
        $model = $this->model->findOrFail($id);

        return $model;
    }

    /**
     * Menampilkan spesifik data berdasarkan kode.
     * @param  string  $code
     *
     * @return Folder
     */
    public function showByCode(string $code)
    {
        $sql =
            "SELECT 
                f.*, 
                pf.kode_folder as kode_parent
            FROM dms.folder f
            LEFT JOIN dms.folder as pf ON pf.id = f.id_parent
                AND pf.waktu_dihapus IS NULL
            WHERE f.kode_folder = :kode_folder
            AND f.waktu_dihapus IS NULL
            LIMIT 1";

        $data = DB::select($sql, ['kode_folder' => $code])[0] ?? null;

        if (!$data) {
            return new Error('Folder tidak ditemukan', 404);
        }

        $model = new $this->model();
        $model->forceFill((array)$data);

        if (!$this->authorizeFolderAccess($model)) {
            return new Error('Tidak diizinkan', 401);
        }

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Folder
     */
    public function store(array $data): Folder
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat membuat folder baru');
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Folder
     */
    public function update(array $data, int $id): Folder
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }

    public function showItems(int $id, bool $withNested)
    {
        $parent = $this->model->find($id);

        // Jika ambil children nested sampai children terbawah
        if ($withNested) {
            return $this->model->where('info_left', '>', $parent->info_left)
                ->where('info_left', '<', $parent->info_right)
                ->get();
        }

        // Return children hanya 1 level jika tidak nested
        return $this->model->where('id_parent', $parent->id)->get();
    }

    public function getOrganizations()
    {
        return UnitKerja::query()->select(['id', 'nama_unit'])->get();
    }

    public function getOrganizationsForSelect()
    {
        return $this->getOrganizations()
            ->mapWithKeys(fn ($org) => [$org->id => $org->nama_unit])
            ->toArray();
    }

    public function getUserRoot()
    {
        return $this->model->query()
            ->where('kode_folder', Modul::CODE_DMS)
            ->first(['id', 'nama_folder', 'apakah_hanya_lihat']);
    }

    public function getParents()
    {
        $sql = 
            "SELECT f.*, (
                SELECT count(id)
                    FROM dms.dokumen
                    WHERE id_folder IN (
                        SELECT id
                        FROM dms.folder
                        WHERE info_left >= f.info_left AND info_right < f.info_right
                ) AND waktu_dihapus IS NULL
            ) as dokumen_count
            FROM dms.folder f
            WHERE f.dibuat_oleh IS NULL
                AND f.kode_folder != ?
                AND f.waktu_dihapus IS NULL
                AND f.id_parent IS NULL";

        $data = DB::select($sql, [Modul::CODE_DMS]);

        return collect($data);
    }

    public function authorizeFolderAccess($folder)
    {
        return RoleManager::isRootRole() ||
            $folder->id_pemilik === Auth::user()->id || 
            $folder->id_unit_kerja === Auth::user()->id_unit;
    }

    public function authorizeFolderPermission($folder)
    {
        return !$folder->apakah_hanya_lihat && $this->authorizeFolderAccess($folder);
    }

    public function defineFields($withSize = true)
    {
        $fields = [
            ['field' => 'nama', 'label' => __('dms::header.nama_dokumen'), 'component' => true],
            ['field' => 'created_by_name', 'label' => __('dms::header.id_pemilik')],
            ['field' => 'waktu_diubah', 'label' => __('dms::header.waktu_diubah'), 'component' => true, 'searchable' => false],
        ];

        if ($withSize) {
            $fields[] = ['field' => 'ukuran', 'label' => __('dms::header.ukuran'), 'component' => true, 'searchable' => false];
        }

        $fields[] = ['field' => 'action', 'component' => 'dokumen'];

        return $fields;
    }

    public function destroy($id)
    {
        try {
            $folder = $this->model->query()->find($id);
            if (!$folder) {
                return new Error('Folder tidak ditemukan', 404);
            }

            $infoLeftRgt = [$folder->info_left, $folder->info_right];
            $childrens = $this->model->query()
                ->whereBetween('info_left', $infoLeftRgt)
                ->get();

            DB::transaction(function () use ($childrens) {
                foreach ($childrens as $child) {
                    $child->delete();
                }

                $ids = $childrens->pluck('id')->toArray();
                Dokumen::query()->whereIn('id_folder', $ids)->delete();
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function destroySome($ids)
    {
        try {
            $folders = $this->model->query()->whereIn('id', $ids)->get();
            $infoLeftRgts = $folders->map(fn ($folder) => [$folder->info_left, $folder->info_right]);
            $childrens = $this->model->query()
                ->where(function ($query) use ($infoLeftRgts) {
                    foreach ($infoLeftRgts as $infoLeftRgt) {
                        $query->orWhereBetween('info_left', $infoLeftRgt);
                    }
                })
                ->get();

            DB::transaction(function () use ($childrens) {
                foreach ($childrens as $child) {
                    $child->delete();
                }

                $ids = $childrens->pluck('id')->toArray();
                Dokumen::query()->whereIn('id_folder', $ids)->delete();
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    public function getFolders()
    {
        $sql = "SELECT f.*
                FROM dms.folder f
                WHERE f.waktu_dihapus IS NULL
                AND f.apakah_hanya_lihat = false";
        $bindings = [];

        if (!RoleManager::isRootRole()) {
            $sql .= " AND (f.id_pemilik = ? OR f.id_unit_kerja = ?)";
            $bindings[] = Auth::user()->id;
            $bindings[] = Auth::user()->id_unit;
        }

        $data = DB::select($sql, $bindings);

        usort($data, function ($a, $b) {
            return $a->info_left < $b->info_left ? -1 : 1;
        });

        return $data;
    }
}
