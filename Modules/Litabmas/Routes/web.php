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
use Illuminate\Support\Facades\Storage;
use Modules\Core\Extensions\Router;
use Modules\Gate\Http\Middleware\EksternalRoleRequiredBiodata;
use Modules\Litabmas\Http\Controllers\Web\AspekPenilaianIsianProposalController;
use Modules\Litabmas\Http\Controllers\Web\AspekPenilaianKomposisiProposalController;
use Modules\Litabmas\Http\Controllers\Web\AspekPenilaianOutputController;
use Modules\Litabmas\Http\Controllers\Web\AspekPenilaianPresentasiProposalController;
use Modules\Litabmas\Http\Controllers\Web\BidangIlmuController;
use Modules\Litabmas\Http\Controllers\Web\BimbinganController;
use Modules\Litabmas\Http\Controllers\Web\DosenEksternalController;
use Modules\Litabmas\Http\Controllers\Web\JenisOutcomePenelitianController;
use Modules\Litabmas\Http\Controllers\Web\JenisOutputPenelitianController;
use Modules\Litabmas\Http\Controllers\Web\KlasterPendanaanController;
use Modules\Litabmas\Http\Controllers\Web\PengumumanKlasterController;
use Modules\Litabmas\Http\Controllers\Web\PendanaanKegiatanController;
use Modules\Litabmas\Http\Controllers\Web\PengajuanPendanaanController;
use Modules\Litabmas\Http\Controllers\Web\PengajuanPengabdianController;
use Modules\Litabmas\Http\Controllers\Web\PengumumanPendanaanController;
use Modules\Litabmas\Http\Controllers\Web\PenilaianReviewerController;
use Modules\Litabmas\Http\Controllers\Web\PeriodePendanaanController;
use Modules\Litabmas\Http\Controllers\Web\PerguruanTinggiController;
use Modules\Litabmas\Http\Controllers\Web\SumberPendanaanController;
use Modules\Litabmas\Http\Controllers\Web\TemaKegiatanController;
use Modules\Litabmas\Http\Controllers\Report\PendanaanKegiatan;
use Modules\Litabmas\Http\Controllers\Report\PengajuanProposal;
use Modules\Litabmas\Http\Controllers\Web\AgendaKegiatanController;
use Modules\Litabmas\Http\Middleware\ViewComposer;
use Modules\Litabmas\Livewire\Dashboard;
use Modules\Litabmas\Livewire\FilterLaporanPendanaanKegiatan;
use Modules\Litabmas\Livewire\FilterLaporanPengajuanProposal;
use Modules\Litabmas\Livewire\FormAspekPenilaianOutput;
use Modules\Litabmas\Livewire\FormDetailAktivitasPenelitian;
use Modules\Litabmas\Livewire\FormDetailMonitoringSumberPendanaan;
use Modules\Litabmas\Livewire\FormDetailPenilaianReviewer;
use Modules\Litabmas\Livewire\FormDetailProposal;
use Modules\Litabmas\Livewire\FormKlasterPendanaan;
use Modules\Litabmas\Livewire\FormPengajuanPendanaan;

$router = new Router('litabmas');

Route::prefix('litabmas')->middleware([EksternalRoleRequiredBiodata::class, ViewComposer::class])->group(function () use ($router) {
    Route::middleware('auth.home')->group(function () use ($router) {
        $router->livewireComponent('/', Dashboard::class)->name('litabmas.home');

        // Temp File
        Route::get('local/temp/{path}', function (string $path) {
            $path = str_replace('|', '/', $path);
            return Storage::disk('local')->response($path);
        })->name('litabmas.local.temp');

    });
    Route::middleware('auth.role')->group(function () use ($router) {
        // Pengajuan Pendanaan
        $router->resource(
            'pengajuan-pendanaan',
            PengajuanPendanaanController::class,
            withDestroySome: false
        )->except(['show']);

        $router->livewireComponent('pengajuan-pendanaan/create', FormPengajuanPendanaan::class)
            ->name('litabmas.pengajuan-pendanaan.create');
        $router->livewireComponent('pengajuan-pendanaan/{pengajuan_pendanaan}/edit', FormPengajuanPendanaan::class)
            ->name('litabmas.pengajuan-pendanaan.edit');
        $router->livewireComponent('pengajuan-pendanaan/{pengajuan_pendanaan}/{sub_resource?}', FormDetailProposal::class)
            ->name('litabmas.pengajuan-pendanaan.show');

        // Pengajuan Pengabdian
        $router->resource(
            'pengajuan-pengabdian',
            PengajuanPengabdianController::class,
            withDestroySome: false
        );

        // Agenda Kegiatan
        $router->resource(
            'agenda-kegiatan',
            AgendaKegiatanController::class,
            withDestroySome: false
        );

        /**
         * Usulan Anggota (Dosen Eksternal)
         */
        $router->resource(
            'usulan-dosen-eksternal',
            DosenEksternalController::class,
            withDestroySome: false
        );
        Route::put('usulan-dosen-eksternal/{usulan_dosen_eksternal}/approve', [DosenEksternalController::class, 'approve'])
            ->name('litabmas.usulan-dosen-eksternal.approve');


        /**
         * Pendanaan Kegiatan
         */
        $router->resource(
            'pendanaan-kegiatan',
            PendanaanKegiatanController::class,
            withDestroySome: false
        )->only(['index']);

        $router->livewireComponent('pendanaan-kegiatan/{id_sumber_pendanaan}', FormDetailMonitoringSumberPendanaan::class)
            ->name('litabmas.pendanaan-kegiatan.show');

        /**
         * Pengumuman
         */
        $router->resource(
            'pengumuman-pendanaan',
            PengumumanPendanaanController::class,
            withDestroySome: false
        );
        Route::prefix('pengumuman-pendanaan')->as('litabmas.pengumuman-pendanaan.')->group(function () use ($router) {
            Route::prefix('{pengumuman_pendanaan}')->group(function () use ($router) {
                Route::put('publikasikan-pengumuman', [PengumumanPendanaanController::class, 'publikasikanPengumuman'])
                    ->name('publikasikan-pengumuman');
                Route::put('batalkan-publikasi-pengumuman', [PengumumanPendanaanController::class, 'batalkanPublikasiPengumuman'])
                    ->name('batalkan-publikasi-pengumuman');
            });
            //create
            Route::get('create', [PengumumanPendanaanController::class, 'create'])
                ->name('create');
        });

        /**
         * Data Referensi
         */
        $router->resource(
            'perguruan-tinggi',
            PerguruanTinggiController::class,
            withDestroySome: false,
            withSync: true
        );

        $router->resource(
            'periode-pendanaan',
            PeriodePendanaanController::class,
            withDestroySome: false
        );
        $router->resource(
            'sumber-pendanaan',
            SumberPendanaanController::class,
            withDestroySome: false
        );
        $router->resource(
            'klaster-pendanaan',
            KlasterPendanaanController::class,
            withDestroySome: false
        );

        Route::get('pengumuman-klaster', [PengumumanKlasterController::class, 'index'])
            ->name('litabmas.pengumuman-klaster.index');

        Route::get('pengumuman-klaster/{klaster_penelitian}', [PengumumanKlasterController::class, 'show'])
            ->name('litabmas.pengumuman-klaster.show');


        $router->livewireComponent('klaster-pendanaan/create', FormKlasterPendanaan::class)
            ->name('litabmas.klaster-pendanaan.create');
        $router->livewireComponent('klaster-pendanaan/{klaster_pendanaan}/edit', FormKlasterPendanaan::class)
            ->name('litabmas.klaster-pendanaan.edit');
        $router->resource(
            'jenis-output-penelitian',
            JenisOutputPenelitianController::class,
            withDestroySome: false,
        );
        $router->resource(
            'jenis-outcome-penelitian',
            JenisOutcomePenelitianController::class,
            withDestroySome: false,
            withSync: true
        );
        $router->resource(
            'bidang-ilmu',
            BidangIlmuController::class,
            withDestroySome: false,
            withSync: true
        );
        $router->resource(
            'tema-kegiatan',
            TemaKegiatanController::class,
            withDestroySome: false
        );
        $router->resource(
            'aspek-penilaian-isian-proposal',
            AspekPenilaianIsianProposalController::class,
            withDestroySome: false
        );
        $router->resource(
            'penilaian-komposisi-proposal',
            AspekPenilaianKomposisiProposalController::class,
            withDestroySome: false
        );
        $router->resource(
            'penilaian-presentasi-proposal',
            AspekPenilaianPresentasiProposalController::class,
            withDestroySome: false,
        );
        $router->resource(
            'aspek-penilaian-output',
            AspekPenilaianOutputController::class,
            withDestroySome: false,
        );
        $router->livewireComponent('aspek-penilaian-output/create', FormAspekPenilaianOutput::class)
            ->name('litabmas.aspek-penilaian-output.create');
        $router->livewireComponent('aspek-penilaian-output/{aspek_penilaian_output}/edit', FormAspekPenilaianOutput::class)
            ->name('litabmas.aspek-penilaian-output.edit');

        $router->resource(
            'penilaian-reviewer',
            PenilaianReviewerController::class,
            withDestroySome: false
        )->only(['index']);

        $router->livewireComponent('penilaian-reviewer/{pengajuan_pendanaan}/{sub_resource?}', FormDetailPenilaianReviewer::class)
            ->name('litabmas.penilaian-reviewer.show');

        /**
         * Daftar Bimbingan
         */
        Route::get('bimbingan', [BimbinganController::class, 'index'])->name('litabmas.bimbingan.index');

        $router->livewireComponent('bimbingan/{id_pengajuan_pendanaan}', FormDetailAktivitasPenelitian::class)
            ->name('litabmas.bimbingan.show');

        $router->livewireComponent('laporan-pendanaan-kegiatan', FilterLaporanPendanaanKegiatan::class)->name('laporan-pendanaan-kegiatan.filter');
        Route::post('laporan-pendanaan-kegiatan', [PendanaanKegiatan::class, 'gReport'])->name('laporan-pendanaan-kegiatan.generate');

        $router->livewireComponent('laporan-pengajuan-proposal', FilterLaporanPengajuanProposal::class)->name('laporan-pengajuan-proposal.filter');
        Route::post('laporan-pengajuan-proposal', [PengajuanProposal::class, 'gReport'])->name('laporan-pengajuan-proposal.generate');
    });
});
