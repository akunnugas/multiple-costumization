<?php

namespace Modules\Kerjasama\Tests\Service;

use Modules\Kerjasama\Services\PenanggungjawabManagementService;
use Modules\Gate\Models\User;
use Modules\Kerjasama\Models\Penanggungjawab;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PenanggungjawabManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenanggungjawabManagementService
     */
    protected $service = PenanggungjawabManagementService::class;
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
        $this->indexReturnData(new Penanggungjawab);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Penanggungjawab, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Penanggungjawab);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Penanggungjawab);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Penanggungjawab);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Penanggungjawab, [
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
        $this->updateDataThrowModelNotFound(new Penanggungjawab);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Penanggungjawab);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Penanggungjawab);
    }
}
