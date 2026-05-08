<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\AkreditasiPeringkatManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class AkreditasiPeringkatManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var AkreditasiPeringkatManagementService
     */
    protected $service = AkreditasiPeringkatManagementService::class;
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
        $this->indexReturnData(new AkreditasiPeringkat);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new AkreditasiPeringkat, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new AkreditasiPeringkat);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new AkreditasiPeringkat);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new AkreditasiPeringkat);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new AkreditasiPeringkat, [
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
        $this->updateDataThrowModelNotFound(new AkreditasiPeringkat);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new AkreditasiPeringkat);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new AkreditasiPeringkat);
    }
}
