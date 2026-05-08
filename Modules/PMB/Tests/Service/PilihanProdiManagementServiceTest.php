<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\PilihanProdiManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\PilihanProdi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PilihanProdiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = PilihanProdiManagementService::class;
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
        $this->indexReturnData(new PilihanProdi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PilihanProdi, 'id_pendaftar', null, '=');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PilihanProdi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PilihanProdi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PilihanProdi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PilihanProdi, [
            [
                'key' => 'status_pilihan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PilihanProdi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PilihanProdi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PilihanProdi);
    }
}
