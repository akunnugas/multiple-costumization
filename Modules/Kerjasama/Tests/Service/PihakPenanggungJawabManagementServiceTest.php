<?php

namespace Modules\Kerjasama\Tests\Service;

use Modules\Kerjasama\Services\PihakPenanggungJawabManagementService;
use Modules\Gate\Models\User;
use Modules\Kerjasama\Models\PihakPenanggungJawab;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PihakPenanggungJawabManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PihakPenanggungJawabManagementService
     */
    protected $service = PihakPenanggungJawabManagementService::class;
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
        $this->indexReturnData(new PihakPenanggungJawab);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PihakPenanggungJawab, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PihakPenanggungJawab);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PihakPenanggungJawab);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PihakPenanggungJawab);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PihakPenanggungJawab, [
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
        $this->updateDataThrowModelNotFound(new PihakPenanggungJawab);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PihakPenanggungJawab);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PihakPenanggungJawab);
    }
}
