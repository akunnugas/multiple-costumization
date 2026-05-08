<?php

namespace Modules\Kerjasama\Tests\Service;

use Modules\Kerjasama\Services\KontakManagementService;
use Modules\Gate\Models\User;
use Modules\Kerjasama\Models\Kontak;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class KontakManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var KontakManagementService
     */
    protected $service = KontakManagementService::class;
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
        $this->indexReturnData(new Kontak);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Kontak, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Kontak);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Kontak);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Kontak);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Kontak, [
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
        $this->updateDataThrowModelNotFound(new Kontak);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Kontak);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Kontak);
    }
}
