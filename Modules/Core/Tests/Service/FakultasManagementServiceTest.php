<?php

namespace Modules\Core\Tests\Service;

use Tests\ServiceTestCase;
use Modules\Gate\Models\User;
use Modules\Core\Services\FakultasManagementService;
use Modules\Core\Models\Fakultas;
use Tests\Traits\ManagementServiceTest;

class FakultasManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = FakultasManagementService::class;
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
        $this->indexReturnData(new Fakultas);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Fakultas, 'nama_fakultas');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Fakultas);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Fakultas);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Fakultas);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Fakultas, [
            [
                'key' => 'nama_fakultas',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Fakultas);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Fakultas);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Fakultas);
    }
}
