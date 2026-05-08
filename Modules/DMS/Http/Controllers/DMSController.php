<?php

namespace Modules\DMS\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\DMS\Services\DokumenManagementService;
use Modules\DMS\Services\FolderManagementService;

class DMSController extends Controller
{
    public function __construct(private FolderManagementService $folderService, private DokumenManagementService $dokumenService)
    {
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('dms::pages.dashboard.index');
    }
}
