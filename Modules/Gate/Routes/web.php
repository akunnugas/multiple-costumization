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
use Modules\Gate\Http\Controllers\Web\UserController;
use Modules\Gate\Http\Controllers\Web\SessionController;
use Modules\Gate\Http\Middleware\ViewComposer;
use Modules\Gate\Livewire\IndexRole;
use Modules\Gate\Livewire\IndexUser;

Route::prefix('gate')->group(function () {
    Route::middleware(ViewComposer::class)->group(function () {
        Route::middleware('auth.role')->group(function () {
            $router = new Router('gate');
            $router->resource('user', UserController::class, indexComponent: IndexUser::class);
            $router->referenceResource('role', IndexRole::class);
        });

        Route::middleware('auth.home')->group(function () {
            // FIXME: untuk sekarang redirect ke user
            Route::get('/', function () {
                return redirect()->route('gate.user.index');
            })->name('gate.home');
        });
    });

    Route::get('sessions/auth', [SessionController::class, 'authenticate']);
    Route::get('sessions/login', [SessionController::class, 'login']);

    Route::middleware('auth')->group(function () {
        Route::delete('sessions', [SessionController::class, 'destroy']);
        Route::post('sessions/switch-role', [SessionController::class, 'switchRole'])->name('switch-role');

        // TODO: sekarang quantum menggunakan link untuk logout, ubah ke form
        Route::get('sessions/logout', [SessionController::class, 'destroy'])->name('logout');
    });
});
