<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\IndikatorLaporanKinerjaManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class IndikatorLaporanKinerjaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = IndikatorLaporanKinerjaManagementService::class;
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
        $this->indexReturnData(new IndikatorLaporanKinerja);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new IndikatorLaporanKinerja);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new IndikatorLaporanKinerja);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new IndikatorLaporanKinerja);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new IndikatorLaporanKinerja, [
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
        $this->updateDataThrowModelNotFound(new IndikatorLaporanKinerja);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new IndikatorLaporanKinerja);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new IndikatorLaporanKinerja);
    }

    

}
