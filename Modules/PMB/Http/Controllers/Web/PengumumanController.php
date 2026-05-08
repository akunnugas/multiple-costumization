<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Core\Helpers\WebController;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\Pengumuman;
use Modules\PMB\Models\PengumumanFile;
use Modules\PMB\Services\PengumumanManagementService;

class PengumumanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PengumumanManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'title'],
			['field' => 'link'],
			['field' => 'description'],
			['field' => 'type', 'component' => 'announcement_type', 'searchable' => false],
			['field' => 'is_active'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('reference-announcement');

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $fields = $this->defineFormFields();

        // remove link from $fields
        foreach ($fields as $key => $field) {
            if ($field['field'] == 'link') {
                unset($fields[$key]);
                break;
            }
        }

        return WebController::create($fields, Pengumuman::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $fields = $this->defineFormFields();

        // set link to url from title
        $request->merge(['link' => Str::slug($request->title)]);

        return WebController::store($this->service, $request, $fields, Pengumuman::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();
        unset($cards[0]);

        return WebController::show($this->service, $id, $cards, Pengumuman::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), Pengumuman::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $fields = $this->defineFormFields();

        // set link to url from title
        $request->merge(['link' => Str::slug($request->link)]);

        return WebController::update($this->service, $id, $request, $fields, Pengumuman::class);
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
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->service, $request);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'image_id', 'type' => 'file', 'image_placement' => 'top'],
            ['field' => 'title'],
			['field' => 'type'],
			['field' => 'description', 'control' => 'textarea'],
			['field' => 'link'],
			['field' => 'is_active', 'control' => 'switch'],
            ['field' => 'attachments', 'type' => 'file', 'max_size' => 1024 * 2, 'multiple' => true,
                'file_type' => PengumumanFile::ACCEPTED_TYPES
            ],
        ];
    }
}
