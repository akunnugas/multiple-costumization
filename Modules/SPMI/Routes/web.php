<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\Core\Extensions\Router;
use Modules\SPMI\Http\Controllers\Web\CityController;
use Modules\SPMI\Http\Controllers\Web\CountryController;
use Modules\SPMI\Http\Controllers\Web\DistrictController;
use Modules\SPMI\Http\Controllers\Web\IndikatorLaporanKinerjaController;
use Modules\SPMI\Http\Controllers\Web\ProvinceController;
use Modules\SPMI\Http\Controllers\Web\ReligionController;
use Modules\SPMI\Http\Controllers\Web\AkreditasiStandarController;
use Modules\SPMI\Http\Controllers\Web\JenisStandarController;
use Modules\SPMI\Http\Controllers\Web\AkreditasiBukuController;
use Modules\SPMI\Http\Controllers\Web\PengisianPanduanController;
use Modules\SPMI\Http\Controllers\Web\LembagaAkreditasiController;
use Modules\SPMI\Http\Controllers\Web\PenilaianPanduanController;
use Modules\SPMI\Http\Controllers\Web\JenjangPendidikanController;
use Modules\SPMI\Http\Controllers\Web\IndikatorEvaluasiDiriController;
use Modules\SPMI\Http\Controllers\Web\UnitKerjaController;
use Modules\SPMI\Http\Controllers\Web\SpmiDokumenController;
use Modules\SPMI\Http\Controllers\Web\SettingIndikatorBobotController;
use Modules\SPMI\Http\Controllers\Web\AuditPeriodeController;
use Modules\SPMI\Http\Middleware\ViewComposer;
use Illuminate\Support\Facades\Storage;
use Modules\SPMI\Http\Controllers\Report\DocumentReports;
use Modules\SPMI\Http\Controllers\Web\AkreditasiPeringkatController;
use Modules\SPMI\Http\Controllers\Web\AkreditasiStatusController;
use Modules\SPMI\Http\Controllers\Web\AkreditasiSyaratController;
use Modules\SPMI\Http\Controllers\Web\IndikatorKolomController;
use Modules\SPMI\Http\Controllers\Web\TargetIndikatorController;
use Modules\SPMI\Http\Controllers\Web\PenilaianMatriksController;
use Modules\SPMI\Http\Controllers\Web\PenilaianMatriksPredikatController;
use Modules\SPMI\Http\Controllers\Web\PenilaianAuditorController;
use Modules\SPMI\Http\Controllers\Web\AuditTemuanController;
use Modules\SPMI\Http\Controllers\Web\JadwalAuditController;
use Modules\SPMI\Http\Controllers\Web\DokumenHasilAuditController;
use Modules\SPMI\Http\Controllers\Web\PengisianIndikatorController;
use Modules\SPMI\Http\Controllers\Web\PengisianIndikatorEvaluasiDiriController;
use Modules\SPMI\Http\Controllers\Web\HasilAkhirAuditController;
use Modules\SPMI\Http\Controllers\Web\HasilAuditTemuanController;
use Modules\SPMI\Http\Controllers\Web\IndikatorCellController;
use Modules\SPMI\Http\Controllers\Web\IndicatorFooterController;
use Modules\SPMI\Http\Controllers\Web\IndikatorReferensiController;
use Modules\SPMI\Http\Controllers\Web\IndikatorBarisController;
use Modules\SPMI\Http\Controllers\Web\IndikatorEvaluasiDiriTambahanController;
use Modules\SPMI\Http\Controllers\Web\IndikatorLaporanKinerjaTambahanController;
use Modules\SPMI\Http\Controllers\Web\MappingLEDController;
use Modules\SPMI\Http\Controllers\Web\MappingLKController;
use Modules\SPMI\Http\Controllers\Web\MappingLKLEDController;
use Modules\SPMI\Http\Controllers\Web\MappingPanduanController;
use Modules\SPMI\Http\Controllers\Web\MappingPenilaianMatriksController;
use Modules\SPMI\Http\Controllers\Web\PegawaiController;
use Modules\SPMI\Http\Controllers\Web\SpmiPeringkatController;
use Modules\SPMI\Http\Controllers\Web\PenilaianMandiriController;
use Modules\SPMI\Http\Controllers\Web\PenilaianMatriksIKTController;
use Modules\SPMI\Http\Controllers\Web\SkAuditorController;
use Modules\SPMI\Http\Controllers\Web\SkorMatriksPredikatController;
use Modules\SPMI\Http\Controllers\Web\SuratTugasAuditorController;
use Modules\SPMI\Http\Controllers\Web\TinjauanTemuanController;
use Modules\SPMI\Livewire\TargetIndikator;
use Modules\SPMI\Livewire\AuditTemuan;
use Modules\SPMI\Livewire\PenilaianAuditor;
use Modules\SPMI\Livewire\Dashboard;
use Modules\SPMI\Livewire\FormAkreditasiSyarat;
use Modules\SPMI\Livewire\FormIndikatorEvaluasiDiri;
use Modules\SPMI\Livewire\FormPenilaianMatriks;
use Modules\SPMI\Livewire\FormJadwalAudit;
use Modules\SPMI\Livewire\FormIndikatorLaporanKinerja;
use Modules\SPMI\Livewire\FormJenisStandart;
use Modules\SPMI\Livewire\FormMappingIndikatorButir;
use Modules\SPMI\Livewire\FormMappingMatriksPenilaian;
use Modules\SPMI\Livewire\FormPengisianPanduan;
use Modules\SPMI\Livewire\FormSKAuditor;
use Modules\SPMI\Livewire\FormSuratTugasAuditor;
use Modules\SPMI\Livewire\PenilaianMandiri;
use Modules\SPMI\Livewire\TinjauanTemuan;

$router = new Router('spmi');

Route::prefix('spmi')->middleware(ViewComposer::class)->group(function () use ($router) {
    Route::middleware('auth.home')->group(function () use ($router) {
        Route::get('/', [Dashboard::class, '__invoke'])->name('spmi.dashboard.index');

        // Temp File
        Route::get('local/temp/{path}', function (string $path) {
            $path = str_replace('|', '/', $path);
            return Storage::disk('local')->response($path);
        })->name('spmi.local.temp');

        // Lazy-load dosen search endpoints
        Route::get('search-dosen', function (Illuminate\Http\Request $request) {
            $search = $request->input('search', '');
            $limit = $request->input('limit', 20);
            $results = \Modules\Core\Models\Biodata::searchPengisianDataDosen($search, $limit);
            return response()->json($results);
        })->name('spmi.search-dosen');

        Route::get('dosen-option/{id}', function ($id) {
            $nama = \Modules\Core\Models\Biodata::getPengisianDataDosenNameById($id);
            if ($nama) {
                return response()->json([
                    'value' => $id,
                    'label' => $nama
                ]);
            }
            return response()->json(['error' => 'Not found'], 404);
        })->name('spmi.dosen-option');
    });

    Route::middleware('auth.role')->group(function () use ($router) {
        // AMI
        Route::get('setting-indikator-bobot', [SettingIndikatorBobotController::class, 'index'])
            ->name('spmi.setting-indikator-bobot.index');
        Route::put('setting-indikator-bobot', [SettingIndikatorBobotController::class, 'updateSetting'])
            ->name('spmi.setting-indikator-bobot.updateSetting');
        $router->resource('spmi-peringkat', SpmiPeringkatController::class);
        // $router->resource('akreditasi-peringkat', AkreditasiPeringkatController::class)
        //     ->only(['index', 'show']);
        $router->resource('audit-periode', AuditPeriodeController::class, withSync: true);
        $router->resource('target-indikator', TargetIndikatorController::class, false)
            ->except(['show', 'destroy']);
        Route::post('target-indikator/copy', [TargetIndikatorController::class, 'copy'])->name('spmi.target-indikator.copy');
        $router->livewireComponent('target-indikator/create', TargetIndikator::class)->name('spmi.target-indikator.create');
        $router->livewireComponent('target-indikator/{target_indikator}/edit', TargetIndikator::class)->name('spmi.target-indikator.edit');
        $router->resource('jadwal-audit', JadwalAuditController::class);
        $router->livewireComponent('jadwal-audit/create', FormJadwalAudit::class)->name('spmi.jadwal-audit.create');
        $router->livewireComponent('jadwal-audit/{jadwal_audit}/edit', FormJadwalAudit::class)->name('spmi.jadwal-audit.edit');
        $router->resource('pengisian-indikator', PengisianIndikatorController::class, false)->except(['destroy']);
        Route::post('pengisian-indikator/copy', [PengisianIndikatorController::class, 'copyFromOtherPeriod'])->name('spmi.pengisian-indikator.copy');

        Route::delete('pengisian-indikator/{pengisian_indikator}/delete-dokumen/{id_dokumen}', [PengisianIndikatorController::class, 'deleteDokumen'])
            ->name('spmi.pengisian-indikator.delete-dokumen');
        Route::delete('pengisian-indikator/{pengisian_indikator}/delete-all/{id_pengisian_indikator}', [PengisianIndikatorController::class, 'deleteAll'])
            ->name('spmi.pengisian-indikator.delete-all');

        $router->resource('sk-auditor', SkAuditorController::class);
        $router->livewireComponent('sk-auditor/create', FormSKAuditor::class)->name('spmi.sk-auditor.create');
        $router->livewireComponent('sk-auditor/{sk_auditor}/edit', FormSKAuditor::class)->name('spmi.sk-auditor.edit');
        $router->resource('surat-tugas-auditor', SuratTugasAuditorController::class);
        $router->livewireComponent('surat-tugas-auditor/create', FormSuratTugasAuditor::class)->name('spmi.surat-tugas-auditor.create');
        $router->livewireComponent('surat-tugas-auditor/{surat_tugas_auditor}/edit', FormSuratTugasAuditor::class)->name('spmi.surat-tugas-auditor.edit');
        $router->resource('penilaian-auditor', PenilaianAuditorController::class, false)
            ->except(['show', 'destroy']);
        $router->livewireComponent('penilaian-auditor/create', PenilaianAuditor::class)->name('spmi.penilaian-auditor.create');
        $router->livewireComponent('penilaian-auditor/{penilaian_auditor}/edit', PenilaianAuditor::class)->name('spmi.penilaian-auditor.edit');
        $router->resource('penilaian-mandiri', PenilaianMandiriController::class, false)
            ->except(['show', 'destroy']);
        $router->livewireComponent('penilaian-mandiri/create', PenilaianMandiri::class)->name('spmi.penilaian-mandiri.create');
        $router->livewireComponent('penilaian-mandiri/{penilaian_mandiri}/edit', PenilaianMandiri::class)->name('spmi.penilaian-mandiri.edit');
        $router->resource('dokumen-hasil-audit', DokumenHasilAuditController::class, false)
            ->only(['index', 'store', 'update']);
        $router->resource('hasil-akhir-audit', HasilAkhirAuditController::class, false)
            ->only(['show', 'index']);
        Route::get('hasil-akhir-audit/{final_result}/laporan', [HasilAkhirAuditController::class, 'report'])->name('spmi.hasil-akhir-audit.laporan');

        // Temuan Auditor
        $router->resource('audit-temuan', AuditTemuanController::class, false)
            ->only(['index']);
        // Id shownya adalah id dari auditor assessment
        $router->livewireComponent('audit-temuan/create', AuditTemuan::class)->name('spmi.audit-temuan.create');
        $router->livewireComponent('audit-temuan/{audit_temuan}', AuditTemuan::class)->name('spmi.audit-temuan.show');

        $router->resource('hasil-audit-temuan', HasilAuditTemuanController::class, false)
            ->only(['index']);
        $router->livewireComponent('hasil-audit-temuan/{audit_temuan}', AuditTemuan::class)->name('spmi.hasil-audit-temuan.show');

        // Tinjauan Manajemen
        $router->resource('tinjauan-temuan', TinjauanTemuanController::class, false)
            ->only(['index']);
        // Id shownya adalah id dari auditor assessment
        $router->livewireComponent('tinjauan-temuan/create', TinjauanTemuan::class)->name('spmi.tinjauan-temuan.create');
        $router->livewireComponent('tinjauan-temuan/{tinjauan_temuan}', TinjauanTemuan::class)->name('spmi.tinjauan-temuan.show');

        // Akreditasi
        $router->livewireComponent('akreditasi-syarat/create', FormAkreditasiSyarat::class)->name('spmi.akreditasi-syarat.create');
        $router->resource('akreditasi-syarat', AkreditasiSyaratController::class)->only(['index', 'show', 'destroy']);
        $router->livewireComponent('akreditasi-syarat/{akreditasi_syarat}/edit', FormAkreditasiSyarat::class)
            ->name('spmi.akreditasi-syarat.edit');

        // LK
        $router->resource('indikator-laporan-kinerja', IndikatorLaporanKinerjaController::class);
        $router->livewireComponent('indikator-laporan-kinerja/create', FormIndikatorLaporanKinerja::class)->name('spmi.indikator-laporan-kinerja.create');
        $router->livewireComponent('indikator-laporan-kinerja/{indikator_laporan_kinerja}/edit', FormIndikatorLaporanKinerja::class)
            ->name('spmi.indikator-laporan-kinerja.edit');

        $router->resource('indikator-lk-tambahan', IndikatorLaporanKinerjaTambahanController::class);
        $router->livewireComponent('indikator-lk-tambahan/create', FormIndikatorLaporanKinerja::class)->name('spmi.indikator-lk-tambahan.create');
        $router->livewireComponent('indikator-lk-tambahan/{indikator_lk_tambahan}/edit', FormIndikatorLaporanKinerja::class)
            ->name('spmi.indikator-lk-tambahan.edit');

        // LED
        $router->resource('indikator-led-tambahan', IndikatorEvaluasiDiriTambahanController::class);
        $router->livewireComponent('indikator-led-tambahan/create', FormIndikatorEvaluasiDiri::class)->name('spmi.indikator-led-tambahan.create');
        $router->livewireComponent('indikator-led-tambahan/{indikator_led_tambahan}/edit', FormIndikatorEvaluasiDiri::class)
            ->name('spmi.indikator-led-tambahan.edit');
        Route::prefix('indikator-led-tambahan')->as('spmi.indikator-led-tambahan.')->group(function () use ($router) {
            Route::prefix('{indicator}')->group(function () use ($router) {
                $router->resource('indikator-referensi', IndikatorReferensiController::class)->names('indicator-reference');
            });
        });

        $router->resource('indikator-evaluasi-diri', IndikatorEvaluasiDiriController::class);
        $router->livewireComponent('indikator-evaluasi-diri/create', FormIndikatorEvaluasiDiri::class)->name('spmi.indikator-evaluasi-diri.create');
        $router->livewireComponent('indikator-evaluasi-diri/{indikator_evaluasi_diri}/edit', FormIndikatorEvaluasiDiri::class)
            ->name('spmi.indikator-evaluasi-diri.edit');
        Route::prefix('indikator-evaluasi-diri')->as('spmi.indikator-evaluasi-diri.')->group(function () use ($router) {
            Route::prefix('{indicator}')->group(function () use ($router) {
                $router->resource('indikator-referensi', IndikatorReferensiController::class)->names('indicator-reference');
            });
        });

        $router->resource('jenis-standar', JenisStandarController::class)->only(['index', 'destroy']);
        $router->livewireComponent('jenis-standar/create', FormJenisStandart::class)->name('spmi.jenis-standar.create');
        $router->livewireComponent('jenis-standar/{jenis_standar}', FormJenisStandart::class)
            ->name('spmi.jenis-standar.show');
        $router->livewireComponent('jenis-standar/{jenis_standar}/edit', FormJenisStandart::class)->name('spmi.jenis-standar.edit');
        // $router->resource('akreditasi-standar', AkreditasiStandarController::class);
        // $router->resource('akreditasi-buku', AkreditasiBukuController::class);

        $router->resource('pengisian-panduan', PengisianPanduanController::class);
        $router->livewireComponent('pengisian-panduan/create', FormPengisianPanduan::class)->name('spmi.pengisian-panduan.create');
        $router->livewireComponent('pengisian-panduan/{pengisian_panduan}/edit', FormPengisianPanduan::class)->name('spmi.pengisian-panduan.edit');

        $router->resource('pengisian-indikator-led', PengisianIndikatorEvaluasiDiriController::class, false)->except(['destroy']);
        Route::delete('pengisian-indikator-led/{pengisian_indikator}/delete-dokumen/{id_dokumen}', [PengisianIndikatorEvaluasiDiriController::class, 'deleteDokumen'])
            ->name('spmi.pengisian-indikator-led.delete-dokumen');
        Route::post('pengisian-indikator-led/copy', [PengisianIndikatorEvaluasiDiriController::class, 'copyFromOtherPeriod'])->name('spmi.pengisian-indikator-led.copy');
        $router->resource('lembaga-akreditasi', LembagaAkreditasiController::class);
        $router->resource('spmi-dokumen', SpmiDokumenController::class);
        $router->resource('penilaian-panduan', PenilaianPanduanController::class);
        Route::prefix('penilaian-panduan')->as('spmi.penilaian-panduan.')->group(function () use ($router) {
            Route::prefix('{id}')->group(function () use ($router) {
                $router->resource('akreditasi-peringkat', AkreditasiPeringkatController::class)
                    ->names('akreditasi-peringkat');
                $router->resource('akreditasi-status', AkreditasiStatusController::class)
                    ->names('akreditasi-status');
                $router->resource('skor-matriks-predikat', SkorMatriksPredikatController::class)
                    ->names('skor-matriks-predikat');
            });
        });
        $router->resource('jenjang-pendidikan', JenjangPendidikanController::class, withSync: true); // Jenjang Pendidikan
        $router->resource('unit-kerja', UnitKerjaController::class, withSync: true); // Unit Kerja
        $router->resource('penilaian-matriks', PenilaianMatriksController::class); // Matrix Penilaian
        Route::get('penilaian-matriks-ikt/download-error', [PenilaianMatriksIKTController::class, 'downloadErrorReport'])->name('spmi.penilaian-matriks-ikt.download-error');
        $router->resource('penilaian-matriks-ikt', PenilaianMatriksIKTController::class); // Matrix Penilaian IKT
        Route::get('urutkan-matriks/{id_penilaian_panduan}', [PenilaianMatriksController::class, 'reorderForm'])->name('spmi.penilaian-matriks.urutkan-matriks');

        $router->resource('mapping-matriks-penilaian', MappingPenilaianMatriksController::class); // Matrix Penilaian
        $router->livewireComponent('mapping-matriks-penilaian/{unit_id}/{period_id}/edit', FormMappingMatriksPenilaian::class)->name('spmi.mapping-matriks-penilaian.edit');
        Route::get('mapping-matriks-penilaian/{unit_id}/{period_id}', [MappingPenilaianMatriksController::class, 'show'])->name('spmi.mapping-matriks-penilaian.show');

        $router->resource('mapping-indikator-butir', MappingLKLEDController::class)->only(['index']);
        $router->livewireComponent('mapping-indikator-butir/{unit_id}/{period_id}/edit', FormMappingIndikatorButir::class)->name('spmi.mapping-indikator-butir.edit');
        Route::get('mapping-indikator-butir/{unit_id}/{period_id}', [MappingLKLEDController::class, 'show'])->name('spmi.mapping-indikator-butir.show');

        $router->resource('mapping-panduan', MappingPanduanController::class); // Mapping Panduan
        $router->livewireComponent('penilaian-matriks-ikt/create', FormPenilaianMatriks::class)->name('spmi.penilaian-matriks-ikt.create');
        $router->livewireComponent('penilaian-matriks-ikt/{penilaian_matriks_ikt}/edit', FormPenilaianMatriks::class)->name('spmi.penilaian-matriks-ikt.edit');
        Route::prefix('penilaian-matriks-ikt')->as('spmi.penilaian-matriks-ikt.')->group(function () use ($router) {
            Route::prefix('{id}')->group(function () use ($router) {
                $router->resource('penilaian-matriks-predikat', PenilaianMatriksPredikatController::class)
                    ->names('penilaian-matriks-predikat');
            });
        });
        Route::prefix('penilaian-matriks')->as('spmi.penilaian-matriks.')->group(function () use ($router) {
            Route::prefix('{id}')->group(function () use ($router) {
                $router->resource('penilaian-matriks-predikat', PenilaianMatriksPredikatController::class)
                    ->names('penilaian-matriks-predikat');
            });
        });
        $router->resource('pegawai', PegawaiController::class, withSync: true);

        // Sementara tidak dipakai
        // $router->resource('university-types', UniversityTypeController::class); // Tipe Universitas

        // wilayah
        $router->resource('countries', CountryController::class);
        $router->resource('provinces', ProvinceController::class);
        $router->resource('cities', CityController::class);
        $router->resource('districts', DistrictController::class);

        // lainnya
        $router->resource('religions', ReligionController::class);

        // grub route reports
        Route::prefix('reports')->as('spmi.reports.')->group(function () use ($router) {
            Route::get('/', [DocumentReports::class, 'show'])->name('document-reports.show');
            Route::post('/', [DocumentReports::class, 'gReport'])->name('document-reports.generate');
            Route::post('filling-indicator-reports', [DocumentReports::class, 'gFillingReport'])->name('filling-reports.generate');
            Route::post('filling-self-reports', [DocumentReports::class, 'gFillingSelfEvaluation'])->name('filling-self-reports.generate');
        });
    });
});
