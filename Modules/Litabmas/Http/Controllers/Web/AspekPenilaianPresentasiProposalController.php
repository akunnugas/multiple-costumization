<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\AspekPenilaianPresentasiProposalService;

class AspekPenilaianPresentasiProposalController extends Controller
{
    protected int|null $idPeriodePendanaan;
    protected string $kodeJenisPendanaan;
    protected array $periodOptions;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AspekPenilaianPresentasiProposalService $service)
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
    private function customCustruct(Request $request)
    {
        // ketika update maka ambil query string dari url sebelumnya
        if ($request->isMethod('put')) {
            $queryString = parse_url(url()->previous())['query'] ?? '';
            $params = [];
            parse_str($queryString, $params);
            $idPeriodePendanaan = $params['filter']['id_periode_pendanaan'] ?? null;
            $kodeJenisPendanaan = $params['tab'] ?? null;
        } else {
            $idPeriodePendanaan = $request->input('filter.id_periode_pendanaan');
            $kodeJenisPendanaan = $request->input('tab');
        }

        // [START] jenis pendanaan
        $allowedTab = JenisPendanaanEnum::CODES;
        if (empty($kodeJenisPendanaan) || !array_key_exists($kodeJenisPendanaan, $allowedTab)) { // klo kosong | tidak valid, set default
            $kodeJenisPendanaan = JenisPendanaanEnum::CODE_PENELITIAN;
        }

        // set attribute
        $this->kodeJenisPendanaan = $kodeJenisPendanaan;
        // [END] jenis pendanaan

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
        $this->customCustruct($request);

        // overide permissions ketika periode pendanaan belum dipilih
        $permissions = request()->permission;
        if (empty($this->idPeriodePendanaan)) {
            $permissions['post'] = false;
            $permissions['put'] = false;
            $permissions['delete'] = false;
            $request->merge(['permission' => $permissions]);
        }

        $header = [
            ['field' => 'no', 'type' => 'number', 'data-number-max' => 100],
            ['field' => 'pertanyaan_presentasi_proposal', 'label' => 'Pertanyaan Penilaian Presentasi', 'control' => 'textarea', 'component' => 'textarea_breakline'],
			['field' => 'bobot_pertanyaan_presentasi_proposal', 'label' => 'Bobot Penilaian (%)',
                'type' => 'number', 'data-number-max' => 100, 'data-number-float' => true,
                'styleAlign' => 'right'
            ],
        ];

        // set jenis pendanaan (default filter)
        $request->merge(['filter' => ['kode_jenis_pendanaan' => $this->kodeJenisPendanaan]]);

        // set filter
        $filterList = [
            'id_periode_pendanaan' => [
                'options' => $this->periodOptions,
                'selected' => $this->idPeriodePendanaan,
                'label' => 'Periode Pendanaan',
            ],
        ];

        // set view data
        $staticMessage = 'Tentukan kriteria yang akan dinilai dalam presentasi proposal penelitian sebagai pendukung seleksi selanjutnya.';
        $staticVariant = 'helper';
        if (empty($this->idPeriodePendanaan)) {
            $staticMessage = 'Tidak ada pilihan periode pendanaan, silahkan hubungi Administrator.';
            $staticVariant = 'warning';
        }

        $viewData = [
            'title' => 'Kriteria Penilaian Presentasi',
            'sidebar' => Menu::masterSidebar('master-aspek-penilaian'),
            'navTab' => Menu::navTabs('penilaian-presentasi-proposal-by-jenis-pendanaan'),
            'showDeleteChecked' => false,
            'staticAlert' => [
                'message' => $staticMessage,
                'variant' => $staticVariant
            ]
        ];

        // set active nav tab berdasarkan tab yang dipilih
        $activePath = 'penilaian-presentasi-proposal?tab=' . $this->kodeJenisPendanaan;
        $viewData['navTab']['activePath'] = $activePath;

        return WebController::index(
            $this->service,
            $request,
            $header,
            filter: $filterList,
            viewData: $viewData,
            model: AspekPenilaianPresentasiProposal::class,
            isReference: true,
            isUsingDefaultOrder: false
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $this->customCustruct($request);

        // set jenis pendanaan
        $request->merge(['kode_jenis_pendanaan' => $this->kodeJenisPendanaan]);

        // set periode pendanaan
        $request->merge(['id_periode_pendanaan' => $this->idPeriodePendanaan]);

        // jika ada parameter create, maka hapus
        $currentUrl = WebRequest::removeQueryParam(url()->previous(), 'create');

        return WebController::store(
            $this->service,
            $request,
            $this->defineFormFields(),
            AspekPenilaianPresentasiProposal::class,
            isReference: true,
            successURL: $currentUrl
        );
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $this->customCustruct($request);

        // set jenis pendanaan
        $request->merge(['kode_jenis_pendanaan' => $this->kodeJenisPendanaan]);

        // set periode pendanaan
        $request->merge(['id_periode_pendanaan' => $this->idPeriodePendanaan]);

        // jika ada parameter edit, maka hapus
        $currentUrl = WebRequest::removeQueryParam(url()->previous(), 'edit');

        return WebController::update(
            $this->service,
            $id,
            $request,
            $this->defineFormFields(),
            AspekPenilaianPresentasiProposal::class,
            isReference: true,
            successURL: $currentUrl
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
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'no'],
            ['field' => 'pertanyaan_presentasi_proposal'],
			['field' => 'bobot_pertanyaan_presentasi_proposal'],
            ['field' => 'kode_jenis_pendanaan', 'type' => 'hidden'],
            ['field' => 'id_periode_pendanaan', 'type' => 'hidden'],
        ];
    }
}
