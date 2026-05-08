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

use Modules\Core\Http\Controllers\Web\RedirectToController;

Route::prefix('core')->group(function() {
    Route::get('/', 'CoreController@index');

    Route::post('/redirect-to', [RedirectToController::class, 'redirectTo'])->name('core.redirectto');
});
