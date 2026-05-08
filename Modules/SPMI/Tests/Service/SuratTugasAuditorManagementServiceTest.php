<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\SuratTugasAuditorManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\SuratTugasAuditor;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SuratTugasAuditorManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SuratTugasAuditorManagementService
     */
    protected $service = SuratTugasAuditorManagementService::class;
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
        $this->indexReturnData(new SuratTugasAuditor);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SuratTugasAuditor, 'nomor_surat_tugas');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SuratTugasAuditor);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SuratTugasAuditor);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SuratTugasAuditor);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SuratTugasAuditor, [
            [
                'key' => 'nomor_surat_tugas',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SuratTugasAuditor);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SuratTugasAuditor);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SuratTugasAuditor);
    }
}
