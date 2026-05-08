<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\KeluargaManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Keluarga;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class KeluargaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var KeluargaManagementService
     */
    protected $service = KeluargaManagementService::class;
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
        $this->indexReturnData(new Keluarga);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Keluarga, 'nama');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Keluarga);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Keluarga);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Keluarga);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Keluarga, [
            [
                'key' => 'nama',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Keluarga);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Keluarga);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Keluarga);
    }
}
