<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;

class Mahasiswa extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.mahasiswa';

    protected $fillable = ['nim', 'nama_mahasiswa', 'id_biodata', 'ref_key_siakad', 'id_unit'];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_biodata' => ['required' => true, 'options' => Biodata::class],
        'nim' => ['required' => true, 'maxlength' => '24', 'unique' => true],
        'nama_mahasiswa' => ['required' => true, 'maxlength' => 100],
        'ref_key_siakad' => ['maxlength' => 255],
        'id_unit' => ['required' => true, 'options' => UnitKerja::class],
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_mahasiswa asc';
    const OPTION_COLUMN = 'nama_mahasiswa';

    public static function optionWithPersons(
        bool $isDefaultOpt = false,
        string $order = self::OPTION_ORDER,
        array $exceptIdBiodatas = []
    ) {
        $sql = "SELECT b.id, b.id_user, m.nim, b.nama
                FROM core.biodata b
                JOIN core.mahasiswa m ON m.id_biodata = b.id AND m.waktu_dihapus IS NULL
                WHERE b.waktu_dihapus IS NULL
                ORDER BY m." . $order;

        $data = DB::select($sql);
        if ($isDefaultOpt) {
            $optDefault = [];
            foreach ($data as $value) {
                if (in_array($value->id, $exceptIdBiodatas)) {
                    continue;
                }

                $optDefault[$value->id] = $value->nim . ' - ' . $value->nama;
            }
            return $optDefault;
        }

        return (array) $data;
    }

    public static function searchOption($searchTerm = '', $idProdi = null, $limit = 0, $exceptIdBiodatas = [])
    {
        $query = self::select('mahasiswa.id_biodata', 'mahasiswa.nim', 'mahasiswa.nama_mahasiswa');
        if ($searchTerm !== '') {
            $query->where('mahasiswa.waktu_dihapus', null)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('mahasiswa.nim', 'like', "%$searchTerm%")
                        ->orWhere('mahasiswa.nama_mahasiswa', 'like', "%$searchTerm%");
                });
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        if ($idProdi !== null) {
            $query->where('mahasiswa.id_unit', $idProdi);
        }

        if (!empty($exceptIdBiodatas)) {
            $query->whereNotIn('mahasiswa.id_biodata', $exceptIdBiodatas);
        }

        $data = $query->get();

        $mapping = [];
        foreach ($data as $value) {
            $mapping[$value->id_biodata] = $value->nim . ' - ' . $value->nama_mahasiswa;
            // $mapping[] = [
            //     'value' => $value->id_biodata,
            //     'label' => $value->nim . ' - ' . $value->nama_mahasiswa,
            // ];
        }

        return $mapping;
    }

    public static function searchOptionV1($searchTerm = '', $idunit = null, $limit = 0, $exceptNimMhs = [])
    {
        $query = "select m.nim, m.nama, m.idunit as kode_unit from akademik.ak_mahasiswa m where m.idstatusmhs = 'A'";
        
        if (!empty($idunit)) {
            $query .= "and m.idunit = '$idunit'";
        }

        if ($searchTerm !== '') {
            $query .= " and (m.nim ilike '%$searchTerm%' or m.nama ilike '%$searchTerm%')";
        }

        if (!empty($exceptNimMhs)) {
            $query .= " and m.nim not in ('" . implode("','", $exceptNimMhs) . "')";
        }

        if ($limit > 0) {
            $query .= " limit $limit";
        }

        $siakadV1Connection = DB::connection('siakadv1');
        $result = $siakadV1Connection->select($query);
        $result = json_decode(json_encode($result), true);

        $data = [];
        foreach ($result as $value) {
            $data[$value['nim']] = $value['nim'] . ' - ' . $value['nama'];
        }

        return $data;
    }
}
