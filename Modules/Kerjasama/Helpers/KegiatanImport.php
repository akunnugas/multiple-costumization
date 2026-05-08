<?php

namespace Modules\Kerjasama\Helpers;

use Illuminate\Support\Facades\Log;
use Modules\Kerjasama\Helpers\ImportToModel;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Core\Models\UnitKerja;
use Carbon\Carbon;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Services\PenanggungJawabManagementService;
use Modules\Kerjasama\Services\PihakPenanggungJawabManagementService;

class KegiatanImport extends ImportToModel
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Kegiatan::class,
            fieldMapping: [
                'Judul Kegiatan' => 'judul_kegiatan',
                'Judul Kerjasama' => 'id_induk_kerjasama',
                'Bentuk Kegiatan' => 'id_bentuk_kegiatan',
                'Tanggal Awal Berlaku DD/MM/YYYY' => 'tanggal_mulai_berlaku',
                'Tanggal Akhir Berlaku DD/MM/YYYY' => 'tanggal_akhir_berlaku',
                'Nama Pihak 1 (PT)' => 'nama_pihak_1',
                'Jabatan Pihak 1 (PT)' => 'jabatan_pihak_1',
                'Email Pihak 1 (PT)' => 'email_pihak_1',
                'No Hp Pihak 1 (PT)' => 'no_hp_pihak_1',
                'Nama Pihak 2 (Mitra)' => 'nama_pihak_2',
                'Jabatan Pihak 2 (Mitra)' => 'jabatan_pihak_2',
                'Email Pihak 2 (Mitra)' => 'email_pihak_2',
                'No Hp Pihak 2 (Mitra)' => 'no_hp_pihak_2',
            ],
            transformers: [
                'id_bentuk_kegiatan' => [$this, 'transformBentukKegiatan'],
                'id_induk_kerjasama' => [$this, 'transformKerjasama'],
                'tanggal_mulai_berlaku' => [$this, 'parseDate'],
                'tanggal_akhir_berlaku' => [$this, 'parseDate'],
            ],

            validators: [
                'jabatan_pihak_1' => fn($value) => empty($value) || strlen($value) <= 100,
                'email_pihak_1' => fn($value) => empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL),
                'jabatan_pihak_2' => fn($value) => empty($value) || strlen($value) <= 255,
                'email_pihak_2' => fn($value) => empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL),
                'nama_pihak_1' => fn($value) => !empty($value) && strlen($value) <= 255,
                'nama_pihak_2' => fn($value) => !empty($value) && strlen($value) <= 255,
                'bentuk_kegiatan' => fn($value) => !empty($value),
                'judul_kegiatan' => fn($value) => !empty($value) && strlen($value) <= 255,
                'id_induk_kerjasama' => fn($value) => !empty($value),
                'tanggal_mulai_berlaku' => fn($value) => !empty($value),
                'tanggal_akhir_berlaku' => fn($value) => !empty($value),
                'no_hp_pihak_2' => fn($value) => empty($value) || strlen($value) <= 20,
                'no_hp_pihak_1' => fn($value) => empty($value) || strlen($value) <= 20,
                // nama pihak_1 and nama_pihak_2 are required
                
            ],

            beforeCreate: [$this, 'beforeCreateKegiatan'],
            afterCreate: [$this, 'afterCreateKegiatan'],
            afterAll: [$this, 'afterAllKegiatan']
        );
    }

    /**
     * Transform bentuk kegiatan string 
     * 
     * @param string $value Input like "Pengabdian - Pengembangan Sistem / Produk"
     * @return int 
     * @throws \Exception
     */
    public function transformBentukKegiatan($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        $parts = explode(' - ', $value);
        $bentukKegiatanName = trim($parts[1]);
        if (empty($bentukKegiatanName)) {
            throw new \InvalidArgumentException("Nama Bentuk Kegiatan tidak boleh kosong");
        }

        $bentukKegiatan = BentukKegiatan::where('nama_bentuk_kegiatan', 'ILIKE', $bentukKegiatanName)
            ->first();

        if (!$bentukKegiatan) {
            throw new \RuntimeException("Bentuk Kegiatan '{$bentukKegiatanName}' tidak ditemukan");
        }

        return $bentukKegiatan->id;
    }

    public function transformKerjasama($value)
    {
        if (empty($value)) {
            return null;
        }
        $parts = explode(' - ', $value);
        $kerjasamaId = trim($parts[0]);
        $kerjasama = Kerjasama::find($kerjasamaId);

        if (!$kerjasama) {
            throw new \Exception("Kerjasama dengan ID '{$kerjasamaId}' tidak ditemukan di database");
        }
        return $kerjasama->id;
    }

    public function beforeCreateKegiatan(array $data, array $originalRow): array
    {
        if (empty($data['judul_kegiatan'])) {
            return [];
        }

        Log::info('Processing Kegiatan row', compact('data', 'originalRow'));

        $existing = Kegiatan::where('judul_kegiatan', $data['judul_kegiatan'])
            ->where('id_induk_kerjasama', '=', $data['id_induk_kerjasama'])
            ->where('id_bentuk_kegiatan', '=', $data['id_bentuk_kegiatan'])
            ->first();

        if ($existing) {
            $data['__existing_model'] = $existing;
            $this->duplicateCount++;
            return []; // skip this row
        }

        if (isset($data['__existing_model'])) {
            $data['waktu_diperbarui'] = now();
            $data['diperbarui_oleh'] = auth()->id() ?? 1;
        } else {
            $data['waktu_dibuat'] = now();
            $data['dibuat_oleh'] = auth()->id() ?? 1;
        }
        if (!empty($data['tanggal_mulai_berlaku']) && !empty($data['tanggal_akhir_berlaku'])) {
            $mulai = Carbon::parse($data['tanggal_mulai_berlaku']);
            $akhir = Carbon::parse($data['tanggal_akhir_berlaku']);
            if ($akhir->lessThanOrEqualTo($mulai)) {
                return []; // skip row
            }
        }
        $client = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        $data['id_unit_kerja'] = $client->id;
        return $data;
    }

    public function afterCreateKegiatan($model, array $data, array $originalRow): void
    {

        $status = isset($data['__existing_model']) ? 'updated' : 'created';
        Log::info("Kegiatan {$status}", ['id' => $model->id, 'judul' => $model->judul_kegiatan]);

        if (!empty($data['nama_pihak_1']) && !empty($data['nama_pihak_2'])) {
            $pihakService = new PihakPenanggungJawabManagementService();
            $penanggungJawabService = new PenanggungJawabManagementService();

            $client = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();

            $kerjasama = Kerjasama::find($model->id_induk_kerjasama);
            $id1 = $pihakService->firstOrCreate([
                'model' => Kegiatan::class,
                'model_id' => $model->id,
                'id_pihak' => $client->id,
                'pihak_ke' => 1,
                'model_pihak' => UnitKerja::class,

            ], [
                'waktu_dibuat' => now(),
                'dibuat_oleh' => auth()->id()
            ]);
            $id2 = $pihakService->firstOrCreate([
                'model' => Kegiatan::class,
                'model_id' => $model->id,
                'id_pihak' => $kerjasama->id_mitra,
                'pihak_ke' => 2,
                'model_pihak' => Mitra::class,
            ], [
                'waktu_dibuat' => now(),
                'dibuat_oleh' => auth()->id(),
            ]);

            $penanggungJawabService->firstOrCreate(
                [
                    'nama_penanggung_jawab' => $data['nama_pihak_1'],
                    'telepon' => $data['no_hp_pihak_1'] ?? null,
                    'email' => $data['email_pihak_1'] ?? null,
                    'jabatan' => $data['jabatan_pihak_1'] ?? null,
                    'id_pihak_penanggung_jawab' => $id1->id
                ],
                [
                    'nama_penanggung_jawab' => $data['nama_pihak_1'],
                    'telepon' => $data['no_hp_pihak_1'] ?? null,
                    'email' => $data['email_pihak_1'] ?? null,
                    'jabatan' => $data['jabatan_pihak_1'] ?? null,
                    'waktu_dibuat' => now(),
                    'dibuat_oleh' => auth()->id(),
                    'id_pihak_penanggung_jawab' => $id1->id
                ]
            );

            $penanggungJawabService->firstOrCreate(
                [
                    'nama_penanggung_jawab' => $data['nama_pihak_2'],
                    'telepon' => $data['no_hp_pihak_2'] ?? null,
                    'email' => $data['email_pihak_2'] ?? null,
                    'jabatan' => $data['jabatan_pihak_2'] ?? null,
                    'dibuat_oleh' => auth()->id(),
                    'id_pihak_penanggung_jawab' => $id2->id
                ],
                [
                    'nama_penanggung_jawab' => $data['nama_pihak_2'],
                    'telepon' => $data['no_hp_pihak_2'] ?? null,
                    'email' => $data['email_pihak_2'] ?? null,
                    'jabatan' => $data['jabatan_pihak_2'] ?? null,
                    'waktu_dibuat' => now(),
                    'dibuat_oleh' => auth()->id(),
                    'id_pihak_penanggung_jawab' => $id2->id
                ]
            );
        }
    }

    public static function parseDate($dateString, $format = 'Y-m-d')
    {
        if (empty($dateString)) {
            return null;
        }

        if (is_numeric($dateString)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString)->format($format);
        }

        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', trim($dateString))) {
                return Carbon::createFromFormat('d/m/Y', trim($dateString))->format($format);
            }
            return Carbon::parse($dateString)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
    public function afterAllKegiatan($results)
    {
        if (is_array($results) && isset($results['summary'])) {
            $results['summary']['duplicate_count'] = $this->duplicateCount;
        }
        return $results;
    }
}
