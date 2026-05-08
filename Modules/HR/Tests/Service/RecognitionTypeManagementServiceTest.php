<?php

namespace Modules\HR\Tests\Service;

use Modules\HR\Services\RecognitionTypeManagementService;
use Modules\Gate\Models\User;
use Modules\HR\Models\RecognitionType;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class RecognitionTypeManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = RecognitionTypeManagementService::class;
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
        $this->indexReturnData(new RecognitionType);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new RecognitionType, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new RecognitionType);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new RecognitionType);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new RecognitionType);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new RecognitionType, [
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
        $this->updateDataThrowModelNotFound(new RecognitionType);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new RecognitionType);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new RecognitionType);
    }
}
