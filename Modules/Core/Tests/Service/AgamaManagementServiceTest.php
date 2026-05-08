<?php

namespace Modules\Core\Tests\Service;

use Tests\ServiceTestCase;
use Modules\Gate\Models\User;
use Modules\Core\Services\AgamaManagementService;
use Modules\Core\Models\Agama;
use Tests\Traits\ManagementServiceTest;

class AgamaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = AgamaManagementService::class;
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
        $this->indexReturnData(new Agama);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Agama, 'nama_agama');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Agama);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Agama);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Agama);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Agama, [
            [
                'key' => 'nama_agama',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Agama);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Agama);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Agama);
    }
}
