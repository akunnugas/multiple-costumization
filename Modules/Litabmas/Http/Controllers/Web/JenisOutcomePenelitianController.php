<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Modules\Litabmas\Models\JenisPublikasi;
use Modules\Litabmas\Services\JenisOutcomePenelitianService;
use Modules\Litabmas\Services\JenisOutputPenelitianService;

class JenisOutcomePenelitianController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JenisOutcomePenelitianService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
			['field' => 'nama_outcome'],
            ['field' => 'nama_jenis_publikasi', 'name' => 'id_jenis_publikasi', 'options' => JenisPublikasi::class,
                'label' => __('litabmas::jenis_outcome_penelitian.id_jenis_publikasi')],
            ['field' => 'kategori_outcome', 'searchable' => false],
            ['field' => 'batas_pengumpulan_outcome', 'searchable' => false]
        ];

        $filter = [
            'kategori_outcome' => [
                'options' => ['' => '-- Semua ' . __('litabmas::jenis_outcome_penelitian.kategori_outcome') . ' --'] + JenisOutcomePenelitian::CATEGORIES,
                'hideLabel' => true,
            ],
        ];

        $viewData = [
            'title' => 'Outcome',
            'showDeleteChecked' => false,
            'showNumber' => true,
            'withSync' => true,
            'navTab' => Menu::navTabs('final-result-activity'),
            'staticAlert' => [
                'message' => 'Masukkan hasil akhir kegiatan berupa luaran serta outcome sebagai referensi yang akan digunakan pada penentuan klaster.',
            ],
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            filter: $filter,
            viewData: $viewData,
            model: JenisOutcomePenelitian::class,
            isReference: true
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JenisOutcomePenelitian::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JenisOutcomePenelitian::class, isReference: true);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return WebController::destroy($this->service, $id);
    }

    /**
     * Sync From Siakad v1
     *
     * @return Renderable
     */
    public function sync()
    {
        return WebController::sync($this->service, 'jenisPublikasiFromHRSiakadV1');
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'nama_outcome'],
            ['field' => 'id_jenis_publikasi'],
            ['field' => 'kategori_outcome'],
            ['field' => 'batas_pengumpulan_outcome']
        ];
    }
}
