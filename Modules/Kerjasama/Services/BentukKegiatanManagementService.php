<?php

namespace Modules\Kerjasama\Services;

use DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Kerjasama\Models\BentukKegiatan;

class BentukKegiatanManagementService
{
    /**
     * @var BentukKegiatan
     */
    protected $model = BentukKegiatan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new BentukKegiatan;
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
        $sql = "SELECT bk.* FROM $table bk join kerjasama.jenis_kegiatan jk on jk.id = bk.id_jenis_kegiatan AND jk.waktu_dihapus IS NULL ";

        $fieldMap = ['id_jenis_kegiatan' => 'jk.nama_jenis_kegiatan'];

        $defaultFilter = "bk.waktu_dihapus IS NULL";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return BentukKegiatan
     */
    public function show(int $id, $with = []): BentukKegiatan
    {
        $model = $this->model->with($with)->findOrFail($id);
        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return BentukKegiatan
     */
    public function store(array $data): BentukKegiatan
    {
        $this->uniqueDependValidation($data);
        $model = $this->model->create($data);

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return BentukKegiatan
     */
    public function update(array $data, int $id): BentukKegiatan|Error
    {  
        [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id, 'mengubah');

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        $model = $this->show($id);
        $this->uniqueDependValidation($data, $id);

        $model->update($data);
        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        // if ($this->checkReference($id)) {
        //     return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        // }

        [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id);

        if ($isDataDefault) {
            return new Error($errorMessage);
        }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
        }

        return true;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        // if ($this->checkReferenceMultiple($ids)) {
        //     return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        // }

        foreach ($ids as $id) {
            [$isDataDefault, $errorMessage] = $this->isianDefaultVaidation($id);

            if ($isDataDefault) {
                return new Error($errorMessage);
            }
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    /**
     * Validasi 2 komponent kolom, jika salah satu beda maka dianggap tidak unik
     * 
     * @param array $data
     * @param string|null $ignoreId
     * 
     * @return mixed
     */
    protected function uniqueDependValidation(array $data, string $ignoreId = null)
    {
        $columnValidate = [
            'nama_bentuk_kegiatan',
            'id_jenis_kegiatan'
        ];
        $errorMessages = [];
        $query = DB::table('kerjasama.bentuk_kegiatan');

        foreach ($columnValidate as $column) {
            $errorMessages[$column] = __('validation.unique', ['attribute' => __("kerjasama::bentuk_kegiatan.$column")]);
            $query->whereRaw("LOWER({$column}::varchar) = ?::varchar", [strtolower($data[$column])]);
        }

        if (!empty($ignoreId)) {
            $query->where('id', '<>', $ignoreId);
        }

        $query->whereNull('waktu_dihapus');

        $isDuplicate = $query->exists();

        if (!$isDuplicate) {
            return;
        }

        throw ValidationException::withMessages($errorMessages);
    }

    protected function isianDefaultVaidation(int $id, string $actionLabel = "menghapus"): array
    {
        $data = $this->model
            ->where('id', $id)
            ->first();

        if (!$data->isian_default) {
            return [false, ""];
        }

        return [true, "Gagal $actionLabel Data '".$data->nama_bentuk_kegiatan."' karena merupakan data default"];
    }

    // protected function checkReference($id)
    // {
    //     $isReferencePengisianPanduan = PengisianPanduan::where('id_jenis_standar', $id)->exists();
    //     $isReferencePenilaianPanduan = PenilaianPanduan::where('id_jenis_standar', $id)->exists();

    //     return $isReferencePengisianPanduan || $isReferencePenilaianPanduan;
    // }

    // protected function checkReferenceMultiple($ids)
    // {
    //     $isReferencePengisianPanduan = PengisianPanduan::whereIn('id_jenis_standar', $ids)->exists();
    //     $isReferencePenilaianPanduan = PenilaianPanduan::whereIn('id_jenis_standar', $ids)->exists();

    //     return $isReferencePengisianPanduan || $isReferencePenilaianPanduan;
    // }
}
