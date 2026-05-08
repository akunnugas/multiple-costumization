<?php

namespace Modules\DMS\Tests\Service;

use Modules\DMS\Models\Folder;
use Modules\DMS\Services\FolderManagementService;
use Modules\Gate\Models\User;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class FolderManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = FolderManagementService::class;
    protected $auth;

    protected function prepare(): void
    {
        // Setup auth
        $user = User::factory()->create();
        $this->auth = $this->actingAs($user);
    }

    /**
     * Test index to retrieve data
     */
    public function test_index_return_data()
    {
        $this->indexReturnData(new Folder);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Folder, 'nama_dokumen');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Folder);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Folder);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Folder);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Folder, [
            [
                'key' => 'nama_dokumen',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Folder);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Folder);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Folder);
    }

    /**
     * Test folder tree
     */
    public function test_folder_tree_hierarchy_is_valid()
    {
        // Buat root folder dulu
        $root = Folder::factory()->create();

        // Buat dummy children level 1 dari root
        $childrenFirstLevel = Folder::factory(5)->create([
            'parent_id' => $root->id
        ]);

        // Buat dummy children level 2 dari parent level 1
        $childrenSecondLevel = Folder::factory(5)->create([
            'parent_id' => $childrenFirstLevel->first()->id
        ]);

        $this->assertTrue($root->depth === 0);
        $this->assertTrue($childrenFirstLevel->first()->depth === $root->depth + 1);
        $this->assertTrue(
            $childrenSecondLevel->first()->depth === $childrenFirstLevel->first()->depth + 1
        );
    }

    /**
     * Test show folder items
     */
    public function test_show_items()
    {
        // Buat root folder dulu
        $root = Folder::factory()->create();

        // Buat dummy children folder level 1
        $children = Folder::factory(5)->create([
            'parent_id' => $root->id
        ]);

        // Buat dummy children folder level 2
        Folder::factory(5)->create([
            'parent_id' => $children->first()->id
        ]);

        $data = $this->service->showItems($root->id, false);

        // Harusnya return 5 karena yang diambil hanya children di level yang sama
        $this->assertTrue(count($data) === 5);
    }

    /**
     * Test show folder items nested
     */
    public function test_show_items_with_nested()
    {
        // Buat root folder dulu
        $roots = Folder::factory(5)->create();

        // Buat dummy children folder level 1
        $children = Folder::factory(3)->create([
            'parent_id' => $roots->last()->id
        ]);

        // Buat dummy children folder level 2
        Folder::factory(5)->create([
            'parent_id' => $children->first()->id
        ]);

        $data = $this->service->showItems($roots->last()->id, true);

        // Harusnya return 8 karena yang diambil semua children sampai level terbawah
        $this->assertTrue(count($data) === 8);
    }
}
