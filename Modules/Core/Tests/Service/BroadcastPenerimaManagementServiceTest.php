<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\BroadcastPenerimaManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\BroadcastPenerima;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class BroadcastPenerimaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var BroadcastPenerimaManagementService
     */
    protected $service = BroadcastPenerimaManagementService::class;
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
        $this->indexReturnData(new BroadcastPenerima);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new BroadcastPenerima, 'registration_id');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new BroadcastPenerima);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new BroadcastPenerima);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new BroadcastPenerima);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new BroadcastPenerima, [
            [
                'key' => 'registration_id',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new BroadcastPenerima);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new BroadcastPenerima);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new BroadcastPenerima);
    }
}
