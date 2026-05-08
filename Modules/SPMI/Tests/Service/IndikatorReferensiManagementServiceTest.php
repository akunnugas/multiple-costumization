<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\IndikatorReferensiManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\IndikatorReferensi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class IndikatorReferensiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var IndikatorReferensiManagementService
     */
    protected $service = IndikatorReferensiManagementService::class;
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
        $this->indexReturnData(new IndikatorReferensi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new IndikatorReferensi, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new IndikatorReferensi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new IndikatorReferensi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new IndikatorReferensi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new IndikatorReferensi, [
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
        $this->updateDataThrowModelNotFound(new IndikatorReferensi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new IndikatorReferensi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new IndikatorReferensi);
    }
}
