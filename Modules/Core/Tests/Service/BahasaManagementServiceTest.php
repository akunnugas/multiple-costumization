<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\BahasaManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Bahasa;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class BahasaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var BahasaManagementService
     */
    protected $service = BahasaManagementService::class;
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
        $this->indexReturnData(new Bahasa);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Bahasa, 'nama_bahasa');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Bahasa);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Bahasa);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Bahasa);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Bahasa, [
            [
                'key' => 'nama_bahasa',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Bahasa);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Bahasa);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Bahasa);
    }
}
