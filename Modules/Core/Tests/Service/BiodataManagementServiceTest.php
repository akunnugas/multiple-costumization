<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\Biodata;
use Modules\Core\Services\BiodataManagementService;
use Modules\Gate\Models\User;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class BiodataManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = BiodataManagementService::class;
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
        $this->indexReturnData(new Biodata);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Biodata, 'nama');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Biodata);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Biodata);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Biodata);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Biodata, [
            [
                'key' => 'nama',
                'val' => fake()->name(),
            ],
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Biodata);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Biodata);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Biodata);
    }
}
