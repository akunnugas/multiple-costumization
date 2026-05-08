<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\IndikatorCellManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\IndikatorCell;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class IndikatorCellManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var IndikatorCellManagementService
     */
    protected $service = IndikatorCellManagementService::class;
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
        $this->indexReturnData(new IndikatorCell);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new IndikatorCell, 'nama');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new IndikatorCell);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new IndikatorCell);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new IndikatorCell);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new IndikatorCell, [
            [
                'key' => 'nama',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new IndikatorCell);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new IndikatorCell);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new IndikatorCell);
    }
}
