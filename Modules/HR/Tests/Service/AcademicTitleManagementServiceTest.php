<?php

namespace Modules\HR\Tests\Service;

use Modules\HR\Services\AcademicTitleManagementService;
use Modules\Gate\Models\User;
use Modules\HR\Models\AcademicTitle;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class AcademicTitleManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var AcademicTitleManagementService
     */
    protected $service = AcademicTitleManagementService::class;
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
        $this->indexReturnData(new AcademicTitle);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new AcademicTitle, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new AcademicTitle);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new AcademicTitle);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new AcademicTitle);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new AcademicTitle, [
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
        $this->updateDataThrowModelNotFound(new AcademicTitle);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new AcademicTitle);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new AcademicTitle);
    }
}
