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

class Kerjasama extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'judul_kerjasama asc';
    const OPTION_COLUMN = 'judul_kerjasama';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.kerjasama';

    protected $fillable = [
        'judul_kerjasama',
        'id_mitra',
        'id_unit_kerja',
        'deskripsi',
        'id_jenis_dokumen',
        'nomor_dokumen',
        'tanggal_mulai_berlaku',
        'tanggal_akhir_berlaku',
        'id_status_kerjasama',
        'id_dokumen',
        'id_bentuk_kegiatan',
        'id_sumber_dana',
        'id_jenis_kegiatan',
        'anggaran',
        'nomor_dokumen_mitra',
        'hasil_pelaksanaan',
        'link_dokumentasi',
        'id_parent',
        'id_sasaran_kinerja',
        'id_indikator_sasaran'
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_mitra' => ['required' => true, 'options' => Mitra::class, 'variant' => 'search'],
        'judul_kerjasama' => ['required' => true, 'maxlength' => 255, 'control' => 'textarea'],
        'id_unit_kerja' => ['required' => true, 'options' => UnitKerja::class, 'variant' => 'tree', 'parentIdentifier' => 'id_parent'],
        'deskripsi' => ['required' => true, 'control' => 'textarea'],
        'tanggal_mulai_berlaku' => ['type' => 'date', 'defaultTypeDate' => 'd F Y', 'validation' => 'date_format:Y-m-d', 'required' => true, 'column' => '6'],
        'tanggal_akhir_berlaku' => ['type' => 'date', 'defaultTypeDate' => 'd F Y', 'validation' => 'date_format:Y-m-d|after_or_equal:tanggal_mulai_berlaku', 'required' => true, 'column' => '6'],
        'id_jenis_dokumen' => ['required' => true, 'options' => JenisDokumen::class, 'variant' => 'search'],
        'nomor_dokumen' => ['maxlength' => 255, 'required' => true], // sementara di disable untuk unique nya.
        'nomor_dokumen_mitra' => ['maxlength' => 255],
        'id_status_kerjasama' => ['required' => true, 'options' => StatusKerjasama::class, 'variant' => 'search'],
        'id_dokumen' => ['required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024, 'showSimpleFile' => true, 'accept' => 'application/pdf,.doc,.docx', 'multiple' => true],
        'id_sumber_dana' => ['options' => SumberDana::class, 'variant' => 'search'],
        'anggaran' => ['validation' => 'max:19', 'currency_field' => true],
        'hasil_pelaksanaan' => ['required' => false, 'control' => 'textarea'],
    ];

    public function dokumenKerjasama(): HasMany
    {
        return $this->hasMany(DokumenKerjasama::class, 'id_kerjasama', 'id');
    }

    public function pihak_penanggung_jawab(): MorphMany
    {
        return $this->morphMany(PihakPenanggungJawab::class, 'model', 'model', 'model_id', 'id');
    }

    public function status_kerjasama(): BelongsTo
    {
        return $this->belongsTo(StatusKerjasama::class);
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }
    public function bentukKegiatan()
    {
        return $this->belongsTo(BentukKegiatan::class, 'id_bentuk_kegiatan');
    }
    public function jenisKegiatan()
    {
        return $this->belongsTo(JenisKegiatan::class, 'id_jenis_kegiatan');
    }
    public static function getFilterExpiredOptions(): array
    {
        $format = 'Y-m-d';
        $status = StatusKerjasama::KADALUWARSA;
        return [
            '' => '-- Semua Filter Kadaluwarsa --',
            now()->addWeek()->format($format) => $status . ' dalam 1 minggu',
            now()->addMonth()->format($format) => $status . ' dalam 1 bulan',
            now()->addMonths(3)->format($format) => $status . ' dalam 3 bulan',
            now()->addMonths(6)->format($format) => $status . ' dalam 6 bulan',
            now()->addYear()->format($format) => $status . ' dalam 1 tahun',
        ];
    }

    public static function getGroupedByUnitKerja(): array
    {
        return self::query()
            ->whereNull('kerjasama.kerjasama.waktu_dihapus')
            ->join('core.unit_kerja as uk', 'kerjasama.kerjasama.id_unit_kerja', '=', 'uk.id')
            ->whereNull('uk.waktu_dihapus')
            ->groupBy('uk.id', 'uk.nama_unit')
            ->select(
                'uk.id as unit_kerja_id',
                'uk.nama_unit',
                DB::raw('count(kerjasama.kerjasama.id) as total_kerjasama')
            )
            ->orderBy('total_kerjasama', 'desc')
            ->take(5)
            ->get()
            ->toArray();
    }

    protected function anggaran(): Attribute
    {
        return Attribute::make(
            set: fn(string|null $value) => (int) str_replace('.', '', $value)
        );
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
