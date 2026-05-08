<?php

namespace Modules\Admission\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Services\SekolahManagementService;

class SekolahController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SekolahManagementService $service)
    {
    }

    public function search(Request $request)
    {
        $cityId = $request->input('city_id');

        if (empty($cityId)) {
            return response()->json(['message' => __('admission::registration.city_id') . ' is required'], 400);
        }

        if (!WebRequest::validateId($cityId)) {
            return response()->json(['message' => 'Invalid ' . __('admission::registration.city_id')], 400);
        }

        $order = [
            ['field' => 'name', 'direction' => 'asc'],
        ];

        $filter = [
            ['field' => 'city_id', 'value' => $cityId, 'operator' => '='],
        ];

        $data = $this->service->index(order: $order, filter: $filter);

        return response()->json($data->items);
    }
}
