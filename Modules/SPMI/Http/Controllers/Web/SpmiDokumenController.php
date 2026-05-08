<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Models\SpmiDokumen;
use Modules\SPMI\Services\SpmiDokumenManagementService;

class SpmiDokumenController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SpmiDokumenManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'kode_spmi_dokumen', 'sortable' => true],
            ['field' => 'nama_spmi_dokumen', 'sortable' => true, 'width' => '70%', 'component' => true],
            ['field' => 'versi', 'sortable' => true, 'width' => '10%'],
            ['field' => 'waktu_diubah', 'sortable' => true, 'width' => '20%', 'component' => true],
            ['field' => 'apakah_aktif', 'component' => true, 'width' => '10%'],
        ];

        $data = $this->service->indexByType($sort ?? []);
        usort($data, function ($a, $b) {
            $order = [
                'Kebijakan' => 1,
                'Pedoman' => 2,
                'Standar' => 3,
                'Tata cara' => 4,
            ];

            $a_type = strtok($a['nama_spmi_jenis_dokumen'], ' ');
            $b_type = strtok($b['nama_spmi_jenis_dokumen'], ' ');

            return ($order[$a_type] ?? 99) <=> ($order[$b_type] ?? 99);
        });

        return view('spmi::pages.spmi-dokumen.index', [
            'header' => $header,
            'data' => $data,
            'sort' => $viewSort ?? []
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $request->validate([
            'id_jenis' => 'numeric'
        ]);

        $qualityType = $request->id_jenis ? $this->service->showTypeDocument((int) $request->id_jenis) : null;

        return WebController::create($this->defineFormFields($qualityType), SpmiDokumen::class)
            ->with('qualityType', $qualityType);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $qualityType = $request->id_jenis ? $this->service->showTypeDocument((int) $request->id_jenis) : null;
        return WebController::store($this->service, $request, $this->defineFormFields($qualityType), SpmiDokumen::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        $qualityDocument = $this->service->show($id);

        $qualityType = $this->service->showTypeDocument((int) $qualityDocument->id_jenis);
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, SpmiDokumen::class, $viewData)
            ->with('qualityType', $qualityType);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $qualityDocument = $this->service->show($id);

        $qualityType = $this->service->showTypeDocument((int) $qualityDocument->id_jenis);

        $view = WebController::edit($this->service, $id, $this->defineFormFields($qualityType), SpmiDokumen::class)
            ->with('qualityType', $qualityType);

        return $view;
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $qualityType = $request->id_jenis ? $this->service->showTypeDocument((int) $request->id_jenis) : null;

        return WebController::update($this->service, $id, $request, $this->defineFormFields($qualityType), SpmiDokumen::class);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $qualityDocument = $this->service->show($id);

        $qualityType = $this->service->showTypeDocument((int) $qualityDocument->id_jenis);

        return WebController::destroy($this->service, $id, 'Berhasil mengapus dokumen ' . $qualityType->nama_spmi_jenis_dokumen);
    }

    /**
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->service, $request);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields($qualityType = null)
    {
        $fields = [
            ['field' => 'kode_spmi_dokumen'],
            ['field' => 'nama_spmi_dokumen'],
            [
                'field' => 'versi',
                'type' => 'number',
                'onKeyPress' => 'if(this.value.length==5) return false;',
                'step' => '0.1',
                'oninput' => "this.value = this.value.replace(/[+\-e]/gi, '')"
            ],
            ['field' => 'deskripsi', 'control' => 'textarea'],
            [
                'field' => 'apakah_aktif',
                'control' => 'radio',
                'options' => [1 => 'Berlaku', 0 => 'Tidak Berlaku'],
                'selected' => 1
            ],
            ['field' => 'tanggal_awal_berlaku'],
            ['field' => 'tanggal_akhir_berlaku'],
            ['field' => 'id_dokumen']
        ];

        if (!isset($qualityType)) {
            array_unshift($fields, ['field' => 'id_jenis']);
        } else {
            array_unshift($fields, ['field' => 'id_jenis', 'type' => 'hidden', 'selected' => $qualityType->id]);
        }

        return $fields;
    }
}
