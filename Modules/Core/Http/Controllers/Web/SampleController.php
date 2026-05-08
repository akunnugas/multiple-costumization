<?php

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\Pagination;

class SampleController extends Controller
{
    // bisa menggunakan View::share pada module service provider
    private $menu = [
        ['label' => 'Home', 'path' => '/', 'permission' => true],
        ['label' => 'List', 'items' => [
            ['label' => 'List', 'path' => 'list'],
            ['label' => 'List with Sidebar', 'path' => 'list/sidebar']
        ]],
        ['label' => 'Create', 'items' => [
            ['label' => 'Create', 'path' => 'create'],
            ['label' => 'Advanced Create', 'path' => 'create/advanced']
        ]],
        ['label' => 'Detail', 'path' => 'detail'],
    ];

    public function home()
    {
        return view('core::pages.temp.home');
    }

    public function index(Request $request)
    {
        return view('core::pages.sample.index')
            ->withMenu(Page::defineMenu('sample', $this->menu))
            ->withHeader($this->getIndexHeader())
            ->withData($this->getIndexData($request->page, $request->perPage));
    }

    public function indexWithSidebar(Request $request)
    {
        $submenu = [
            ['label' => 'List', 'items' => [
                ['label' => 'List', 'path' => 'resource-1'],
                ['label' => 'List with Sidebar', 'path' => 'resource-2'],
            ]]
        ];

        return view('core::pages.sample.index')
            ->withMenu(Page::defineMenu('sample', $this->menu))
            ->withSubmenu(Page::defineMenu('sample', $submenu))
            ->withHeader($this->getIndexHeader())
            ->withData($this->getIndexData($request->page, $request->perPage));
    }

    public function create()
    {
        return view('core::pages.sample.create')
            ->withMenu(Page::defineMenu('sample', $this->menu))
            ->withData([
                ['field' => 'nim', 'label' => __('core::sample.nim'), 'maxlength' => 20]
            ]);
    }

    public function createAdvanced()
    {
        return view('core::pages.sample.create')
            ->withMenu(Page::defineMenu('sample', $this->menu))
            ->withData([
                ['title' => 'Data Mahasiswa', 'subtitle' => 'Informasi umum mahasiswa', 'items' => [
                    ['field' => 'nim', 'label' => __('core::sample.nim'), 'maxlength' => 20]
                ]]
            ]);
    }

    public function detail()
    {
        $submenu = [];

        return view('core::pages.sample.detail')
            ->withMenu(Page::defineMenu('sample', $this->menu))
            ->withSubmenu(Page::defineMenu('sample', $submenu))
            ->withData([
                ['title' => 'Informasi Pendaftaran', 'subtitle' => 'Informasi pendaftaran mahasiswa', 'items' => [
                    ['label' => __('core::sample.sistem_kuliah'), 'text' => 'Kelas Karyawan'],
                    ['label' => __('core::sample.periode_masuk'), 'text' => '2023/2024']
                ]]
            ]);
    }

    private function getIndexHeader()
    {
        return [
            ['field' => 'name', 'label' => __('gate::users.name')],
            ['field' => 'email', 'label' => __('gate::users.email')],
            ['field' => 'role_name', 'label' => __('gate::roles.name')],
            ['field' => 'unit_name', 'label' => __('core::organizations.name')],
        ];
    }

    private function getIndexData($page, $perPage)
    {
        $sql = "select u.name, u.email, r.name as role_name, o.name as unit_name
                from gate.users u
                join gate.user_roles ur on ur.user_id = u.id and ur.waktu_dihapus is null
                join gate.roles r on r.id = ur.role_id and r.waktu_dihapus is null
                join core.organizations o on o.id = ur.organization_id and o.waktu_dihapus is null
                where u.waktu_dihapus is null";

        return Pagination::create($sql, page: $page, perPage: $perPage);
    }

    // [Slicing]
    public function frontendDetail()
    {
        // Detail Page
        return view('core::pages.sample.frontend.index');
    }
    public function frontendTab()
    {
        // Tab Page
        return view('core::pages.sample.frontend.tab');
    }

    public function frontendForm()
    {
        // Form Page
        return view('core::pages.sample.frontend.form');
    }
}
