<?php

namespace Modules\Kerjasama\Tests\Service;

use Modules\Kerjasama\Services\SasaranKinerjaManagementService;
use Modules\Gate\Models\User;
use Modules\Kerjasama\Models\SasaranKinerja;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SasaranKinerjaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SasaranKinerjaManagementService
     */
    protected $service = SasaranKinerjaManagementService::class;
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
        $this->indexReturnData(new SasaranKinerja);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SasaranKinerja, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SasaranKinerja);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SasaranKinerja);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SasaranKinerja);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SasaranKinerja, [
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
        $this->updateDataThrowModelNotFound(new SasaranKinerja);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SasaranKinerja);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SasaranKinerja);
    }
}
