<?php

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Web\SampleController;
// use Modules\Gate\Http\Controllers\Web\SessionController;
use Modules\Core\Http\Controllers\Web\TempController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// FIXME: sementara
/* Route::middleware('guest')->group(function () {
    Route::get('/', [SessionController::class, 'index'])->name('login');
}); */

Route::middleware('guest')->group(function () {
    // NOTE: Sementara tidak dipakai
    // Route::resource('/', TempController::class)->only(['index', 'store'])->names(['index' => 'login']);
    Route::get('/', [TempController::class, 'redirectGateV1'])->name('login');
    // Route::post('/temp/migrate', [TempController::class, 'migrate']);
});

Route::get(RouteServiceProvider::HOME, [TempController::class, 'redirectToActiveModule']);

if (env('APP_ENV') !== 'production') {

    // sample
    Route::prefix('sample')->controller(SampleController::class)->group(function () {
        Route::middleware('auth.home')->group(function () {
            Route::get('/', 'home')->name('sample.home');
        });
        Route::middleware('auth.role')->group(function () {
            Route::get('list', 'index')->name('sample.resource.index');
            Route::get('create', 'create')->name('sample.resource.create');
            Route::get('detail', 'detail')->name('sample.resource.detail');
        });
        Route::get('list/sidebar', 'indexWithSidebar')->middleware('auth.role:list-sidebar')->name('sample.resource.index.sidebar');
        Route::get('create/advanced', 'createAdvanced')->middleware('auth.role:create-advanced')->name('sample.resource.create.advanced');

        // FrontEnd (HTML Only)
        Route::get('frontend/nav', 'frontendDetail');
        Route::get('frontend/tab', 'frontendTab');
        Route::get('frontend/form', 'frontendForm');
        Route::get('frontend/detail', function () {
            return view('core::pages.sample.frontend.detail');
        });
    });
}

// NOTE: Healthy check untuk kebutuhan probe
Route::middleware('healthy.check')->group(function () {
    Route::get('/healthz', function () {
        return response()->json([
            'status' => 'OK!!!',
        ]);
    });
});


// fallback route
Route::fallback(function () {
    abort(404);
});
