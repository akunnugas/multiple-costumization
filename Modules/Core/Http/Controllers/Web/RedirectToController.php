<?php 
namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\RedirectToManagementService;

class RedirectToController extends Controller
{

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private RedirectToManagementService $service)
    {
    }

    public function redirectTo(Request $request)
    {
        // send request to view
        $data = $request->all();
        return redirect()->back()->with($data);
    }

}