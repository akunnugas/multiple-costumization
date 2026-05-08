<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\PenghasilanManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Penghasilan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PenghasilanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenghasilanManagementService
     */
    protected $service = PenghasilanManagementService::class;
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
        $this->indexReturnData(new Penghasilan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Penghasilan, 'nama_penghasilan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Penghasilan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Penghasilan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Penghasilan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Penghasilan, [
            [
                'key' => 'nama_penghasilan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Penghasilan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Penghasilan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Penghasilan);
    }
}
