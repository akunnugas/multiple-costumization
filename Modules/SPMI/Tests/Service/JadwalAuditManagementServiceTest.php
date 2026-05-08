<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\JadwalAuditManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\JadwalAudit;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JadwalAuditManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var JadwalAuditManagementService
     */
    protected $service = JadwalAuditManagementService::class;
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
        $this->indexReturnData(new JadwalAudit);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JadwalAudit, 'year');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JadwalAudit);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JadwalAudit);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JadwalAudit);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JadwalAudit, [
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
        $this->updateDataThrowModelNotFound(new JadwalAudit);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JadwalAudit);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JadwalAudit);
    }
}
