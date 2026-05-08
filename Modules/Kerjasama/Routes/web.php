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
use Modules\Kerjasama\Http\Controllers\Web\KerjasamaController;
use Modules\Kerjasama\Http\Controllers\Web\BentukKegiatanController;
use Modules\Kerjasama\Http\Controllers\Web\DashboardController;
use Modules\Kerjasama\Http\Controllers\Web\DashboradKerjasamaController;
use Modules\Kerjasama\Http\Controllers\Web\EvaluasiKerjasamaController;
use Modules\Kerjasama\Http\Controllers\Web\EvaluasiKuesionerController;
use Modules\Kerjasama\Http\Controllers\Web\EvaluasiPengisianController;
use Modules\Kerjasama\Http\Controllers\Web\KriteriaMitraController;
use Modules\Kerjasama\Http\Controllers\Web\LaporanKerjasamaController;
use Modules\Kerjasama\Http\Controllers\Web\StatusKerjasamaController;
use Modules\Kerjasama\Http\Controllers\Web\SumberDanaController;
use Modules\Kerjasama\Http\Controllers\Web\JenisDokumenController;
use Modules\Kerjasama\Http\Controllers\Web\KegiatanController;
use Modules\Kerjasama\Http\Controllers\Web\KegiatanKerjasamaController;
use Modules\Kerjasama\Http\Controllers\Web\MitraController;
use Modules\Kerjasama\Http\Controllers\Web\KerjasamaMitraController;
use Modules\Kerjasama\Http\Controllers\Web\SasaranKinerjaController;
use Modules\Kerjasama\Http\Controllers\Web\UnitKerjaController;
use Modules\Kerjasama\Http\Middleware\ViewComposer;
use Modules\Kerjasama\Livewire\FormKegiatan;
use Modules\Kerjasama\Livewire\FormKerjasama;
use Modules\Kerjasama\Livewire\FormEvaluasi;

use Modules\Kerjasama\Livewire\FormMitra;
use Modules\Kerjasama\Livewire\FormSasaranKinerja;

$router = new Router('kerjasama');

Route::prefix('kerjasama')->middleware(ViewComposer::class)->group(function () use ($router) {


    Route::get('kuesioner/view/{uuid}', [EvaluasiPengisianController::class, 'index'])
        ->name('kerjasama.kuesioner.view');
    Route::post('kuesioner/view/{uuid}/store', [EvaluasiPengisianController::class, 'store'])
        ->name('kerjasama.kuesioner.store');
    Route::get('kuesioner/view/{uuid}/success', [EvaluasiPengisianController::class, 'success'])
        ->name('kerjasama.kuesioner.success');
        
    Route::middleware('auth.role')->group(function () use ($router) {
        // sementara route / redirect ke data-kerjasama
        Route::get('/', function () {
            return redirect()->route('kerjasama.dashboard.index');
        })->name('kerjasama.home');

        // Data Referensi
        $router->resource('bentuk-kegiatan', BentukKegiatanController::class);
        $router->resource('sasaran-kinerja', SasaranKinerjaController::class);
        $router->livewireComponent('sasaran-kinerja/create', FormSasaranKinerja::class)->name('kerjasama.sasaran-kinerja.create');
        $router->livewireComponent('sasaran-kinerja/{sasaran_kinerja}/edit', FormSasaranKinerja::class)->name('kerjasama.sasaran-kinerja.edit');
        $router->resource('status-kerjasama', StatusKerjasamaController::class);
        $router->resource('kriteria-mitra', KriteriaMitraController::class);
        $router->resource('sumber-dana', SumberDanaController::class);
        $router->resource('jenis-dokumen', JenisDokumenController::class);
        $router->resource('unit-kerja', UnitKerjaController::class,  withSync: true);

        // Data Mitra
        Route::get('mitra/export', [MitraController::class, 'export'])
            ->name('kerjasama.mitra.export');


        Route::post('mitra/import', [MitraController::class, 'import'])
            ->name('kerjasama.mitra.import');

        $router->resource('mitra', MitraController::class);
        $router->livewireComponent('mitra/{mitra}/edit', FormMitra::class)->name('kerjasama.mitra.edit');
        $router->livewireComponent('mitra/create', FormMitra::class)->name('kerjasama.mitra.create');

        Route::get('kerjasama-mitra/{id_mitra}', [KerjasamaMitraController::class, 'index'])
            ->name('kerjasama.kerjasama-mitra.index');
        Route::get('kerjasama-mitra/{id_mitra}/{id}/show', [KerjasamaMitraController::class, 'show'])
            ->name('kerjasama.kerjasama-mitra.show');

        // Dashborad 
        $router->resource('dashboard', DashboardController::class);


        // Data Kerjasama
        Route::get('data-kerjasama/export', [KerjasamaController::class, 'export'])
            ->name('kerjasama.data-kerjasama.export');

        Route::get('data-kerjasama/export-format', [KerjasamaController::class, 'exportFormat'])
            ->name('kerjasama.data-kerjasama.export-format');

        Route::post('data-kerjasama/import', [KerjasamaController::class, 'import'])
            ->name('kerjasama.data-kerjasama.import');
        $router->resource('data-kerjasama', KerjasamaController::class);
        $router->livewireComponent('data-kerjasama/{data_kerjasama}/edit', FormKerjasama::class)->name('kerjasama.data-kerjasama.edit');
        $router->livewireComponent('data-kerjasama/create', FormKerjasama::class)->name('kerjasama.data-kerjasama.create');

        // Data Kegiatan
        Route::get('kegiatan/export-format', [KegiatanController::class, 'exportFormat'])
            ->name('kerjasama.kegiatan.export-format');

        $router->resource('kegiatan', KegiatanController::class);
        Route::post('kegiatan/import', [KegiatanController::class, 'import'])
            ->name('kerjasama.kegiatan.import');
        $router->livewireComponent('kegiatan/create', FormKegiatan::class)
            ->name('kerjasama.kegiatan.create');
        $router->livewireComponent('kegiatan/{kegiatan}/edit', FormKegiatan::class)
            ->name('kerjasama.kegiatan.edit');
        Route::get('kegiatan/{id}/report', [KegiatanController::class, 'report'])
            ->name('kerjasama.kegiatan.report');

        // Data Kegiatan Kerjasama
        Route::get('kegiatan-kerjasama/{id_parent}', [KegiatanKerjasamaController::class, 'index'])
            ->name('kerjasama.kegiatan-kerjasama.index');
        Route::post('kegiatan-kerjasama/{id_parent}/delete', [KegiatanKerjasamaController::class, 'destroySome'])
            ->name('kerjasama.kegiatan-kerjasama.destroy_some');
        Route::delete('kegiatan-kerjasama/{id_parent}/{id}', [KegiatanKerjasamaController::class, 'destroy'])
            ->name('kerjasama.kegiatan-kerjasama.destroy');




        // Data Evaluasi Kuesioner
        $router->livewireComponent('evaluasi-kuesioner/create', FormEvaluasi::class)
            ->name('kerjasama.evaluasi-kuesioner.create');
        $router->livewireComponent('evaluasi-kuesioner/{evaluasi_kuesioner}/edit', FormEvaluasi::class)
            ->name('kerjasama.evaluasi-kuesioner.edit');
        $router->resource('evaluasi-kuesioner', EvaluasiKuesionerController::class)->except(['edit', 'create']);




        // data Evaluasi Kerjasama
        Route::get('evaluasi-kerjasama/{id_parent}', [EvaluasiKerjasamaController::class, 'index'])
            ->name('kerjasama.evaluasi-kerjasama.index');
        Route::get('evaluasi-kerjasama/show/{id}', [EvaluasiKerjasamaController::class, 'index2'])
            ->name('kerjasama.evaluasi-kuesioner-kerjasama.show');
        Route::post('evaluasi-kerjasama/{id_parent}/delete', [EvaluasiKerjasamaController::class, 'destroySome'])
            ->name('kerjasama.evaluasi-kuesioner-kerjasama.destroy_some');
        Route::delete('evaluasi-kerjasama/{id_parent}/{id}', [EvaluasiKerjasamaController::class, 'destroy'])
            ->name('kerjasama.evaluasi-kuesioner-kerjasama.destroy');
        Route::post('evaluasi-kerjasama/{id}/duplicate', [EvaluasiKerjasamaController::class, 'duplicate'])
            ->name('kerjasama.evaluasi-kuesioner-kerjasama.duplicate');



        // Laporan Kerjasama
        Route::get('laporan-kerjasama', [LaporanKerjasamaController::class, 'index'])
            ->name('kerjasama.laporan-kerjasama.index');
        Route::post('laporan-kerjasama', action: [LaporanKerjasamaController::class, 'show'])
            ->name('kerjasama.laporan-kerjasama.show');
    });

    Route::get('dokumen/{kegiatan}', [KerjasamaController::class, 'dokumen'])
        ->name('kerjasama.dokumen');
});
