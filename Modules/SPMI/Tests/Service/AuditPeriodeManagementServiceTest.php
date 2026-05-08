<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\AuditPeriodeManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\AuditPeriode;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class AuditPeriodeManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var AuditPeriodeManagementService
     */
    protected $service = AuditPeriodeManagementService::class;
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
        $this->indexReturnData(new AuditPeriode);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new AuditPeriode, 'year');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new AuditPeriode);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new AuditPeriode);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new AuditPeriode);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new AuditPeriode, [
            [
                'key' => 'year',
                'val' => fake()->year
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new AuditPeriode);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new AuditPeriode);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new AuditPeriode);
    }
}
