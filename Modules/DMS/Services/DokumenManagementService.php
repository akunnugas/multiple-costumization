<?php

namespace Modules\DMS\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\PaginationMultiple;
use Modules\DMS\Helpers\RoleManager;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Models\DokumenCache;
use Modules\DMS\Models\DokumenKolaborator;
use Modules\DMS\Models\DokumenUnit;
use Modules\DMS\Models\DokumenPerizinan;
use Modules\DMS\Models\DokumenTag;
use Modules\DMS\Models\DokumenVersi;
use Modules\DMS\Models\Folder;
use Modules\DMS\Models\Tag;

class DokumenManagementService
{
    /**
     * @var Dokumen
     */
    protected $model = Dokumen::class;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->model = new Dokumen;
    }

    /**
     * Temporary data.
     *
     * @var array
     */
    protected $temporary = [];

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
        $sql = "SELECT
                    d.*,
                    COALESCE(u.nama_user, '-') AS created_by_name
                FROM dms.dokumen d
                LEFT JOIN gate.user u ON u.id = d.dibuat_oleh AND u.waktu_dihapus IS NULL";
        $defaultFilter = 'd.waktu_dihapus IS NULL';
        $filter = $this->prepareFilter($filter);
        $fieldMap = ['waktu_diubah' => 'd.waktu_diubah', 'nama_dokumen' => 'd.nama_dokumen'];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function indexSearch(string $query, int|null $page = null, int|null $perPage = null, array $order = [], array $filter = [], $folderId = null): mixed
    {
        $searchQuery =
            "SELECT d.*, f.nama_folder, f.kode_folder
            FROM dms.dokumen d
            LEFT JOIN dms.folder f ON f.id = d.id_folder
                AND f.waktu_dihapus IS NULL
            LEFT JOIN (
                SELECT
                    dt.id_dokumen,
                    string_agg(t.nama_tag, ' ') AS tags
                FROM dms.dokumen_tag dt
                LEFT JOIN dms.tag t ON t.id = dt.id_tag
                GROUP BY dt.id_dokumen
            ) tg ON tg.id_dokumen = d.id
            WHERE to_tsvector(f.nama_folder || ' ' || tags || ' ' || d.nama_dokumen || '.' || d.extension_versi_terbaru) @@ to_tsquery(?)
            AND d.waktu_dihapus IS NULL";

        $tsQuery = $this->prepareTSQuery($query);

        $sqls = [$searchQuery];
        $bindings = [[$tsQuery]];
        $orders = [$order];
        $filters = [$filter];

        if ($folderId) {
            // prioritaskan folder
            $sqls = [
                $searchQuery . " AND d.id_folder = ?",
                $searchQuery . " AND d.id_folder != ?",
            ];

            $bindings = [[$tsQuery, $folderId], [$tsQuery, $folderId]];
            $orders = [$order, $order];
            $filters = [$filter, $filter];
        }

        $queries = PaginationMultiple::buildQuery(
            queries: $sqls,
            bindingss: $bindings,
            orders: $orders,
            filters: $filters,
        );

        return PaginationMultiple::create($queries, $page, $perPage);
    }

    public function prepareTSQuery($query)
    {
        $words = explode(' ', $query);
        $words = array_filter($words, fn ($word) => !!$word);
        $words = array_map(fn ($word) => $word . ':*', $words);

        return implode(' & ', $words);
    }

    public function prepareFilter($filter)
    {
        $dates = [
            // today
            'td' => Carbon::now()->startOfDay(),
            // last week
            'lw' => Carbon::now()->subWeek()->startOfDay(),
            // last month
            'lm' => Carbon::now()->subMonth()->startOfDay(),
        ];

        return array_map(function ($val) use ($dates) {
            if (@$val['field'] == 'waktu_diubah') {
                $val['operator'] = '>';
                $val['value'] = $dates[$val['value']] ?? $val['value'];
            }

            return $val;
        }, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Dokumen
     */
    public function show(int $id): Dokumen
    {
        $model = $this->model->findOrFail($id);

        return $model;
    }

    public function showBySlug(string $slug): Dokumen|Error
    {
        $model = $this->model->selectRaw("dokumen.*, COALESCE(u.nama_user, '-') AS created_by_name, f.kode_folder AS kode_folder")
            ->leftJoin('gate.user AS u', 'u.id', '=', 'dokumen.dibuat_oleh')
            ->leftJoin('dms.folder AS f', 'f.id', '=', 'dokumen.id_folder')
            ->where('dokumen.slug', $slug)
            ->firstOrFail();

        $permission = $this->authorizeDokumentPermission($model);

        // Cek error perizinan
        if ($permission instanceof Error) {
            return $permission;
        }

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data required
     * @var UploadedFile $data['file'] required
     * @var int $data['kode_folder'] required
     * @var string $data['nama_dokumen'] required
     * @var string $data['kode_modul'] required
     * @var string $data['catatan'] required
     * @var int $data['visibilitas'] required
     * @var array $data['tags'] optional
     *
     * @return Dokumen|Error
     */
    public function store(array $data, bool $withTransaction = true, bool $autoExecuteUpload = true): Dokumen|Error
    {
        if ($withTransaction) {
            DB::beginTransaction();
        }

        // Ambil ukuran file
        $folder = Folder::where('kode_folder', $data['kode_folder'])->first();
        $slug = Str::uuid();
        $ext = $data['file']->getClientOriginalExtension();
        $size = $data['file']->getSize();

        // Jika terdapat error upload
        if ($data['file']->getError()) {
            return new Error('Gagal upload file', 500);
        }

        // Default upload pertama kali versi 1
        // Upload file dulu
        $versionNumber = 1;
        $uploadedUrl = $this->uploadDokumen(
            file: $data['file'],
            clientCode: request()?->client['kode_klien'],
            folderPath: $folder->full_folder_path,
            slug: $slug,
            version: $versionNumber,
        );

        // Masukkan ke database dokumen
        try {
            $model = $this->model->create([
                ...$data,
                'id_folder' => $folder->id,
                'ukuran' => $size,
                'alamat_versi_terbaru' => $uploadedUrl,
                'extension_versi_terbaru' => $ext,
                'versi_terbaru' => $versionNumber,
                'slug' => $slug,
            ]);
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        // Masukkan ke dalam tabel versi dokumen
        DokumenVersi::create([
            'id_dokumen' => $model->id,
            'versi' => $versionNumber,
            'alamat_berkas' => $uploadedUrl,
            'ukuran' => $size,
            'extension' => $ext,
        ]);

        // Buat permission
        $permissions = [
            [
                'id_dokumen' => $model->id,
                'nama_perizinan' => 'Organization',
                'jenis_perizinan' => DokumenPerizinan::ORGANIZATION,
                'waktu_dibuat' => Carbon::now(),
                'waktu_diubah' => Carbon::now(),
            ], [
                'id_dokumen' => $model->id,
                'nama_perizinan' => 'Collaborator',
                'jenis_perizinan' => DokumenPerizinan::COLLABORATOR,
                'waktu_dibuat' => Carbon::now(),
                'waktu_diubah' => Carbon::now(),
            ]
        ];

        DokumenPerizinan::insert($permissions);

        if (isset($data['tags'])) {
            $this->addTags($model, $data['tags']);
        }

        if ($autoExecuteUpload) {
            $executeUpload = $this->executeUpload();
            if ($executeUpload instanceof Error) {
                return $executeUpload;
            }
        }

        if ($withTransaction) {
            DB::commit();
        }

        return $model;
    }

    /**
     * Buat data multiple.
     *
     * @param array $data
     * @param bool $withTransaction
     * @param bool $autoExecuteUpload
     *
     * @return array|Error
     */
    public function storeMultiple(array $data, bool $withTransaction = true, bool $autoExecuteUpload = true): array|Error
    {
        if ($withTransaction) {
            DB::beginTransaction();
        }

        $result = [];

        foreach ($data as $item) {
            $result[] = $this->store($item, false, false);
        }

        if ($autoExecuteUpload) {
            $executeUpload = $this->executeUpload();
            if ($executeUpload instanceof Error) {
                return $executeUpload;
            }
        }

        if ($withTransaction) {
            DB::commit();
        }

        return $result;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Dokumen|Error
     */
    public function update(array $data, int $id, bool $withTransaction = true, bool $autoExecuteUpload = true, bool $isReplace = true): Dokumen|Error
    {
        if ($withTransaction) {
            DB::beginTransaction();
        }

        $model = $this->model->findOrFail($id);

        if (isset($data['file'])) {
            if ($isReplace) {
                // Hapus Versi Dokumen
                $model->versions()->delete();

                // Hapus dari storage
                Storage::delete($model->alamat_versi_terbaru);

                // Masukkan ke dalam tabel versi dokumen
                $this->addVersion(
                    id: $model->id,
                    file: $data['file'],
                    clientCode: request()?->client['kode_klien'],
                    isReplace: true,
                    autoExecuteUpload: $autoExecuteUpload,
                );
            } else {
                // Masukkan ke dalam tabel versi dokumen
                $this->addVersion(
                    id: $model->id,
                    file: $data['file'],
                    clientCode: request()?->client['kode_klien'],
                    isReplace: false,
                    autoExecuteUpload: $autoExecuteUpload,
                );
            }
        }

        try {
            // Jika nama kosong maka gunakan nama file yang lama
            $data['nama_dokumen'] = !empty($data['nama_dokumen']) ? $data['nama_dokumen'] : $model->name;
            $model->update($data);
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        DokumenCache::destroy($model->slug);

        if ($autoExecuteUpload) {
            $executeUpload = $this->executeUpload();
            if ($executeUpload instanceof Error) {
                return $executeUpload;
            }
        }

        if ($withTransaction) {
            DB::commit();
        }

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param bool $withTransaction
     * @param bool $autoExecuteUpload
     */
    public function updateMultiple(array $data, bool $withTransaction = true, bool $autoExecuteUpload = true, bool $isReplace = true): array|Error
    {
        if ($withTransaction) {
            DB::beginTransaction();
        }

        $result = [];

        foreach ($data as $item) {
            $result[] = $this->update($item, $item['id'], false, false, $isReplace);
        }

        if ($autoExecuteUpload) {
            $executeUpload = $this->executeUpload();
            if ($executeUpload instanceof Error) {
                return $executeUpload;
            }
        }

        if ($withTransaction) {
            DB::commit();
        }

        return $result;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id): void
    {
        $this->model->destroy($id);
    }

    /**
     * Buat versi dokumen baru.
     *
     * @param int $id
     * @param UploadedFile $file
     *
     * @return DokumenVersi|Error
     */
    public function addRevision(
        int $id,
        UploadedFile $file,
    ): DokumenVersi|Error {

        DB::beginTransaction();
        $new = $this->addVersion($id, $file, request()?->client['kode_klien']);
        DB::commit();

        return $new;
    }

    /**
     * Setting perizinan dokumen
     *
     * @param int|Dokumen $dokumen
     * @param string $type
     * @param array $data
     * @param bool $withTransaction
     *
     * @return Dokumen
     */
    public function setPermission(int|Dokumen $dokumen, string $type, array $data, bool $withTransaction = true): Dokumen
    {
        if (is_int($dokumen)) {
            $dokumen = Dokumen::findOrFail($dokumen);
        }

        if ($withTransaction) {
            DB::beginTransaction();
        }

        // Jika set permission, maka visibility diset ke private
        $dokumen->visibilitas = Dokumen::VISIBILITY_PRIVATE;
        $dokumen->save();

        if ($type === DokumenPerizinan::COLLABORATOR) {
            $this->setCollaborator($dokumen, $data);
        } else if ($type === DokumenPerizinan::ORGANIZATION) {
            $this->setOrganization($dokumen, $data);
        }

        if ($withTransaction) {
            DB::commit();
        }

        return $dokumen;
    }

    /**
     * Ambil path dokumen.
     *
     * @param string $id
     * @param string|null $version
     *
     * @return mixed
     */
    public function showPath(string $id, string|null $version = null)
    {
        $dokumen = $this->showByVersion($id, $version);

        // Cek error dokumen
        if ($dokumen instanceof Error) {
            return $dokumen;
        }

        // Cek perizinan dokumen
        $permission = $this->authorizeDokumentPermission($dokumen);

        // Cek error perizinan
        if ($permission instanceof Error) {
            return $permission;
        }

        return $dokumen->path;
    }

    /**
     * Ambil dokumen berdasarkan versi.
     *
     * @param string $id
     * @param string|null $version
     *
     * @return Error|Dokumen
     */
    private function showByVersion(string $id, string|null $version = null): Error|Dokumen
    {
        // Cek valid uuid slug
        if (!Str::isUuid($id)) {
            return new Error('Not Found', 404);
        }

        $dokumen = DokumenCache::find($id);

        if (!$dokumen) {
            return new Error('Not Found', 404);
        }

        if (isset($version)) {
            $dokumenVersion = $dokumen->versions()->where('versi', $version)->firstOrFail();
            $dokumen->path = $dokumenVersion->alamat_berkas;
        } else {
            $dokumen->path = $dokumen->alamat_versi_terbaru;
        }

        return $dokumen;
    }

    /**
     * Cek perizinan dokumen
     *
     * @param Dokumen $dokumen
     *
     * @return Error|null
     */
    public function authorizeDokumentPermission($dokumen): Error|null
    {
        // Cek perizinan dokumen jika public langsung return null
        if ($dokumen->visibilitas === Dokumen::VISIBILITY_PUBLIC || RoleManager::isRootRole()) {
            return null;
        }

        // Cek perizinan dokumen jika private
        $authUser = Auth::user();

        // Cek jika owner dokumen, maka bisa mengakses dokumen
        if ($dokumen->dibuat_oleh === $authUser->id) {
            return null;
        }

        if ($dokumen->folder->id_unit_kerja === $authUser->id_unit_kerja) {
            return null;
        }

        $permissions = $dokumen->permissions->toArray();

        $organizationPermission = array_values(array_filter($permissions, function ($item) {
            return $item['jenis_perizinan'] === DokumenPerizinan::ORGANIZATION;
        }));

        $collaboratorPermission = array_values(array_filter($permissions, function ($item) {
            return $item['jenis_perizinan'] === DokumenPerizinan::COLLABORATOR;
        }));

        // Pengecekan perizinan dokumen di tingkat organisasi terlebih dahulu
        $hasOrganizationPermission = false;
        if (!empty($organizationPermission)) {
            $authUserOrganization = UnitKerja::where('id', $authUser->id_unit_kerja)->first('id');
            $hasOrganizationPermission = !!DokumenUnit::where('id_dokumen_perizinan', $organizationPermission[0]['id'])
                ->where('id_unit_kerja', $authUserOrganization?->id)->first('id');
        }

        // Pengecekan perizinan dokumen di tingkat kolaborator
        $hasCollaboratorPermission = false;
        if (!empty($collaboratorPermission)) {
            $hasCollaboratorPermission = !!DokumenKolaborator::where('id_dokumen_perizinan', $collaboratorPermission[0]['id'])
                ->where('id_user', $authUser?->id)->first('id');
        }

        // Pengecekan jika mempunyai salah satu perizinan kolaorator atau organisasi maka bisa mengakses dokumen
        if (!$hasOrganizationPermission && !$hasCollaboratorPermission) {
            return new Error('Unauthorized action', 401);
        }

        return null;
    }

    public function authorizeDokumenManagementPermission($dokumen): Error|null
    {
        if (RoleManager::isRootRole()) {
            return null;
        }

        $authUser = Auth::user();

        if ($dokumen->folder->id_unit_kerja === $authUser->id_unit_kerja) {
            return null;
        }

        return new Error('Unauthorized action', 401);
    }

    /**
     * Setting collaborator
     *
     * @param Dokumen $model
     * @param array $data
     *
     * @return mixed
     */
    private function setCollaborator(Dokumen $model, array  $data): mixed
    {
        $permissionCollaborator = $model->permissions()->where('jenis_perizinan', DokumenPerizinan::COLLABORATOR)->first();

        $recentUser = DokumenKolaborator::where('id_dokumen_perizinan', $permissionCollaborator->id)
            ->whereIn('id_user', $data)->pluck('id_user')?->toArray();

        // perizinan user yang tidak ada di payload data
        $excludeUser = DokumenKolaborator::where('id_dokumen_perizinan', $permissionCollaborator->id)
            ->whereNotIn('id_user', $data);

        $newUserPermissions = array_filter($data, function ($item) use ($recentUser) {
            return !in_array($item, $recentUser);
        });

        // Perbaiki index
        $newUserPermissions = array_values($newUserPermissions);

        // Map untuk dimasukkan ke table collaborator
        $newUserPermissions = array_map(function ($item) use ($permissionCollaborator) {
            return [
                'id_dokumen_perizinan' => $permissionCollaborator->id,
                'id_user' => $item,
                'waktu_dibuat' => Carbon::now(),
                'waktu_diubah' => Carbon::now(),
            ];
        }, $newUserPermissions);

        // Hapus excluded user
        $excludeUser->delete();

        // Masukkan user ke tabel collaborator
        return DokumenKolaborator::insert($newUserPermissions);
    }

    /**
     * Setting organization
     *
     * @param Dokumen $model
     * @param array $data
     *
     * @return mixed
     */
    private function setOrganization(Dokumen $model, array  $data): mixed
    {
        $permissionOrganization = $model->permissions()->where('jenis_perizinan', DokumenPerizinan::ORGANIZATION)->first();

        $recentOrganization = DokumenUnit::where('id_dokumen_perizinan', $permissionOrganization->id)
            ->whereIn('id_unit_kerja', $data)->pluck('id_unit_kerja')?->toArray();

        // perizinan organisasi yang tidak ada di payload data
        $excludeOrganization = DokumenUnit::where('id_dokumen_perizinan', $permissionOrganization->id)
            ->whereNotIn('id_unit_kerja', $data);

        $newOrganizationPermissions = array_filter($data, function ($item) use ($recentOrganization) {
            return !in_array($item, $recentOrganization);
        });

        // Perbaiki index
        $newOrganizationPermissions = array_values($newOrganizationPermissions);

        // Map untuk dimasukkan ke table collaborator
        $newOrganizationPermissions = array_map(function ($item) use ($permissionOrganization) {
            return [
                'id_dokumen_perizinan' => $permissionOrganization->id,
                'id_unit_kerja' => $item,
                'waktu_dibuat' => Carbon::now(),
                'waktu_diubah' => Carbon::now(),
            ];
        }, $newOrganizationPermissions);

        // Hapus excluded organisasi
        $excludeOrganization->delete();

        // Masukkan organisasi ke tabel collaborator
        return DokumenUnit::insert($newOrganizationPermissions);
    }

    /**
     * @param Dokumen $model
     * @param array $tags
     *
     * @return void
     */
    private function addTags(Dokumen $model, array $tags): void
    {
        // Tambahkan tag
        foreach ($tags as $tag) {
            $newTag = Tag::firstOrCreate(
                [
                    'nama_tag' => $tag
                ]
            );

            DokumenTag::firstOrCreate(
                [
                    'id_dokumen' => $model->id,
                    'id_tag' => $newTag->id,
                ],
            );
        }

        $excludeTags = Tag::whereNot('nama_tag', $tags)->get();

        // Hapus exclude tag
        foreach ($excludeTags as $excludeTag) {
            $excludeTag->dokumenTag()->where('id_dokumen', $model->id)->delete();
        }
    }


    /**
     * Buat versi dokumen baru.
     *
     * @param int|Dokumen $id
     * @param UploadedFile $file
     * @param string $clientCode
     * @param bool $isReplace
     *
     * @return DokumenVersi|Error
     */
    private function addVersion(
        int|Dokumen $id,
        UploadedFile $file,
        string $clientCode,
        bool $isReplace = false,
        bool $autoExecuteUpload = true,
    ): DokumenVersi|Error {
        if (is_int($id)) {
            $dokumen = Dokumen::findOrFail($id);
        } else {
            $dokumen = $id;
        }
        $folder = Folder::findOrFail($dokumen->id_folder);
        $size = $file->getSize();
        $ext = $file->getClientOriginalExtension();

        // Versi dokumen
        $lastVersion = DokumenVersi::where('id_dokumen', $id)->max('versi');
        $version = isset($lastVersion) ? (int) $lastVersion + 1 : 1;

        // Upload file dulu
        $uploadedUrl = $this->uploadDokumen(
            file: $file,
            clientCode: $clientCode,
            folderPath: $folder->full_folder_path,
            slug: $dokumen->slug,
            version: $version,
            isReplace: $isReplace,
        );

        // Masukkan ke dalam tabel versi dokumen
        $docVersion = DokumenVersi::create([
            'id_dokumen' => $id,
            'versi' => $version,
            'alamat_berkas' => $uploadedUrl,
            'ukuran' => $size,
            'extension' => $ext,
        ]);

        // update informasi dokumen
        $dokumen->update([
            'versi_terbaru' => $docVersion->versi,
            'alamat_versi_terbaru' => $docVersion->alamat_berkas,
            'extension_versi_terbaru' => $ext,
            'ukuran' => $dokumen->size + $size,
        ]);

        // destroy cache ketika ada update
        DokumenCache::destroy($dokumen->slug);

        if ($autoExecuteUpload) {
            $executeUpload = $this->executeUpload();
            if ($executeUpload instanceof Error) {
                return $executeUpload;
            }
        }

        return $docVersion;
    }

    /**
     * Upload dokumen.
     *
     * @param UploadedFile $file
     * @param string $clientCode
     * @param string $moduleCode
     * @param string $folderPath
     * @param string $version
     *
     * @return string
     */
    private function uploadDokumen(
        UploadedFile $file,
        string|null $clientCode,
        string $folderPath,
        string $slug,
        int $version,
        bool $isReplace = false,
    ): string {
        // Definisikan folder storage
        $clientCode = request()?->client['kode_klien'] ?? $clientCode;
        $folder = $clientCode . '/' . $folderPath;

        // Penamaan dokumen
        $originalExtension = $file->getClientOriginalExtension();
        $generatedName = $slug . '-v' . $version . '.' . $originalExtension;

        // push to temporary
        $this->temporary[] = [
            'folder' => $folder,
            'file' => $file,
            'generatedName' => $generatedName,
            'isReplace' => $isReplace,
        ];

        if (Storage::getDefaultDriver() == 's3') {
            return $folder . '/' . $generatedName;
        } else {
            $folder = 'storage/' . $folder . '/';
            return $folder . $generatedName;
        }

        return $generatedName;
    }

    public function executeUpload()
    {
        try {
            foreach ($this->temporary as $temp) {
                $folder = $temp['folder'];
                $file = $temp['file'];
                $generatedName = $temp['generatedName'];

                // Jika isReplace maka hapus file lama
                if (isset($temp['isReplace']) && $temp['isReplace'] == true) {
                    Storage::delete($folder . '/' . $generatedName);
                }

                // Simpan dokumen. jika menggunakan livewire maka gunakan storeAs untuk menyimpan file
                if ($file instanceof TemporaryUploadedFile) {
                    $file->storeAs($folder, $generatedName);
                } else {
                    Storage::putFileAs($folder, $file, $generatedName);
                }

                if (Storage::getDefaultDriver() == 's3') {
                    Storage::setVisibility($folder . '/' . $generatedName, Visibility::PRIVATE);
                }
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }
    }

    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }
}
