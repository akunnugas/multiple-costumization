<?php

namespace Modules\Admission\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Services\WilayahManagementService;

class KotaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private WilayahManagementService $service)
    {
    }

    public function search(Request $request)
    {
        $provinceId = $request->input('province_id');

        if (empty($provinceId)) {
            return response()->json(['message' => __('admission::registration.province_id') . ' is required'], 400);
        }

        if (!WebRequest::validateId($provinceId)) {
            return response()->json(['message' => 'Invalid ' . __('admission::registration.province_id')], 400);
        }

        $filter = [
            ['field' => 'parent_id', 'value' => $provinceId, 'operator' => '=']
        ];

        $data = $this->service->indexCities(filter: $filter);

        return response()->json($data->items);
    }
}
