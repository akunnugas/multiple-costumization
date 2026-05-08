<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\RuanganManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Ruangan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class RuanganManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var RuanganManagementService
     */
    protected $service = RuanganManagementService::class;
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
        $this->indexReturnData(new Ruangan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Ruangan, 'nama_ruangan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Ruangan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Ruangan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Ruangan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Ruangan, [
            [
                'key' => 'nama_ruangan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Ruangan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Ruangan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Ruangan);
    }
}
