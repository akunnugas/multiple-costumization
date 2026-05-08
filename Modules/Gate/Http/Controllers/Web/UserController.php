<?php

namespace Modules\Gate\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Services\UserManagementService;

class UserController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private UserManagementService $service)
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
            ['field' => 'nama_user'],
            ['field' => 'email_user'],
            ['field' => 'id_role', 'sortable' => false, 'searchable' => false],
            ['field' => 'email_terverifikasi', 'component' => true, 'searchable' => false],
        ];
        $filter = [
            'roles' => ['options' => Role::class],
        ];

        return WebController::index($this->service, $request, $header, $filter);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), User::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), User::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return WebController::show($this->service, $id, model: User::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), User::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), User::class);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'nama_user'],
            ['field' => 'email_user'],
            ['field' => 'telepon_user'],
        ];
    }
}
