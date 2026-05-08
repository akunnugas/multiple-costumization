<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\LembagaAkreditasiManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\LembagaAkreditasi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class LembagaAkreditasiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = LembagaAkreditasiManagementService::class;
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
        $this->indexReturnData(new LembagaAkreditasi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new LembagaAkreditasi, 'nama_lembaga');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new LembagaAkreditasi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new LembagaAkreditasi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new LembagaAkreditasi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new LembagaAkreditasi, [
            [
                'key' => 'nama_lembaga',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new LembagaAkreditasi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new LembagaAkreditasi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new LembagaAkreditasi);
    }
}
