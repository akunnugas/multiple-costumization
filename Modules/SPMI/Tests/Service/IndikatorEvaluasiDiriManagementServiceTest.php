<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\IndikatorEvaluasiDiriManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class IndikatorEvaluasiDiriManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var IndikatorEvaluasiDiriManagementService
     */
    protected $service = IndikatorEvaluasiDiriManagementService::class;
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
        $this->indexReturnData(new IndikatorEvaluasiDiri);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new IndikatorEvaluasiDiri, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new IndikatorEvaluasiDiri);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new IndikatorEvaluasiDiri);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new IndikatorEvaluasiDiri);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new IndikatorEvaluasiDiri, [
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
        $this->updateDataThrowModelNotFound(new IndikatorEvaluasiDiri);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new IndikatorEvaluasiDiri);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new IndikatorEvaluasiDiri);
    }
}
