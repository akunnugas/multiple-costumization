<?php

namespace Modules\HR\Tests\Service;

use Modules\HR\Services\PositionLevelManagementService;
use Modules\Gate\Models\User;
use Modules\HR\Models\PositionLevel;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PositionLevelManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PositionLevelManagementService
     */
    protected $service = PositionLevelManagementService::class;
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
        $this->indexReturnData(new PositionLevel);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PositionLevel, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PositionLevel);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PositionLevel);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PositionLevel);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PositionLevel, [
            [
                'key' => 'name',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PositionLevel);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PositionLevel);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PositionLevel);
    }
}
