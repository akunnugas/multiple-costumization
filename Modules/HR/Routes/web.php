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

use Modules\Core\Extensions\Router;
use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\HRController;
use Modules\HR\Http\Controllers\Web\JabatanAkademikController;
use Modules\HR\Http\Controllers\Web\AcademicTitleController;
use Modules\HR\Http\Controllers\Web\BankController;
use Modules\HR\Http\Controllers\Web\BloodTypeController;
use Modules\HR\Http\Controllers\Web\CertificationTypeController;
use Modules\HR\Http\Controllers\Web\CityController;
use Modules\HR\Http\Controllers\Web\CountryController;
use Modules\HR\Http\Controllers\Web\JenjangPendidikanController;
use Modules\HR\Http\Controllers\Web\DistrictController;
use Modules\HR\Http\Controllers\Web\EchelonController;
use Modules\HR\Http\Controllers\Web\EmployeeController;
use Modules\HR\Http\Controllers\Web\EmployeeStatusController;
use Modules\HR\Http\Controllers\Web\EthnicController;
use Modules\HR\Http\Controllers\Web\FieldStudyController;
use Modules\HR\Http\Controllers\Web\FunctionalPositionController;
use Modules\HR\Http\Controllers\Web\GeneralUniversityController;
use Modules\HR\Http\Controllers\Web\JobController;
use Modules\HR\Http\Controllers\Web\LanguageController;
use Modules\HR\Http\Controllers\Web\LecturerDedicationTypeController;
use Modules\HR\Http\Controllers\Web\MarriageStatusController;
use Modules\HR\Http\Controllers\Web\OutputTypeController;
use Modules\HR\Http\Controllers\Web\PositionLevelController;
use Modules\HR\Http\Controllers\Web\PromotionTypeController;
use Modules\HR\Http\Controllers\Web\ProvinceController;
use Modules\HR\Http\Controllers\Web\PublicationMediaController;
use Modules\HR\Http\Controllers\Web\ReligionController;
use Modules\HR\Http\Controllers\Web\ResearchOutputController;
use Modules\HR\Http\Controllers\Web\SKTypeController;
use Modules\HR\Http\Controllers\Web\StructuralPositionController;
use Modules\HR\Http\Controllers\Web\StructuralPositionTypeController;
use Modules\HR\Http\Controllers\Web\UnitKerjaController;
use Modules\HR\Http\Controllers\Web\WorkRelationController;
use Modules\HR\Http\Middleware\ViewComposer;

$router = new Router('hr');

Route::prefix('hr')->middleware(ViewComposer::class)->group(function () use ($router) {
    Route::middleware('auth.home')->group(function () {
        Route::get('/', [HRController::class, 'index']);
    });

    Route::middleware('auth.role')->group(function () use ($router) {
        // Pegawai
        $router->resource('employees', EmployeeController::class, withSync: true);

        // Master Aktifitas
        $router->resource('certification-types', CertificationTypeController::class);            // Jenis Sertifikasi
        $router->resource('lecturer-dedication-types', LecturerDedicationTypeController::class); // Jenis Pengabdian Masyarakat
        $router->resource('research-outputs', ResearchOutputController::class);                  // Output Penelitian
        $router->resource('output-types', OutputTypeController::class);                          // Jenis Luaran

        // wilayah
        $router->resource('countries', CountryController::class);                                // Negara
        $router->resource('provinces', ProvinceController::class);                               // Provinsi
        $router->resource('cities', CityController::class);                                      // Kabupaten / Kota
        $router->resource('districts', DistrictController::class);                               // Kecamatan

        // Kepegawaian
        $router->resource('unit-kerja', UnitKerjaController::class);                             // Unit Kerja
        $router->resource('employee-statuses', EmployeeStatusController::class);                 // Status Kepegawaian
        $router->resource('work-relations', WorkRelationController::class, withSync: true);      // Hubungan Kerja
        $router->resource('position-levels', PositionLevelController::class);                    // Pangkat / Golongan
        $router->resource('promotion-types', PromotionTypeController::class);                    // Jenis Kenaikan Pangkat
        $router->resource('jabatan-akademik', JabatanAkademikController::class);               // Jabatan Akademik
        $router->resource('functional-positions', FunctionalPositionController::class);          // Jabatan Fungsional
        $router->resource('echelons', EchelonController::class);                                 // Eselon
        $router->resource('structural-position-types', StructuralPositionTypeController::class); // Jenis Jabatan Struktural
        $router->resource('structural-positions', StructuralPositionController::class);          // Jabatan Struktural
        $router->resource('field-studies', FieldStudyController::class);                         // Rumpun Bidang Ilmu
        $router->resource('sk-types', SKTypeController::class);                                  // Jenis SK
        $router->resource('academic-titles', AcademicTitleController::class);                    // Gelar Akademik
        $router->resource('publication-medias', PublicationMediaController::class);              // Media Publikasi

        // Pelengkap
        $router->resource('religions', ReligionController::class);                               // Agama
        $router->resource('languages', LanguageController::class);                               // Bahasa
        $router->resource('jobs', JobController::class);                                         // Pekerjaan
        $router->resource('marriage-statuses', MarriageStatusController::class);                 // Status Pernikahan
        $router->resource('degrees', JenjangPendidikanController::class);                        // Jenjang Pendidikan
        $router->resource('general-universities', GeneralUniversityController::class);           // Universitas Luar
        $router->resource('banks', BankController::class);                                       // Bank
        $router->resource('ethnics', EthnicController::class);                                   // Suku
        $router->resource('blood-types', BloodTypeController::class);                            // Golongan Darah
    });
});
