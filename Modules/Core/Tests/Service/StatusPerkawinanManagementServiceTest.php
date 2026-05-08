<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\StatusPerkawinanManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\StatusPerkawinan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class StatusPerkawinanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var StatusPerkawinanManagementService
     */
    protected $service = StatusPerkawinanManagementService::class;
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
        $this->indexReturnData(new StatusPerkawinan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new StatusPerkawinan, 'nama_status_perkawinan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new StatusPerkawinan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new StatusPerkawinan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new StatusPerkawinan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new StatusPerkawinan, [
            [
                'key' => 'nama_status_perkawinan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new StatusPerkawinan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new StatusPerkawinan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new StatusPerkawinan);
    }
}
