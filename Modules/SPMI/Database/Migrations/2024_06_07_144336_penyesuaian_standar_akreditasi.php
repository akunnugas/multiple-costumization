<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Cstr;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PenilaianKlaster;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $accreditationSync = new AccreditationSync;
        $assessmentGuideData = PenilaianPanduan::get();
        $assessmentGuideList = $assessmentGuideData->pluck('id', 'kode_penilaian_panduan')->toArray();

        $mapField = [
            'nobutirspmi' => 'nomor_penilaian',
            'kodespmi' => 'id_penilaian_panduan',
            'parentbutirspmi' => 'id_parent',
            'namabutirspmi' => 'pertanyaan_penilaian',
            'bobot' => 'bobot_penilaian',
            'keterangan' => 'deskripsi',
            'idkategori' => 'kategori_penilaian',
            'idklaster' => 'id_penilaian_klaster',
            'kodestandar' => 'id_akreditasi_standar',
            'idsyaratakreditasi' => 'syarat_terakreditasi',
            'idstandarpt' => 'standar_perguruan_tinggi',
            'isaktif' => 'apakah_aktif',
            'idjenispenilaian' => 'jenis_penilaian',
            'idsumberreferensi' => 'referensi_penilaian',
            'istampilkandinilaiakhir' => 'apakah_nilai_ditampilkan',
            'butirlk' => 'indikator_laporan_kinerja',
            'butirled' => 'indikator_evaluasi_diri',
            'level' => 'info_level',
            'infoleft' => 'info_left',
            'inforight' => 'info_right',
            'pilihanskor' => 'scores',
        ];

        foreach ($assessmentGuideList as $assessmentGuideCode => $assessmentGuideId) {
            $savedMatrices = [];

            DB::beginTransaction();

            $assessmentMatrices = $accreditationSync->getAssessmentMatrices($assessmentGuideCode);
            $assessmentMatrices = $assessmentMatrices['data'] ?? [];

            // Referensi Kluster Penilaian
            $mappedPenilaianKlaster = array_map(fn ($item) => $item['idklaster'], $assessmentMatrices);
            $refPenilaianKlaster = PenilaianKlaster::whereIn('kode_klaster', $mappedPenilaianKlaster)->pluck('id', 'kode_klaster')->toArray();

            // Referensi butir LK
            $indicatorPerformanceGuide = $assessmentGuideData->where('kode_penilaian_panduan', $assessmentGuideCode)->first()->id_laporan_kinerja;
            $mappedIndicatorPerformanceReport = array_map(fn ($item) => $item['butirlk'], $assessmentMatrices);
            $mappedIndicatorPerformanceReport = array_filter($mappedIndicatorPerformanceReport, fn ($item) => !empty($item));
            $mappedIndicatorPerformanceReport = array_unique(Arr::flatten($mappedIndicatorPerformanceReport));
            $refIndicatorPerformanceReport = IndikatorLaporanKinerja::where('id_pengisian_panduan', $indicatorPerformanceGuide)->whereIn('nomor_indikator', $mappedIndicatorPerformanceReport)->pluck('id', 'nomor_indikator')->toArray();

            // Referensi butir LED
            $indicatorSelfEvaluationGuide = $assessmentGuideData->where('kode_penilaian_panduan', $assessmentGuideCode)->first()->id_panduan_evaluasi_diri;
            $mappedIndicatorSelfEvaluation = array_map(fn ($item) => $item['butirled'], $assessmentMatrices);
            $mappedIndicatorSelfEvaluation = array_filter($mappedIndicatorSelfEvaluation, fn ($item) => !empty($item));
            $mappedIndicatorSelfEvaluation = array_unique(Arr::flatten($mappedIndicatorSelfEvaluation));
            $refIndicatorSelfEvaluation = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $indicatorSelfEvaluationGuide)
                ->whereIn('nomor_indikator', $mappedIndicatorSelfEvaluation)->pluck('id', 'nomor_indikator')->toArray();

            // Ambil standar akfreditasi sesuai jenis standar panduan
            $mappedStandarAkreditasi = array_map(
                function ($item) {
                    if (substr($item['kodestandar'], -1) == '.') {
                        $item['kodestandar'] = substr($item['kodestandar'], 0, -1);
                    }

                    return $item['kodestandar'];
                },
                $assessmentMatrices
            );

            $accreditationStandarts = AkreditasiStandar::whereIn('kode_standar', $mappedStandarAkreditasi)->get();

            foreach ($assessmentMatrices as $PenilaianMatriks) {
                $mappedData = [];

                foreach ($mapField as $key => $selfKey) {
                    $value = $PenilaianMatriks[$key] ?? null;

                    // Sanitasi html tag di pertanyaan penilaian
                    if ($key == 'namabutirspmi') {
                        $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                        continue;
                    }

                    if ($key == 'kodespmi') {
                        $mappedData[$selfKey] = $assessmentGuideId;
                        continue;
                    }

                    // Sementara di nonaktifkan
                    // if ($key == 'keterangan') {
                    //     $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                    //     continue;
                    // }

                    // Mapping parent matrix
                    if ($key == 'parentbutirspmi' && !empty($value)) {
                        $parentMatrix = array_filter($savedMatrices, fn ($item) => $item['nomor_penilaian'] == $value);
                        $parentMatrix = array_values($parentMatrix);
                        $mappedData[$selfKey] = $parentMatrix[0]['id'] ?? null;
                        continue;
                    }

                    if ($key == 'idklaster') {
                        $mappedData[$selfKey] = $refPenilaianKlaster[$value] ?? null;
                        continue;
                    }

                    if ($key == 'idsumberreferensi') {
                        $mappedData[$selfKey] = $value == 'BA' ? 'pr' : 'se';
                        continue;
                    }

                    if ($key == 'kodestandar') {
                        if (empty($value)) {
                            continue;
                        }

                        $standartCode = $value;
                        if (substr($value, -1) == '.') {
                            $standartCode = substr($value, 0, -1);
                        }

                        $mappedData[$selfKey] = $accreditationStandarts->where('kode_standar', $standartCode)->first()->id ?? null;
                        continue;
                    }

                    // Mapping skor matrix
                    if ($key == 'pilihanskor' && !empty($value) && is_array($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) {
                            return [
                                'nilai' => $item['skor'],
                                'deskripsi' => $item['uraian'],
                                'apakah_nonaktif' => $item['isdisable'],
                            ];
                        }, $value);
                        continue;
                    }

                    // Jika ada butir LK maka diambil dari referensi LK
                    if ($key == 'butirlk' && !empty($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) use ($refIndicatorPerformanceReport) {
                            return [
                                'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
                                'id_butir_referensi' => $refIndicatorPerformanceReport[$item['nobutir']] ?? null,
                            ];
                        }, $value);
                        continue;
                    }

                    // Jika ada butir LED maka diambil dari referensi LED
                    if ($key == 'butirled' && !empty($value)) {
                        $mappedData[$selfKey] = array_map(function ($item) use ($refIndicatorSelfEvaluation) {
                            return [
                                'jenis_referensi' => PenilaianMatriks::REFERENCE_SELF_EVALUATION,
                                'id_butir_referensi' => $refIndicatorSelfEvaluation[$item['kodeled']] ?? null,
                            ];
                        }, $value);
                        continue;
                    }

                    $mappedData[$selfKey] = $value;
                }

                // Jika katagori penilaian adalah element
                if (
                    ($mappedData['kategori_penilaian'] == PenilaianMatriks::CATEGORY_ELEMENT) &&
                    ($mappedData['jenis_penilaian']) == PenilaianMatriks::TYPE_FINAL_SCORE
                ) {
                    $mappedData['bobot_penilaian'] = null;
                }

                $toStoreMatrix = Arr::except($mappedData, ['indikator_laporan_kinerja', 'indikator_evaluasi_diri', 'scores']);

                try {
                    PenilaianMatriks::updateOrCreate(
                        [
                            'nomor_penilaian' => $toStoreMatrix['nomor_penilaian'],
                            'id_penilaian_panduan' => $toStoreMatrix['id_penilaian_panduan'],
                        ],
                        [
                            'id_akreditasi_standar' => $toStoreMatrix['id_akreditasi_standar'] ?? null,
                        ]
                    );
                } catch (\Exception $e) {
                    DB::rollback();
                    break;
                }
            }

            DB::commit();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
