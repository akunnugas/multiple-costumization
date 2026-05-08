<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Wilayah;
use Modules\Kerjasama\Enums\JenisMitra;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Helpers\MitraImport;
use Modules\Kerjasama\Models\KriteriaMitra;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Services\MitraManagementService;
use Modules\Kerjasama\Helpers\ExportFormat;
use Modules\Kerjasama\Models\JenisDokumen;

class MitraController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private MitraManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'jenis_mitra', 'options' => JenisMitra::getOptions()],
            ['field' => 'nama_mitra'],
            ['field' => 'tingkat_mitra'],
            ['field' => 'id_kriteria_mitra', 'searchable' => false, 'options' => []],
            ['field' => 'telepon'],
            ['field' => 'email'],
        ];

        $viewData = [
            'withExport' => true,
        ];


        return WebController::index(
            $this->service,
            $request,
            $header,
            $this->defineFilter(),
            $viewData,
            model: Mitra::class,
            isUsingDefaultOrder: false
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $data = $this->service->show($id);
        if (Error::isError($data)) {
            return $data->redirectBack();
        }

        $viewData = [
            'sidebar' => Menu::mitraSidebar($id)
        ];

        $cards = $this->hiddenFields($id);
        if (isset($cards['informasi-mitra']) && isset($data['nama_mitra'])) {
            $cards['informasi-mitra']['title'] = $data['nama_mitra'];
            $cards['informasi-mitra']['subtitle'] = 'Detail informasi terkait data mitra dan kontak';
        }

        return WebController::show(
            $this->service,
            $id,
            $cards,
            Mitra::class,
            $viewData,
            data: $data
        );
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
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->service, $request);
    }

    public function getOptionWilayah($level, $id)
    {
        $optionsProvinsi = Wilayah::optionsByLevel($level, $id);
        return response()->json($optionsProvinsi);
    }

    public function export(Request $request)
    {
        $header = [
            ['field' => 'jenis_mitra', 'options' => JenisMitra::getOptions()],
            ['field' => 'nama_mitra'],
            ['field' => 'id_kriteria_mitra'],
            ['field' => 'kode_mitra'],
            ['field' => 'npwp_mitra'],
            ['field' => 'tingkat_mitra'],
            ['field' => 'id_negara'],
            ['field' => 'id_provinsi'],
            ['field' => 'id_kota'],
            ['field' => 'id_kecamatan'],
            ['field' => 'kode_pos'],
            ['field' => 'alamat'],
            ['field' => 'email'],
            ['field' => 'telepon'],
            ['field' => 'website'],
        ];


        return WebController::export('Export Data Mitra.xlsx', $this->service, $request, $header, $this->defineFilter(), Mitra::class);
    }


    protected function defineFormFields(): array
    {
        return [
            'informasi-mitra' => [
                'title' => 'Mitra',
                'subtitle' => 'Informasi Mitra',
                'icon' => 'building-02',
                'edit_url' => url()->current() . '/edit',
                'items' => [
                    ['field' => 'jenis_mitra', 'options' => JenisMitra::getOptions()],
                    ['field' => 'nama_mitra'],
                    ['field' => 'kode_mitra'],
                    ['field' => 'npwp_mitra'],
                    ['field' => 'id_kriteria_mitra'],
                    ['field' => 'tingkat_mitra'],

                    ['field' => 'id_negara'],
                    ['field' => 'id_provinsi'],
                    ['field' => 'id_kota'],
                    ['field' => 'id_kecamatan'],
                    ['field' => 'kode_pos'],
                    ['field' => 'alamat'],

                    ['field' => 'telepon'],
                    ['field' => 'email'],
                    ['field' => 'website', 'dynamicComponent' => 'kerjasama::fields.link_dokumentasi'],
                ]
            ],
            '_' => [
                'title' => '_',
                'items' => [
                    ['field' => '_']
                ]
            ]
        ];
    }

    protected function defineFilter(): array
    {
        return [
            'jenis_mitra' => [
                'options' => ['' => '-- Semua Jenis Mitra --'] + JenisMitra::getOptions(),
                'hideLabel' => true,
            ],
            'tingkat_mitra' => [
                'options' => ['' => '-- Semua Lingkup Mitra --'] + Mitra::LEVELS,
                'hideLabel' => true,
            ],
            'id_kriteria_mitra' => [
                'options' => ['' => '-- Semua Kriteria Mitra --'] + KriteriaMitra::options(),
                'hideLabel' => true,
            ]
        ];
    }

    protected function hiddenFields($id)
    {
        $cardBaseKey = 'informasi-mitra';
        $formFields = $this->defineFormFields();
        $hidefieldEdit = [
            Mitra::LEVEL_INTERNASIONAL => ['id_provinsi', 'id_kota', 'id_kecamatan'],
            Mitra::LEVEL_NASIONAL => ['id_negara'],
            Mitra::LEVEL_REGIONAL => ['id_negara'],
            Mitra::LEVEL_LOKAL => ['id_negara'],

        ];

        $tingkatMitra = $this->service->getTingkatMitraById($id);
        $fieldsToHide = $hidefieldEdit[$tingkatMitra] ?? [];

        foreach ($formFields as $sectionId => $section) {
            if ($sectionId == $cardBaseKey) {
                foreach ($section['items'] as $key => $field) {
                    if (in_array($field['field'], $fieldsToHide)) {
                        $formFields[$sectionId]['items'][$key]['type'] = 'hidden';
                    }
                }
            }
        }
        return $formFields;
    }
}
