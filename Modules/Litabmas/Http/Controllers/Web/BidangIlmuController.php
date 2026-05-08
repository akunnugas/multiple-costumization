<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\BidangIlmu;
use Modules\Litabmas\Models\Cache\BidangIlmuCache;
use Modules\Litabmas\Services\BidangIlmuService;

class BidangIlmuController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private BidangIlmuService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        // overide permissions to false, except get
        $permissions = request()->permission;
        $permissions['post'] = false;
        $permissions['put'] = false;
        $permissions['delete'] = false;
        $request->merge(['permission' => $permissions]);

        $viewData = [
            'title' => 'Bidang Ilmu',
            'showDeleteChecked' => false,
            'showNumber' => true,
            'withSync' => true,
            'syncMessage' => 'Apakah Anda yakin mensinkronisasi data bidang ilmu dari modul kepegawaian?',
            'staticAlert' => [
                'message' => 'Silakan Sinkroniasasi Data Referensi Bidang ilmu atau ruang lingkup pengetahuan tertentu yang menjadi fokus kegiatan penelitian dan pengabdian masyarakat.'
            ]
        ];

        return WebController::index(
            $this->service,
            $request,
            $this->defineFormFields(),
            viewData: $viewData,
            model: BidangIlmu::class,
            isReference: true
        );
    }

    /**
     * Sync From Siakad v1
     *
     * @return Renderable
     */
    public function sync()
    {
        $view = WebController::sync($this->service, 'syncBidangIlmuFromHRSiakadV1');

        BidangIlmuCache::destroyCustomKey(BidangIlmuCache::KEY_OPTIONS);

        return $view;
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'nama_bidang_ilmu'],
        ];
    }
}
