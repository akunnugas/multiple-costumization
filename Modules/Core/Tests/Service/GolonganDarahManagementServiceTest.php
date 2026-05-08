<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\GolonganDarahManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\GolonganDarah;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class GolonganDarahManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var GolonganDarahManagementService
     */
    protected $service = GolonganDarahManagementService::class;
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
        $this->indexReturnData(new GolonganDarah);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new GolonganDarah, 'kode_golongan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new GolonganDarah);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new GolonganDarah);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new GolonganDarah);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new GolonganDarah, [
            [
                'key' => 'kode_golongan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new GolonganDarah);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new GolonganDarah);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new GolonganDarah);
    }
}
