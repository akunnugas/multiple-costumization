<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Models\Dokumen;

class Kegiatan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'judul_kegiatan asc';
    const OPTION_COLUMN = 'judul_kegiatan';

    protected $fillable = [
        'judul_kegiatan',
        'id_unit_kerja',
        'ruang_lingkup',
        'nomor_dokumen',
        'nomor_dokumen_mitra',
        'tanggal_mulai_berlaku',
        'tanggal_akhir_berlaku',
        'id_bentuk_kegiatan',
        'id_sasaran_kinerja',
        'id_indikator_sasaran',
        'anggaran',
        'hasil_pelaksanaan',
        'id_dokumen',
        'link_dokumentasi',
        'id_induk_kerjasama',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_induk_kerjasama' => ['required' => true, 'options' => Kerjasama::class, 'variant' => 'search'],
        'judul_kegiatan' => ['required' => true, 'maxlength' => 255, 'control' => 'textarea'],
        'id_unit_kerja' => ['required' => true, 'options' => UnitKerja::class, 'variant' => 'tree', 'parentIdentifier' => 'id_parent'],
        'ruang_lingkup' => ['required' => false, 'control' => 'textarea'],
        'tanggal_mulai_berlaku' => ['type' => 'date', 'defaultTypeDate' => 'd F Y', 'validation' => 'date_format:Y-m-d', 'required' => true, 'column' => '6'],
        'tanggal_akhir_berlaku' => ['type' => 'date', 'defaultTypeDate' => 'd F Y', 'validation' => 'date_format:Y-m-d|after_or_equal:tanggal_mulai_berlaku', 'required' => true, 'column' => '6'],
        'nomor_dokumen' => ['maxlength' => 255, 'required' => false], // sementara di disable dulu 'unique_ci' => true nya
        'nomor_dokumen_mitra' => ['maxlength' => 255],
        'id_dokumen' => ['required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024, 'showSimpleFile' => true, 'accept' => 'application/pdf,.doc,.docx', 'multiple' => true],
        'id_bentuk_kegiatan' => ['required' => true, 'options' => BentukKegiatan::class, 'control' => 'select', 'variant' => 'search'],
        'id_sasaran_kinerja' => ['required' => false, 'options' => SasaranKinerja::class, 'control' => 'select', 'variant' => 'search'],
        'id_indikator_sasaran' => ['required' => false, 'options' => IndikatorSasaran::class, 'control' => 'select', 'variant' => 'search'],
        'anggaran' => ['validation' => 'max:19', 'currency_field' => true],
        'hasil_pelaksanaan' => ['required' => false, 'control' => 'textarea'],
        'link_dokumentasi' => ['required' => false],
    ];

    public function dokumenKegiatan(): HasMany
    {
        return $this->hasMany(DokumenKegiatan::class, 'id_kegiatan', 'id');
    }

    public function indukKerjasama(): BelongsTo
    {
        return $this->belongsTo(Kerjasama::class, 'id_induk_kerjasama');
    }

    public function sasaranKinerja(): BelongsTo
    {
        return $this->belongsTo(SasaranKinerja::class);
    }

    public function pihak_penanggung_jawab(): MorphMany
    {
        return $this->morphMany(PihakPenanggungJawab::class, 'model', 'model', 'model_id', 'id');
    }

    public function pelaksanaKegiatan(): HasMany
    {
        return $this->hasMany(PelaksanaKegiatan::class, 'id_kegiatan');
    }

    protected function anggaran(): Attribute
    {
        return Attribute::make(
            set: fn(string|null $value) => (int) str_replace('.', '', $value)
        );
    }

    public static function getGroupedByBentukAndJenisKegiatan(): array
    {
        $sql = "
            SELECT
                bk.id AS bentuk_kegiatan_id,
                bk.nama_bentuk_kegiatan,
                jk.id AS jenis_kegiatan_id,
                jk.nama_jenis_kegiatan,
                COUNT(kegiatan.id) AS total,
                CONCAT(jk.nama_jenis_kegiatan, '-', bk.nama_bentuk_kegiatan) AS merge
            FROM
                kerjasama.kegiatan
            JOIN
                kerjasama.bentuk_kegiatan AS bk ON kegiatan.id_bentuk_kegiatan = bk.id
            JOIN
                kerjasama.jenis_kegiatan AS jk ON bk.id_jenis_kegiatan = jk.id
            WHERE
                kegiatan.waktu_dihapus IS NULL
                AND bk.waktu_dihapus IS NULL
                AND jk.waktu_dihapus IS NULL
            GROUP BY
                bk.id, jk.id, bk.nama_bentuk_kegiatan, jk.nama_jenis_kegiatan
            ORDER BY
                total DESC
            LIMIT 5
        ";

        $results = DB::select($sql);

        return array_map(function ($item) {
            return [
                'bentuk_kegiatan' => $item->nama_bentuk_kegiatan,
                'jenis_kegiatan' => $item->nama_jenis_kegiatan,
                'merge' => $item->merge,
                'total' => (int)$item->total,
            ];
        }, $results);
    }


    /**
     * count the kegiatan that do not have hasil_pelaksanaan and not deleted
     * @return array 
     */
    public static function countKegiatanHasilPelaksanaan(): array
    {
        $with = self::query()
            ->whereNull('waktu_dihapus')
            ->whereNotNull('hasil_pelaksanaan')
            ->count();

        $without = self::query()
            ->whereNull('waktu_dihapus')
            ->whereNull('hasil_pelaksanaan')
            ->count();

        return [
            'with_hasil_pelaksanaan' => $with,
            'without_hasil_pelaksanaan' => $without,
        ];
    }
}
