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
use Modules\PMB\Http\Controllers\Web\AktivitasController;
use Modules\PMB\Http\Controllers\Web\PengumumanController;
use Modules\PMB\Http\Controllers\Web\SeleksiKomponenController;
use Modules\PMB\Http\Controllers\Web\SyaratController;
use Modules\PMB\Http\Controllers\Web\SeleksiRuanganController;
use Modules\PMB\Http\Controllers\Web\SyaratPilihanController;
use Modules\PMB\Http\Controllers\Web\SeleksiJenisController;
use Modules\PMB\Http\Controllers\Web\GelombangController;
use Modules\PMB\Http\Controllers\Web\BroadcastController;
use Modules\PMB\Http\Controllers\Web\BroadcastPenerimaController;
use Modules\PMB\Http\Controllers\Web\GedungController;
use Modules\PMB\Http\Controllers\Web\KampusController;
use Modules\PMB\Http\Controllers\Web\KotaController;
use Modules\PMB\Http\Controllers\Web\NegaraController;
use Modules\PMB\Http\Controllers\Web\JenjangPendidikanController;
use Modules\PMB\Http\Controllers\Web\KecamatanController;
use Modules\PMB\Http\Controllers\Web\KeluargaController;
use Modules\PMB\Http\Controllers\Web\StatusHubunganKeluargaController;
use Modules\PMB\Http\Controllers\Web\JenisInstitusiController;
use Modules\PMB\Http\Controllers\Web\PekerjaanController;
use Modules\PMB\Http\Controllers\Web\SistemKuliahController;
use Modules\PMB\Http\Controllers\Web\PeriodeAkademikController;
use Modules\PMB\Http\Controllers\Web\SeleksiController;
use Modules\PMB\Http\Controllers\Web\SeleksiKomposisiController;
use Modules\PMB\Http\Controllers\Web\SebaranProdiController;
use Modules\PMB\Http\Controllers\Web\ProvinsiController;
use Modules\PMB\Http\Controllers\Web\PendaftarController;
use Modules\PMB\Http\Controllers\Web\JalurPendaftaranController;
use Modules\PMB\Http\Controllers\Web\PeriodePendaftaranController;
use Modules\PMB\Http\Controllers\Web\SyaratPendaftaranController;
use Modules\PMB\Http\Controllers\Web\TautanTerkaitController;
use Modules\PMB\Http\Controllers\Web\AgamaController;
use Modules\PMB\Http\Controllers\Web\PenilaianRaporController;
use Modules\PMB\Http\Controllers\Web\SyaratJenisController;
use Modules\PMB\Http\Controllers\Web\RuanganController;
use Modules\PMB\Http\Controllers\Web\JenisRuanganController;
use Modules\PMB\Http\Controllers\Web\PenghasilanController;
use Modules\PMB\Http\Controllers\Web\SekolahController;
use Modules\PMB\Http\Controllers\Web\ProgramStudiController;
use Modules\PMB\Http\Controllers\Web\PerguruanTinggiController;
use Modules\PMB\Http\Controllers\Web\MataPelajaranController;
use Modules\PMB\Http\Middleware\ViewComposer;

Route::prefix('pmb')->middleware(ViewComposer::class)->group(function () {
    $router = new Router('pmb');

    Route::middleware('auth.home')->group(function () {
        // FIXME: utk sekarang redirect ke batch
        Route::get('/', function () {
            return redirect()->route('pmb.batches.index');
        })->name('pmb.home');
    });

    Route::middleware('auth.role')->group(function () use ($router) {

        // Pendaftar
        $router->resource('registrants', PendaftarController::class);
        Route::prefix('registrants')->as('pmb.registrants.')->group(function () use ($router) {
            Route::prefix('{registrant}')->group(function () use ($router) {
                $router->resource('families', KeluargaController::class)->names('families'); // Anggota Keluarga
            });
        });

        /**
         * ? Menu Pengaturan
         */
        $router->resource('registration-periods', PeriodePendaftaranController::class);
        Route::prefix('registration-periods')->as('pmb.registration-periods.')->group(function () use ($router) {
            Route::prefix('{registration_period}')->group(function () use ($router) {
                $router->resource('program-assessments', SeleksiController::class)
                    ->names('program-assessments');
                $router->resource('program-distributions', SebaranProdiController::class)
                    ->names('program-distributions');
                $router->resource('program-compositions', SeleksiKomposisiController::class)
                    ->names('program-compositions');
                $router->resource('registration-requirements', SyaratPendaftaranController::class)
                    ->names('registration-requirements');
            });
        });

        /**
         * ? Menu Referensi
         */
        // Pendaftaran
        $router->resource('batches', GelombangController::class);
        $router->resource('registration-paths', JalurPendaftaranController::class);
        $router->resource('lecture-systems', SistemKuliahController::class);
        // Berita
        $router->resource('announcements', PengumumanController::class);
        $router->resource('related-links', TautanTerkaitController::class);
        $router->resource('broadcasts', BroadcastController::class);
        Route::prefix('broadcasts')->as('pmb.broadcasts.')->group(function () use ($router) {
            Route::prefix('{broadcast}')->group(function() use ($router) {
                $router->resource('broadcast-recipients', BroadcastPenerimaController::class)
                    ->names('broadcast-recipients');
            });
        });
        // Seleksi
        $router->resource('assessment-types', SeleksiJenisController::class);
        $router->resource('assessment-compositions', SeleksiKomponenController::class);
        $router->resource('requirement-types', SyaratJenisController::class);
        $router->resource('assessment-requirements', SyaratController::class);
        $router->resource('assessment-selections', SyaratPilihanController::class);
        $router->resource('subjects', MataPelajaranController::class);
        $router->resource('report-evaluations', PenilaianRaporController::class);
        // wilayah
        $router->resource('countries', NegaraController::class); // Negara
        $router->resource('provinces', ProvinsiController::class); // Provinsi
        $router->resource('cities', KotaController::class); // Kabupaten / Kota
        $router->resource('districts', KecamatanController::class); // Kecamatan
        // Pelengkap
        $router->resource('religions', AgamaController::class); // Agama
        // $router->resource('languages', LanguageController::class); // Bahasa
        $router->resource('jobs', PekerjaanController::class); // Pekerjaan
        $router->resource('salaries', PenghasilanController::class); // Pekerjaan
        $router->resource('family_statuses', StatusHubunganKeluargaController::class); // Status Hubungan Keluarga
        // $router->resource('marriage-statuses', MarriageStatusController::class); // Status Pernikahan
        // Pendidikan
        $router->resource('degrees', JenjangPendidikanController::class); // Jenjang Pendidikan
        $router->resource('schools', SekolahController::class); // Sekolah
        $router->resource('institution-types', JenisInstitusiController::class); // Jenis Institusi
        $router->resource('general-universities', PerguruanTinggiController::class); // Universitas Luar
        $router->resource('general-programs', ProgramStudiController::class); // Prodi Luar
        // Ruangan
        $router->resource('room-types', JenisRuanganController::class); // Jenis Ruangan
        $router->resource('campuses', KampusController::class); // Kampus
        $router->resource('buildings', GedungController::class); // Gedung
        $router->resource('rooms', RuanganController::class); // Ruangan
        $router->resource('assessment-rooms', SeleksiRuanganController::class); // Ruangan Seleksi

        // Lainya
        $router->resource('periods', PeriodeAkademikController::class);
        $router->resource('activities', AktivitasController::class);
    });
});
