<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\LogODSManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\LogODS;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class LogODSManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = LogODSManagementService::class;
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
        $this->indexReturnData(new LogODS);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new LogODS, 'keterangan_log');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new LogODS);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new LogODS);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new LogODS);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new LogODS, [
            [
                'key' => 'keterangan_log',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new LogODS);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new LogODS);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new LogODS);
    }
}
