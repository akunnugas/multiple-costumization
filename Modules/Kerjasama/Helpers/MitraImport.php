<?php

namespace Modules\Kerjasama\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Core\Models\UnitKerja;
use Modules\Kerjasama\Helpers\ImportToModel;
use Modules\Kerjasama\Models\Mitra;
use Modules\Core\Models\Wilayah;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\StatusKerjasama;
use Modules\Kerjasama\Services\JenisDokumenManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Modules\Kerjasama\Services\KriteriaMitraManagementService;
use Modules\Kerjasama\Services\PenanggungJawabManagementService;
use Modules\Kerjasama\Services\PihakPenanggungJawabManagementService;

class MitraImport extends ImportToModel
{

    public function __construct()
    {
        parent::__construct(
            modelClass: Mitra::class,
            fieldMapping: [
                'Nama Mitra' => 'nama_mitra',
                'Jenis Mitra' => 'jenis_mitra',
                'Tingkat Mitra' => 'tingkat_mitra',
                'Kriteria Mitra' => 'kriteria_mitra_name',
                'Provinsi' => 'provinsi_name',
                'Judul Kerjasama' => 'judul_kerjasama',
                'Nomor Dokumen Kerjasama' => 'nomor_dokumen',
                'Tanggal Awal Berlaku DD/MM/YYYY' => 'tanggal_mulai_berlaku',
                'Tanggal Akhir Berlaku DD/MM/YYYY' => 'tanggal_akhir_berlaku',
                'Nama Penanggung Jawab Pihak 1 (PT)' => 'nama_pihak_1',
                'Jabatan Pihak 1 (PT)' => 'jabatan_pihak_1',
                'Email Pihak 1 (PT)' => 'email_pihak_1',
                'No Hp Pihak 1 (PT)' => 'no_hp_pihak_1',
                'Nama Penanggung Jawab Pihak 2 (Mitra)' => 'nama_pihak_2',
                'Jabatan Pihak 2 (Mitra)' => 'jabatan_pihak_2',
                'Email Pihak 2 (Mitra)' => 'email_pihak_2',
                'No Hp Pihak 2 (Mitra)' => 'no_hp_pihak_2',
                'Jenis Dokumen' => 'jenis_dokumen'
            ],
            transformers: [
                'nama_mitra' => fn($value) => trim($value),
                'jenis_mitra' => fn($value) => $this->determineJenisMitra($value),
                'tingkat_mitra' => fn($value) => $this->determineTingkatMitra($value),
                'tanggal_mulai_berlaku' => fn($value) => ImportToModel::parseDate($value),
                'tanggal_akhir_berlaku' => fn($value) => ImportToModel::parseDate($value),
                'nomor_dokumen' => fn($value) => is_numeric($value) ? (string)$value : (is_string($value) ? trim($value) : '-'),
                'nama_pihak_1' => function ($value, $row = []) {
                    $v1 = $row['Nama Penanggung Jawab Pihak 1 (PT)'] ?? null;
                    $v2 = $row['Nama Pihak 1 (PT)'] ?? null;
                    $final = $v1 ?: $v2 ?: $value;
                    return trim((string)$final);
                },
                'nama_pihak_2' => function ($value, $row = []) {
                    $v1 = $row['Nama Penanggung Jawab Pihak 2 (Mitra)'] ?? null;
                    $v2 = $row['Nama Pihak 2 (Mitra)'] ?? null;
                    $final = $v1 ?: $v2 ?: $value;
                    return trim((string)$final);
                },
            ],
            validators: [
                'nama_mitra' => fn($value) => !empty($value) && strlen($value) <= 255,
                'jenis_mitra' => fn($value) => in_array($value, [Mitra::MITRA_PERGURUAN_TINGGI, Mitra::MITRA_NON_PERGURUAN_TINGGI]),
                'tingkat_mitra' => fn($value) => in_array($value, array_keys(Mitra::LEVELS)),
                'kriteria_mitra_name' => fn($value) => !empty($value),
                'provinsi_name' => fn($value) => !empty($value),
                'judul_kerjasama' => fn($value) => !empty($value) && strlen($value) <= 255,
                'deskripsi' => fn($value) => strlen($value ?? '') <= 1000,
                'nomor_dokumen' => fn($value) => !empty($value) && strlen($value) <= 100,
                'jabatan_pihak_1' => fn($value) => empty($value) || strlen($value) <= 100,
                'email_pihak_1' => fn($value) => empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL),
                'jabatan_pihak_2' => fn($value) => empty($value) || strlen($value) <= 255,
                'email_pihak_2' => fn($value) => empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL),
                'jenis_dokumen' => fn($value) => !empty($value) && strlen($value) <= 255,
                'tanggal_mulai_berlaku' => function ($value) {
                    if (empty($value)) return false;
                    try {
                        $dt = Carbon::createFromFormat('Y-m-d', $value);
                        return $dt && $dt->format('Y-m-d') === $value;
                    } catch (\Exception $e) {
                        return false;
                    }
                },
                'tanggal_akhir_berlaku' => function ($value) {
                    if (empty($value)) return false;
                    try {
                        $dt = Carbon::createFromFormat('Y-m-d', $value);
                        return $dt && $dt->format('Y-m-d') === $value;
                    } catch (\Exception $e) {
                        return false;
                    }
                },
                'nama_pihak_1' => fn($value) => !empty($value) && strlen($value) <= 255,
                'nama_pihak_2' => fn($value) => !empty($value) && strlen($value) <= 255,
            ],
            beforeCreate: [$this, 'beforeCreateMitra'],
            afterCreate: [$this, 'afterCreateMitra'],
            afterAll: [$this, 'afterAllIMitra']
        );
    }


    public function beforeCreateMitra(array $data): array
    {


        if (empty($data['nama_mitra'])) {
            Log::info('Skipping row due to empty nama_mitra');
            return [];
        }

        $existingMitra = Mitra::where('nama_mitra', trim($data['nama_mitra']))->first();



        if (!empty($data['tanggal_mulai_berlaku']) && !empty($data['tanggal_akhir_berlaku'])) {
            try {
                $mulai = Carbon::createFromFormat('Y-m-d', $data['tanggal_mulai_berlaku']);
                $akhir = Carbon::createFromFormat('Y-m-d', $data['tanggal_akhir_berlaku']);
                if ($akhir->lessThanOrEqualTo($mulai)) {
                    Log::error('Tanggal akhir harus lebih besar dari tanggal mulai', [
                        'mulai' => $data['tanggal_mulai_berlaku'],
                        'akhir' => $data['tanggal_akhir_berlaku']
                    ]);
                    return [];
                }
            } catch (\Exception $e) {
                Log::error('Format tanggal tidak valid', [
                    'mulai' => $data['tanggal_mulai_berlaku'] ?? null,
                    'akhir' => $data['tanggal_akhir_berlaku'] ?? null,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }
        if ($existingMitra) {
            $existingKerjasama = Kerjasama::where('judul_kerjasama', $data['judul_kerjasama'])
                ->where('id_mitra', $existingMitra->id)
                ->first();

            if ($existingKerjasama) {
                $this->duplicateCount++;
                return [];
            }

            $data['__existing_model'] = $existingMitra;
        }

        if (!empty($data['kriteria_mitra_name'])) {
            $kriteriaMitra = (new KriteriaMitraManagementService())
                ->firstOrCreate($data['kriteria_mitra_name']);
            $data['id_kriteria_mitra'] = $kriteriaMitra->id;
        }
        unset($data['kriteria_mitra_name']);

        if (!empty($data['provinsi_name'])) {
            $provinsiName = trim($data['provinsi_name']);
            $provinsi = Wilayah::where('nama_wilayah', 'ILIKE', '%' . $provinsiName . '%')
                ->where('level_wilayah', '1')
                ->first();
            $data['id_provinsi'] = $provinsi ? $provinsi->id : null;
        }
        unset($data['provinsi_name']);

        $kerjasamaFields = [
            'judul_kerjasama',
            'deskripsi',
            'nomor_dokumen',
            'tanggal_mulai_berlaku',
            'tanggal_akhir_berlaku',
            'nama_pihak_1',
            'jabatan_pihak_1',
            'email_pihak_1',
            'no_hp_pihak_1',
            'nama_pihak_2',
            'jabatan_pihak_2',
            'email_pihak_2',
            'no_hp_pihak_2',
            'jenis_dokumen'
        ];

        $validators = [
            'nama_pihak_1' => fn($v) => !empty($v) && strlen($v) <= 255,
            'nama_pihak_2' => fn($v) => !empty($v) && strlen($v) <= 255,
            'jabatan_pihak_1' => fn($v) => empty($v) || strlen($v) <= 100,
            'email_pihak_1' => fn($v) => empty($v) || filter_var($v, FILTER_VALIDATE_EMAIL),
            'no_hp_pihak_1' => fn($v) => empty($v) && (is_string($v) || is_numeric($v)) && strlen((string)$v) <= 20,
            'jabatan_pihak_2' => fn($v) => empty($v) || strlen($v) <= 255,
            'email_pihak_2' => fn($v) => empty($v) || filter_var($v, FILTER_VALIDATE_EMAIL),
            'no_hp_pihak_2' => fn($v) => empty($v) && (is_string($v) || is_numeric($v)) && strlen((string)$v) <= 20,
        ];

        foreach ($validators as $field => $validator) {
            if (!isset($data[$field]) || !$validator($data[$field])) {
                $data[$field] = '';
            }
        }

        foreach ($kerjasamaFields as $field) {
            unset($data[$field]);
        }

        if (isset($data['__existing_model'])) {
            $data['waktu_diubah'] = now();
            $data['diubah_oleh'] = auth()->id() ?? 1;
        } else {
            $data['waktu_dibuat'] = now();
            $data['dibuat_oleh'] = auth()->id() ?? 1;
        }

        return $data;
    }

    public function afterCreateMitra($model, array $data, array $originalRow): void
    {
        if (!$model) {
            return;
        }


        $originalRow['Nama Pihak 1 (PT)'] = $originalRow['Nama Pihak 1 (PT)'] ?? $originalRow['Nama Penanggung Jawab Pihak 1 (PT)'] ?? '';
        $originalRow['Nama Pihak 2 (Mitra)'] = $originalRow['Nama Pihak 2 (Mitra)'] ?? $originalRow['Nama Penanggung Jawab Pihak 2 (Mitra)'] ?? '';



        $kerjasamaData = [
            'judul_kerjasama' => $originalRow['Judul Kerjasama'] ?? null,
            'deskripsi' => $originalRow['Deskripsi Kerjasama'] ?? '-',
            'nomor_dokumen' => $originalRow['Nomor Dokumen Kerjasama'] ?? null,
            'tanggal_mulai_berlaku' => $originalRow['Tanggal Awal Berlaku DD/MM/YYYY'] ?? null,
            'tanggal_akhir_berlaku' => $originalRow['Tanggal Akhir Berlaku DD/MM/YYYY'] ?? null,
            'nama_pihak_1' => $originalRow['Nama Pihak 1 (PT)'] ?? null,
            'jabatan_pihak_1' => $originalRow['Jabatan Pihak 1 (PT)'] ?? null,
            'email_pihak_1' => $originalRow['Email Pihak 1 (PT)'] ?? null,
            'no_hp_pihak_1' => $originalRow['No Hp Pihak 1 (PT)'] ?? null,
            'nama_pihak_2' => $originalRow['Nama Pihak 2 (Mitra)'] ?? null,
            'jabatan_pihak_2' => $originalRow['Jabatan Pihak 2 (Mitra)'] ?? null,
            'email_pihak_2' => $originalRow['Email Pihak 2 (Mitra)'] ?? null,
            'no_hp_pihak_2' => $originalRow['No Hp Pihak 2 (Mitra)'] ?? null,
            'jenis_dokumen' => $originalRow['Jenis Dokumen'] ?? null,
        ];

        $existingKerjasama = Kerjasama::where('judul_kerjasama', $kerjasamaData['judul_kerjasama'] ?? null)
            ->where('id_mitra', $model->id)
            ->first();

        Log::info('Existing kerjasama check', [
            'judul_kerjasama' => $kerjasamaData['judul_kerjasama'] ?? null,
            'id_mitra' => $model->id,
            'existingKerjasama' => $existingKerjasama ? $existingKerjasama->id : null
        ]);

        if (empty($existingKerjasama)) {
            $kerjasamaService = new KerjasamaManagementService();
            $jenisDokumenService = new JenisDokumenManagementService();

            $client = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();

            $dateStart = !empty($kerjasamaData['tanggal_mulai_berlaku'])
                ? ImportToModel::parseDate($kerjasamaData['tanggal_mulai_berlaku'])
                : null;

            $dateEnd = !empty($kerjasamaData['tanggal_akhir_berlaku'])
                ? ImportToModel::parseDate($kerjasamaData['tanggal_akhir_berlaku'])
                : null;

            $jenisDokumenId = null;
            if (!empty($kerjasamaData['jenis_dokumen'])) {
                $jenisDokumen = $jenisDokumenService->firstOrCreate(trim($kerjasamaData['jenis_dokumen']));
                if ($jenisDokumen && isset($jenisDokumen->id)) {
                    $jenisDokumenId = $jenisDokumen->id;
                }
            }

            $statusKerjasama = StatusKerjasama::where('status_kerjasama', '=', 'Aktif')->first();

            $kerjasamaCreateData = [
                'judul_kerjasama' => $kerjasamaData['judul_kerjasama'] ?? null,
                'id_mitra' => $model->id,
                'id_unit_kerja' => $client->id,
                'id_jenis_dokumen' => $jenisDokumenId,
                'id_status_kerjasama' => $statusKerjasama->id,
                'deskripsi' => $kerjasamaData['deskripsi'] ?? '-',
                'nomor_dokumen' => $kerjasamaData['nomor_dokumen'] ?? null,
                'tanggal_mulai_berlaku' => $dateStart,
                'tanggal_akhir_berlaku' => $dateEnd,
                'waktu_dibuat' => now(),
                'dibuat_oleh' => auth()->id(),
            ];

            $kerjasama = $kerjasamaService->storeImport($kerjasamaCreateData);

            if (!empty($kerjasamaData['nama_pihak_1']) && !empty($kerjasamaData['nama_pihak_2'])) {
                $pihakService = new PihakPenanggungJawabManagementService();
                $penanggungJawabService = new PenanggungJawabManagementService();

                $pihak1 = $pihakService->firstOrCreate([
                    'model' => Kerjasama::class,
                    'model_id' => (int)$kerjasama->id,
                    'id_pihak' => (int)$client->id,
                    'pihak_ke' => 1,
                    'model_pihak' => UnitKerja::class,
                ], [
                    'waktu_dibuat' => now(),
                    'dibuat_oleh' => auth()->id()
                ]);

                $penanggungJawabService->updateOrCreate(
                    [
                        'nama_penanggung_jawab' => $kerjasamaData['nama_pihak_1'],
                        'telepon' => $kerjasamaData['no_hp_pihak_1'] ?? null,
                        'email' => $kerjasamaData['email_pihak_1'] ?? null,
                        'jabatan' => $kerjasamaData['jabatan_pihak_1'] ?? null,
                        'id_pihak_penanggung_jawab' => $pihak1->id
                    ],
                    [
                        'nama_penanggung_jawab' => $kerjasamaData['nama_pihak_1'],
                        'telepon' => $kerjasamaData['no_hp_pihak_1'] ?? null,
                        'email' => $kerjasamaData['email_pihak_1'] ?? null,
                        'jabatan' => $kerjasamaData['jabatan_pihak_1'] ?? null,
                        'waktu_dibuat' => now(),
                        'dibuat_oleh' => auth()->id(),
                        'id_pihak_penanggung_jawab' => $pihak1->id
                    ]
                );

                $pihak2 = $pihakService->firstOrCreate([
                    'model' => Kerjasama::class,
                    'model_id' => (int)$kerjasama->id,
                    'id_pihak' => (int)$model->id,
                    'pihak_ke' => 2,
                    'model_pihak' => Mitra::class,
                ], [
                    'waktu_dibuat' => now(),
                    'dibuat_oleh' => auth()->id()
                ]);

                $penanggungJawabService->firstOrCreate(
                    [
                        'nama_penanggung_jawab' => $kerjasamaData['nama_pihak_2'],
                        'telepon' => $kerjasamaData['no_hp_pihak_2'] ?? null,
                        'email' => $kerjasamaData['email_pihak_2'] ?? null,
                        'jabatan' => $kerjasamaData['jabatan_pihak_2'] ?? null,
                        'dibuat_oleh' => auth()->id(),
                        'id_pihak_penanggung_jawab' => $pihak2->id
                    ],
                    [
                        'nama_penanggung_jawab' => $kerjasamaData['nama_pihak_2'],
                        'telepon' => $kerjasamaData['no_hp_pihak_2'] ?? null,
                        'email' => $kerjasamaData['email_pihak_2'] ?? null,
                        'jabatan' => $kerjasamaData['jabatan_pihak_2'] ?? null,
                        'id_pihak_penanggung_jawab' => $pihak2->id
                    ]
                );
            }
        }
    }



    private function determineTingkatMitra(?string $lingkupMitra): string
    {
        if (empty($lingkupMitra)) {
            return Mitra::LEVEL_LOKAL;
        }

        $lingkup = strtolower(trim($lingkupMitra));

        if (strpos($lingkup, 'lokal') !== false) {
            return Mitra::LEVEL_LOKAL;
        } elseif (strpos($lingkup, 'regional') !== false) {
            return Mitra::LEVEL_REGIONAL;
        } elseif (strpos($lingkup, 'nasional') !== false) {
            return Mitra::LEVEL_NASIONAL;
        } elseif (strpos($lingkup, 'internasional') !== false) {
            return Mitra::LEVEL_INTERNASIONAL;
        }

        return Mitra::LEVEL_LOKAL;
    }

    private function determineJenisMitra(?string $value)
    {
        if ($value === 'Perguruan Tinggi') {
            return 'PT';
        }
        return 'INS';
    }

    public function afterAllIMitra($results)
    {
        if (is_array($results) && isset($results['summary'])) {
            $results['summary']['duplicate_count'] = $this->duplicateCount;
        }
        return $results;
    }
}
