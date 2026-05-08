<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\TargetIndikatorManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\TargetIndikator;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class TargetIndikatorManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var TargetIndikatorManagementService
     */
    protected $service = TargetIndikatorManagementService::class;
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
        $this->indexReturnData(new TargetIndikator);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new TargetIndikator, 'apakah_terfinalisasi');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new TargetIndikator);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new TargetIndikator);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new TargetIndikator);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new TargetIndikator, [
            [
                'key' => 'apakah_terfinalisasi',
                'val' => fake()->year
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new TargetIndikator);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new TargetIndikator);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new TargetIndikator);
    }
}
