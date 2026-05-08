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
use Modules\Admission\Http\Controllers\Web\KotaController;
use Modules\Admission\Http\Controllers\Web\SekolahController;
use Modules\Admission\Http\Controllers\Web\SessionController;
use Modules\Admission\Http\Middleware\ViewComposer;
use Modules\Admission\Livewire\Announcement;
use Modules\Admission\Livewire\AnnouncementDetail;
use Modules\Admission\Livewire\Home;
use Modules\Admission\Livewire\HomeSingle;
use Modules\Admission\Livewire\Login;
use Modules\Admission\Livewire\Registration;
use Modules\Admission\Livewire\RegistrationPath;
use Modules\Admission\Livewire\ProgramDetail;
use Modules\Admission\Livewire\RegistrationPathDetail;
use Modules\Admission\Livewire\RegistrationSuccess;
use Modules\Admission\Livewire\StepAdministration;
use Modules\Admission\Livewire\UserGuide;
use Modules\Core\Extensions\Router;
use Modules\Gate\Models\Modul;

Route::prefix(Modul::CODE_PMB_ADMISSION)->middleware([ViewComposer::class, 'auth.admission'])->group(function () {
    $router = new Router(Modul::CODE_PMB_ADMISSION);

    $router->livewireComponent('/home', HomeSingle::class)
        ->name('admission.home-single');
    $router->livewireComponent('/', Home::class)
        ->name('admission.home');
    $router->livewireComponent('registration-path', RegistrationPath::class)
        ->name('admission.registration-path');
    $router->livewireComponent('registration-path-detail', RegistrationPathDetail::class)
        ->name('admission.registration-path-detail');
    $router->livewireComponent('registration', Registration::class)
        ->name('admission.registration');
    $router->livewireComponent('registration-success', RegistrationSuccess::class)
        ->name('admission.registration-success');
    $router->livewireComponent('programs-detail/{id}', ProgramDetail::class)
        ->name('admission.programs.detail');
    $router->livewireComponent('announcements', Announcement::class)
        ->name('admission.announcements');
    $router->livewireComponent('announcements/{id}', AnnouncementDetail::class)
        ->name('admission.announcements.detail');
    $router->livewireComponent('user-guide', UserGuide::class)
        ->name('admission.user-guide');

    Route::get('auth', [SessionController::class, 'authenticate']);
    Route::post('login', [SessionController::class, 'login'])->name('admission.login');
    Route::post('forgot-password', [SessionController::class, 'forgotPassword'])->name('admission.forgot-password');
    Route::get('logout', [SessionController::class, 'destroy'])->name('admission.logout');

    Route::middleware(['guest.admission'])->group(function () use ($router) {
        $router->livewireComponent('login', Login::class);
    });

    // [Start] After login (langkah pendaftaran)
    $router->livewireComponent('registration-steps/administration', StepAdministration::class)
        ->name('admission.registration-steps.administration');
    // [End] After login (langkah pendaftaran)

    // [Start] searching
    Route::post('search-city', [KotaController::class, 'search'])->name('admission.city.search');
    Route::post('search-school', [SekolahController::class, 'search'])->name('admission.school.search');
    // [End] searching
});
