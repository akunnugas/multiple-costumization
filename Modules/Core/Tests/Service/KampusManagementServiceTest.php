<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\KampusManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Kampus;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class KampusManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var KampusManagementService
     */
    protected $service = KampusManagementService::class;
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
        $this->indexReturnData(new Kampus);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Kampus, 'nama_kampus');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Kampus);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Kampus);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Kampus);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Kampus, [
            [
                'key' => 'nama_kampus',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Kampus);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Kampus);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Kampus);
    }
}
