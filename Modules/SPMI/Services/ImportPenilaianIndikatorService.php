<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;

class ImportPenilaianIndikatorService
{


    protected static array $sheets;
    protected static $allowedJenisIndikator = ['lk', 'ed'];
    protected static string $jenisIndikator;


    protected static ?string $panduanId = null;
    protected static array $periodeIds = [];
    protected static array $unitIds = [];

    public function __construct(
        array $sheets,
        string $jenisIndikator,
        string $panduanId = null,
        array $periodeIds = [],
        array $unitIds = []
    ) {
        if (!in_array($jenisIndikator, self::$allowedJenisIndikator)) {
            throw new \Exception("Jenis indikator $jenisIndikator tidak valid");
        }

        self::$sheets = $sheets;
        self::$jenisIndikator = $jenisIndikator;
        self::$panduanId = $panduanId;
        self::$periodeIds = $periodeIds;
        self::$unitIds = $unitIds;
    }

    public static function importLK()
    {
        try {
            DB::beginTransaction();

            $matrices = self::$sheets[0];
            $matrix_ids = [];
            foreach ($matrices as $key => $row) {
                if ($key == 0 || empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $new = IndikatorLaporanKinerja::updateOrCreate(
                    [
                        'nomor_indikator' => (string) $row[0],
                        'id_pengisian_panduan' => self::$panduanId,
                    ],
                    [
                        'id_pengisian_panduan' => self::$panduanId,
                        'nomor_indikator' => (string) $row[0],
                        'nama_indikator_laporan_kinerja' => $row[1],
                        'deskripsi' => $row[2] ?? null,
                        'informasi' => $row[3],
                        'sumber_data' => IndikatorLaporanKinerja::DATA_MANUAL_INPUT,
                        'dapat_dilihat_pada_laporan' => true,
                        'dapat_lihat_nama_pada_laporan' => true,
                        'apakah_parent' => false,
                        'apakah_aktif' => $row[4] === 'TRUE' ? true : false,
                    ]
                );

                $matrix_ids[] = $new->id;
            }

            foreach (self::$unitIds as $unitId) {
                foreach (self::$periodeIds as $periodeId) {
                    $payload = [
                        'mapping' => $matrix_ids,
                        'id_pengisian_panduan' => self::$panduanId,
                        'id_audit_periode' => $periodeId,
                        'id_unit' => $unitId,
                    ];
                    $mapping = new MappingLKManagementService();
                    $mapping->store($payload);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Gagal mengimport data Indikator Kinerja Tambahan'];
        }

        return [true, 'Berhasil mengimport data Indikator Kinerja Tambahan'];
    }

    public static function importED()
    {
        try {
            DB::beginTransaction();

            $matrices = self::$sheets[0];
            $matrix_ids = [];
            foreach ($matrices as $key => $row) {
                if ($key == 0 || empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $new = IndikatorEvaluasiDiri::updateOrCreate(
                    [
                        'nomor_indikator' => (string) $row[0],
                        'id_pengisian_panduan' => self::$panduanId,
                    ],
                    [
                        'id_pengisian_panduan' => self::$panduanId,
                        'nomor_indikator' => (string) $row[0],
                        'nama_indikator_evaluasi_diri' => $row[1],
                        'deskripsi' => $row[2] ?? null,
                        'apakah_aktif' => $row[3] === 'TRUE' ? true : false,
                        'apakah_data_default' => false,
                        'apakah_parent' => false,
                    ]
                );

                $matrix_ids[] = $new->id;
            }

            foreach (self::$unitIds as $unitId) {
                foreach (self::$periodeIds as $periodeId) {
                    $payload = [
                        'mapping' => $matrix_ids,
                        'id_pengisian_panduan' => self::$panduanId,
                        'id_audit_periode' => $periodeId,
                        'id_unit' => $unitId,
                    ];
                    $mapping = new MappingLEDManagementService();
                    $mapping->store($payload);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Gagal mengimport data Indikator Evaluasi Diri Tambahan'];
        }

        return [true, 'Berhasil mengimport data Indikator Evaluasi Diri Tambahan'];
    }

    public static function import()
    {
        /**
         * Sheet 1: Indikator Evaluasi Diri
         * Sheet 2: Referensi
         */

        if (self::$jenisIndikator == 'lk') {
            return self::importLK();
        }

        return self::importED();
    }
}
