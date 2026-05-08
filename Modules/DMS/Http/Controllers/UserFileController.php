<?php

namespace Modules\DMS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebRequest;
use Modules\DMS\Models\Folder;
use Modules\DMS\Services\DokumenManagementService;
use Modules\DMS\Services\FolderManagementService;

class UserFileController extends Controller
{
    public function __construct(private FolderManagementService $folderService, private DokumenManagementService $dokumenService)
    {
    }

    public function index(Request $request) {
        $data = WebRequest::indexData(
            $this->folderService,
            Folder::class,
            $this->folderService->defineFields(withSize: false),
            $this->folderService->getFilter(),
            $request->page,
            $request->perPage,
            $request->sort,
            $request->sortDesc,
            $request->filter,
            null,
            'myFilesIndex'
        );

        $organizations = $this->folderService
            ->getOrganizationsForSelect();

        return view('dms::pages.folder.show', [
            'title' => __('dms::pages.my_files'),
            'canCreate' => true,
            'canDelete' => true,
            'organizations' => $organizations,
            ...$data
        ]);
    }

    public function destroySome(Request $request) {
        $data = array_map(fn($str) => explode(',', $str), $request->group);

        $fileIds = array_map(fn($arr) => $arr[0], array_filter($data, fn($arr) => $arr[1] === 'file'));
        $folderIds = array_map(fn($arr) => $arr[0], array_filter($data, fn($arr) => $arr[1] === 'folder'));

        foreach ([...$fileIds, ...$folderIds] as $id) {
            WebRequest::validateId($id);
        }

        $return = $this->folderService->destroySome($folderIds);
        if (Error::isError($return)) {
            return $return->redirectBack();
        }

        $return = $this->DokumenService->destroySome($fileIds);
        if (Error::isError($return)) {
            return $return->redirectBack();
        }

        return back()->withSuccess('Berhasil');
    }
}
