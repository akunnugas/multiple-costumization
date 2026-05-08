<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\IndikatorBobotManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\IndikatorBobot;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class IndikatorBobotManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var IndikatorBobotManagementService
     */
    protected $service = IndikatorBobotManagementService::class;
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
        $this->indexReturnData(new IndikatorBobot);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new IndikatorBobot, 'nama_kategori_indikator');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new IndikatorBobot);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new IndikatorBobot);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new IndikatorBobot);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new IndikatorBobot, [
            [
                'key' => 'nama_kategori_indikator',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new IndikatorBobot);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new IndikatorBobot);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new IndikatorBobot);
    }
}
