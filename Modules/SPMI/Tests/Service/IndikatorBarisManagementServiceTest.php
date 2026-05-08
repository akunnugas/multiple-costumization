<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\IndikatorBarisManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\IndikatorBaris;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class IndikatorBarisManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var IndikatorBarisManagementService
     */
    protected $service = IndikatorBarisManagementService::class;
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
        $this->indexReturnData(new IndikatorBaris);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new IndikatorBaris, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new IndikatorBaris);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new IndikatorBaris);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new IndikatorBaris);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new IndikatorBaris, [
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
        $this->updateDataThrowModelNotFound(new IndikatorBaris);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new IndikatorBaris);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new IndikatorBaris);
    }
}
