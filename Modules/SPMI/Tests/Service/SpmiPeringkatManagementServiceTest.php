<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\SpmiPeringkatManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\SpmiPeringkat;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SpmiPeringkatManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SpmiPeringkatManagementService
     */
    protected $service = SpmiPeringkatManagementService::class;
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
        $this->indexReturnData(new SpmiPeringkat);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SpmiPeringkat, 'nama_spmi_peringkat');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SpmiPeringkat);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SpmiPeringkat);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SpmiPeringkat);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SpmiPeringkat, [
            [
                'key' => 'nama_spmi_peringkat',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SpmiPeringkat);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SpmiPeringkat);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SpmiPeringkat);
    }
}
