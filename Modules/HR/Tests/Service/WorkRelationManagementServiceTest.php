<?php

namespace Modules\HR\Tests\Service;

use Modules\HR\Services\WorkRelationManagementService;
use Modules\Gate\Models\User;
use Modules\HR\Models\WorkRelation;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class WorkRelationManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var WorkRelationManagementService
     */
    protected $service = WorkRelationManagementService::class;
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
        $this->indexReturnData(new WorkRelation);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new WorkRelation, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new WorkRelation);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new WorkRelation);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new WorkRelation);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new WorkRelation, [
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
        $this->updateDataThrowModelNotFound(new WorkRelation);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new WorkRelation);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new WorkRelation);
    }
}
