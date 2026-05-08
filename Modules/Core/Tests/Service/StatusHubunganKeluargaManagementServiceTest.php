<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\StatusHubunganKeluargaManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\StatusHubunganKeluarga;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class StatusHubunganKeluargaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var StatusHubunganKeluargaManagementService
     */
    protected $service = StatusHubunganKeluargaManagementService::class;
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
        $this->indexReturnData(new StatusHubunganKeluarga);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new StatusHubunganKeluarga, 'nama_status_keluarga');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new StatusHubunganKeluarga);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new StatusHubunganKeluarga);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new StatusHubunganKeluarga);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new StatusHubunganKeluarga, [
            [
                'key' => 'nama_status_keluarga',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new StatusHubunganKeluarga);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new StatusHubunganKeluarga);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new StatusHubunganKeluarga);
    }
}
