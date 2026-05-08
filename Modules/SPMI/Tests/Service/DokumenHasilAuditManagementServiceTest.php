<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\DokumenHasilAuditManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\DokumenHasilAudit;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class DokumenHasilAuditManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var DokumenHasilAuditManagementService
     */
    protected $service = DokumenHasilAuditManagementService::class;
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
        $this->indexReturnData(new DokumenHasilAudit);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new DokumenHasilAudit, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new DokumenHasilAudit);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new DokumenHasilAudit);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new DokumenHasilAudit);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new DokumenHasilAudit, [
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
        $this->updateDataThrowModelNotFound(new DokumenHasilAudit);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new DokumenHasilAudit);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new DokumenHasilAudit);
    }
}
