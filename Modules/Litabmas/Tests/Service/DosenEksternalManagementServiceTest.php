<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\DosenEksternalManagementService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\DosenEksternal;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class DosenEksternalManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var DosenEksternalManagementService
     */
    protected $service = DosenEksternalManagementService::class;
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
        $this->indexReturnData(new DosenEksternal);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new DosenEksternal, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new DosenEksternal);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new DosenEksternal);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new DosenEksternal);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new DosenEksternal, [
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
        $this->updateDataThrowModelNotFound(new DosenEksternal);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new DosenEksternal);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new DosenEksternal);
    }
}
