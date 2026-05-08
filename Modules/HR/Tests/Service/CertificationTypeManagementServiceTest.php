<?php

namespace Modules\HR\Tests\Service;

use Modules\HR\Services\CertificationTypeManagementService;
use Modules\Gate\Models\User;
use Modules\HR\Models\CertificationType;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class CertificationTypeManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = CertificationTypeManagementService::class;
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
        $this->indexReturnData(new CertificationType);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new CertificationType, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new CertificationType);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new CertificationType);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new CertificationType);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new CertificationType, [
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
        $this->updateDataThrowModelNotFound(new CertificationType);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new CertificationType);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new CertificationType);
    }
}
