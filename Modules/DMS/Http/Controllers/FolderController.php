<?php

namespace Modules\DMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\DMS\Models\Folder;
use Modules\DMS\Services\FolderManagementService;

class FolderController extends Controller
{
    public function __construct(
        private FolderManagementService $service,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('dms.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dms::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rootFolderId = $this->service->getUserRoot()?->id;

        if (!$rootFolderId) {
            return redirect()
                ->back()
                ->with('toast', [
                    'message' => 'Root folder DMS tidak ditemukan',
                    'icon' => 'exclamation-triangle-solid'
                ]);
        }

        // User created folder
        $data = [
            'id_unit_kerja' => $request->input('id_unit_kerja', null),
            'kode_folder' => Str::random(),
            'nama_folder' => $request->input('nama_folder'),
            'id_parent' => $request->input('id_parent', null) ?? $rootFolderId,
            'id_pemilik' => auth()->id(),
            'apakah_hanya_lihat' => false
        ];

        try {
            WebRequest::validateData($data, Folder::class);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('toast', [
                    'message' => $e->getMessage(),
                    'icon' => 'exclamation-triangle-solid'
                ]);
        }

        $folder = $this->service->store($data);

        if ($folder instanceof Error) {
            abort($folder->code);
        }

        return redirect()
            ->back()
            ->with('toast', [
                'message' => 'Folder berhasil dibuat',
                'action' => [
                    'label' => 'Buka Folder',
                    'href' => route('dms.folder.show', $folder->kode_folder)
                ],
                'icon' => 'check-circle-solid'
            ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id, Request $request)
    {
        $folder = $this->service->showByCode($id);
        if ($folder instanceof Error) {
            abort($folder->code);
        }

        $data = WebRequest::indexData(
            $this->service,
            Folder::class,
            $this->service->defineFields(),
            [
                'id_folder' => ['selected' => $folder->id],
                ...$this->service->getFilter()
            ],
            $request->page,
            $request->perPage,
            $request->sort,
            $request->sortDesc,
            $request->filter,
            null,
            'indexWithDokumen'
        );

        unset($data['filter']['id_folder']);
        unset($data['filter']['id_parent']);

        $canManage = $this->service->authorizeFolderPermission($folder);

        if ($canManage) {
            $organizations = $this->service->getOrganizationsForSelect();
            $folders = $this->service->getFolders();
        }

        return view('dms::pages.folder.show', [
            'title' => $folder->nama_folder,
            'folder' => $folder,
            'canCreate' => $canManage,
            'canDelete' => $canManage,
            'organizations' => $organizations ?? null,
            'folders' => $folders ?? null,
            ...$data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('dms::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $folder = $this->service->showByCode($id);
        if ($folder instanceof Error) {
            abort($folder->code);
        }

        return WebController::destroy($this->service, $id);
    }
}
