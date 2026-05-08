<?php

namespace Modules\DMS\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error as ErrorHelper;
use Modules\DMS\Services\DokumenManagementService;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Services\FolderManagementService;
use Modules\Gate\Models\Modul;

// Non Resource Controller
class FileController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private DokumenManagementService $service, private FolderManagementService $folderService)
    {
    }

    public function store(Request $request)
    {
        $data = $request->only(['nama_dokumen', 'file', 'kode_folder']);

        $folder = $this->folderService->showByCode($data['kode_folder']);
        if ($folder instanceof ErrorHelper) {
            abort($folder->kode_folder);
        }

        $data['kode_modul'] = Modul::CODE_DMS;
        $data['visibilitas'] = Dokumen::VISIBILITY_PRIVATE;
        $data = WebRequest::sanitizeXSS($data, Dokumen::class);

        WebRequest::validateData($data, Dokumen::class);

        $dokumen = $this->service->store($data);

        if ($dokumen instanceof ErrorHelper) {
            abort($dokumen->code);
        }

        return redirect()
            ->back()
            ->with('toast', [
                'message' => 'File berhasil diupload',
                'destination' => route('dms.files.preview', $dokumen->slug),
                'icon' => 'check-circle-solid'
            ]);
    }

    /**
     * Download the specified resource from storage.
     * @param string $id
     * @param Request $request
     *
     * @return Renderable
     */
    public function download($id, Request $request)
    {
        $version = $request->query('version', null);
        $response = $this->service->showPath($id, $version);

        if ($response instanceof ErrorHelper) {
            abort($response->code);
        }

        return Storage::download($response);
    }

    /**
     * Show raw the specified Dokumen storage.
     * @param string $id
     * @param Request $request
     *
     * @return Renderable
     */
    public function raw($id, Request $request)
    {
        $version = $request->query('version', null);
        $response = $this->service->showPath($id, $version);

        if ($response instanceof ErrorHelper) {
            abort($response->code);
        }

        return Storage::response($response);
    }

    /**
     * Show preview the specified Dokumen storage.
     * @param string $slug
     *
     * @return Renderable
     */
    public function preview(string $slug)
    {
        $model = $this->service->showBySlug($slug);

        if ($model instanceof ErrorHelper) {
            abort($model->code);
        }

        return view('dms::pages.files.show', [
            'Dokumen' => $model
        ]);
    }

    public function update($id, Request $request) {
        if (!WebRequest::validateId($id)) {
            abort(404);
        };

        $model = $this->service->show($id);
        $permission = $this->service->authorizeDokumenManagementPermission($model);

        if ($permission instanceof ErrorHelper) {
            abort($permission->code);
        }

        $data = $request->only(['visibilitas', 'id_folder']);
        $data = WebRequest::sanitizeXSS($data, Dokumen::class);

        WebRequest::validateData($data, Dokumen::class, $id);

        $response = $this->service->update($data, $id);

        if ($response instanceof ErrorHelper) {
            abort($response->code);
        }

        if ($request->wantsJson()) {
            return response()->json(['toast' => 'File berhasil diupdate']);
        }

        $toast = [
            'message' => 'File berhasil diupdate',
            'icon' => 'check-circle-solid'
        ];

        if ($data['id_folder']) {
            return redirect()
                ->route('dms.folder.show', $response->folder->kode_folder)
                ->with('toast', $toast);
        }

        return redirect()
            ->back()
            ->with('toast', $toast);
    }

    public function destroy($id) {
        return WebController::destroy($this->service, $id);
    }

    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->service, $request);
    }
}
