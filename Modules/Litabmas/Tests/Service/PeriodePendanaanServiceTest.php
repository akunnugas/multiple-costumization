<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\PeriodePendanaanService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\PeriodePendanaan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PeriodePendanaanServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PeriodePendanaanService
     */
    protected $service = PeriodePendanaanService::class;
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
        $this->indexReturnData(new PeriodePendanaan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PeriodePendanaan, 'periode');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PeriodePendanaan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PeriodePendanaan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PeriodePendanaan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PeriodePendanaan, [
            [
                'key' => 'periode',
                'val' => fake()->year()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PeriodePendanaan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PeriodePendanaan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PeriodePendanaan);
    }
}
