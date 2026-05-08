<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\SukuManagementService;
use Tests\ServiceTestCase;
use Modules\Gate\Models\User;
use Modules\Core\Models\Suku;
use Tests\Traits\ManagementServiceTest;

class SukuManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = SukuManagementService::class;
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
        $this->indexReturnData(new Suku);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Suku, 'nama_suku');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Suku);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Suku);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Suku);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Suku, [
            [
                'key' => 'nama_suku',
                'val' => fake()->unique()->word()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Suku);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Suku);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Suku);
    }
}
