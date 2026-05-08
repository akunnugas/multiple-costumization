<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\PerguruanTinggi;
use Tests\ServiceTestCase;
use Modules\Gate\Models\User;
use Modules\Core\Services\PerguruanTinggiManagementService;
use Tests\Traits\ManagementServiceTest;

class PerguruanTinggiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = PerguruanTinggiManagementService::class;
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
        $this->indexReturnData(new PerguruanTinggi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PerguruanTinggi, 'nama_pt');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PerguruanTinggi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PerguruanTinggi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PerguruanTinggi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PerguruanTinggi, [
            [
                'key' => 'nama_pt',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PerguruanTinggi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PerguruanTinggi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PerguruanTinggi);
    }
}
