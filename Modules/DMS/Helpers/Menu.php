<?php

namespace Modules\DMS\Helpers;

use Illuminate\Support\Facades\Auth;
use Modules\DMS\Services\FolderManagementService;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar(): array
    {
        return [
            ['label' => 'Beranda', 'path' => '/'],
            ['label' => 'DMS', 'path' => 'folder'],
        ];
    }

    /**
     * Sidebar untuk mobile.
     *
     * @return array[]
     */
    public static function mobileSidebar()
    {
        $sidebar = [
            [
                'label' => __('dms::pages.sevima_platform'),
                'path' => '/',
                'icon' => 'server-stack-solid',
            ],
            [
                'label' => __('dms::pages.my_files'),
                'path' => 'user-files',
                'icon' => 'server-stack-solid',
            ],
            [
                'label' => __('dms::pages.trash'),
                'path' => 'trash',
                'icon' => 'trash-solid',
            ],
        ];

        return [
            'items' => [
                [
                    'items' => $sidebar
                ]
            ]
        ];
    }

    public static function modules() {
        $service = new FolderManagementService();
        $parents = $service->getParents(true);

        return $parents->map(function ($folder) {
            return [
                'label' => $folder->nama_folder,
                'path' => route('dms.folder.show', $folder->kode_folder),
                'icon' => '/images/folder-solid.svg',
                'files' => $folder->dokumen_count
            ];
        });
    }
}
