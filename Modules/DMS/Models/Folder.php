<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\TreeStructure;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\User;

class Folder extends IndonesianModel
{
    use HasFactory, TreeStructure, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.folder';

    protected $fillable = [
        'id_parent',
        'id_pemilik',
        'kode_folder',
        'nama_folder',
        'apakah_hanya_lihat',
        'id_unit_kerja',
        'info_left',
        'info_right',
        'info_level',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_parent' => ['required' => false, 'options' => Folder::class],
        'id_pemilik' => ['required' => false, 'options' => User::class],
        'kode_folder' => ['required' => true, 'maxlength' => 50],
        'nama_folder' => ['required' => true, 'maxlength' => 100],
        'apakah_hanya_lihat' => ['required' => true, 'type' => 'boolean'],
        'id_unit_kerja' => ['required' => false, 'options' => UnitKerja::class],
        'info_left' => ['required' => false, 'type' => 'integer'],
        'info_right' => ['required' => false, 'type' => 'integer'],
        'info_level' => ['required' => false, 'type' => 'integer'],
    ];

    protected static function defineDepthField()
    {
        return 'info_level';
    }

    protected static function newFactory()
    {
        return \Modules\DMS\Database\factories\FolderFactory::new();
    }

    public function allParentFolders($hideDMS = false)
    {
        $currentFolder = $this;
        $parentFolders = collect();

        while (!is_null($currentFolder->id_parent)) {
            $currentFolder = Folder::find($currentFolder->id_parent);

            if ($hideDMS && $currentFolder->kode_folder === Modul::CODE_DMS) {
                continue;
            }

            $parentFolders->push($currentFolder);
        }

        return $parentFolders->reverse();
    }

    public function fullFolderPath(): Attribute
    {
        return Attribute::make(
            get: function () {
                $parents = $this->allParentFolders()->toArray();

                $parents = array_map(function ($parent) {
                    return $parent['kode_folder'];
                }, $parents);

                $parentPath = implode('/', $parents) . '/' . $this->kode_folder;
                return $parentPath;
            }
        );
    }

    public function fullFolderPathArray($hideDMS = false)
    {
        $parents = $this->allParentFolders($hideDMS)->toArray();

        $parents = array_map(function ($parent) {
            return [
                'nama_folder' => $parent['nama_folder'],
                'kode_folder' => $parent['kode_folder'],
            ];
        }, $parents);

        return $parents;
    }

    public function dokumenCount(): Attribute
    {
        return Attribute::make(
            get: function () {
                return DB::table('dms.dokumen')
                    ->whereRaw(
                        "id_folder IN (SELECT id FROM dms.folder WHERE info_left >= ? AND info_right < ?) AND waktu_dihapus IS NULL",
                        [$this->info_left, $this->info_right]
                    )
                    ->count();
            }
        );
    }
}
