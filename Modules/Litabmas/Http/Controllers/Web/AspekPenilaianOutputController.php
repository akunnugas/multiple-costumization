<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\AspekPenilaianOutputService;

class AspekPenilaianOutputController extends Controller
{
    protected int|null $idPeriodePendanaan;
    protected array $periodOptions;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AspekPenilaianOutputService $service)
    {
    }

    /**
     * Custom construct.
     * Karena jika ditaruh di __construct, maka tidak bisa mendapatkan dynamic config.
     * Karena lebih dulu __construct daripada middleware.
     *
     * @param Request $request
     * @return void
     */
    private function customConstruct(Request $request)
    {
        // ketika update maka ambil query string dari url sebelumnya
        if ($request->isMethod('put')) {
            $queryString = parse_url(url()->previous())['query'] ?? '';
            $params = [];
            parse_str($queryString, $params);
            $idPeriodePendanaan = $params['filter']['id_periode_pendanaan'] ?? null;
        } else {
            $idPeriodePendanaan = $request->input('filter.id_periode_pendanaan');
        }

        // [START] periode pendanaan
        $periodOptions = PeriodePendanaan::options();
        foreach ($periodOptions as $key => $value) {
            $periodOptions[$key] = 'Periode Pendanaan ' . $value;
        }

        // get periode pendanaan dari query string
        $invalidParam = !WebRequest::validateId($idPeriodePendanaan ?? null);
        if (empty($idPeriodePendanaan) || $invalidParam) { // handle default di halaman index pertama kali (sebelum milih filter)
            $idPeriodePendanaan = key($periodOptions);
        }

        // set attribute
        $this->periodOptions = $periodOptions;
        $this->idPeriodePendanaan = $idPeriodePendanaan;
        // [END] periode pendanaan
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $this->customConstruct($request);

        // overide permissions ketika periode pendanaan belum dipilih
        $permissions = request()->permission;
        if (empty($this->idPeriodePendanaan)) {
            $permissions['post'] = false;
            $permissions['put'] = false;
            $permissions['delete'] = false;
            $request->merge(['permission' => $permissions]);
        }

        $header = [
			['field' => 'pertanyaan_penilaian_output', 'label' => 'Pertanyaan Penilaian Luaran', 'component' => 'textarea_breakline'],
            ['field' => 'daftar_jawaban', 'component' => 'aspek_jawaban', 'label' => 'Jawaban Penilaian Luaran', 'searchable' => false]
        ];

        // set filter
        $filterList = [
            'id_periode_pendanaan' => [
                'options' => $this->periodOptions,
                'selected' => $this->idPeriodePendanaan,
                'label' => 'Periode Pendanaan',
            ],
        ];

        // set view data
        $staticMessage = 'Tentukan kriteria yang akan dinilai oleh reviewer terkait dengan hasil penelitian atau pengabdian yang dilakukan untuk pendukung penilaian LPPM selanjutnya.';
        $staticVariant = 'helper';
        if (empty($this->idPeriodePendanaan)) {
            $staticMessage = 'Tidak ada pilihan periode pendanaan, silahkan hubungi Administrator.';
            $staticVariant = 'warning';
        }

        $viewData = [
            'title' => 'Kriteria Penilaian Luaran',
            'sidebar' => Menu::masterSidebar('master-aspek-penilaian'),
            'showDeleteChecked' => false,
            'showNumber' => true,
            'staticAlert' => [
                'message' => $staticMessage,
                'variant' => $staticVariant
            ],
            'showDetail' => false,
            'canUpdate' => $permissions['put'],
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            filter: $filterList,
            viewData: $viewData,
            model: AspekPenilaianOutputPertanyaan::class,
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
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, AspekPenilaianOutputPertanyaan::class);
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
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'no'],
            ['field' => 'id_periode_pendanaan', 'label' => 'Periode Pendanaan'],
			['field' => 'pertanyaan_penilaian_output'],
        ];
    }
}
