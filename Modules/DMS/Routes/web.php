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
use Modules\DMS\Http\Controllers\DMSController;
use Modules\DMS\Http\Controllers\FileController;
use Modules\DMS\Http\Controllers\FolderController;
use Modules\DMS\Http\Controllers\UserFileController;
use Modules\DMS\Http\Middleware\ViewComposer;

$router = new Router('dms');

Route::prefix('dms')->group(function () use ($router) {
    Route::middleware('auth.role')->group(function () use ($router) {
        Route::middleware(ViewComposer::class)->group(function () use ($router) {
            Route::get('/', [DMSController::class, 'index'])->name('dms.index');
            $router->resource('user-files', UserFileController::class)->only(['index', 'destroySome']);
            $router->resource('folder', FolderController::class);
            $router->resource('files', FileController::class)->only(['store', 'update', 'destroy']);

            Route::get('files/{dokumen}/preview', [FileController::class, 'preview'])->name('dms.files.preview');
        });
    });

    Route::middleware('auth')->group(function () use ($router) {
        // Non View Composer
        Route::get('files/{dokumen}', [FileController::class, 'raw'])->name('dms.files.raw');
        Route::get('files/{dokumen}/download', [FileController::class, 'download'])->name('dms.files.download');
    });
});
