<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\PenilaianMatriksManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\PenilaianMatriks;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PenilaianMatriksManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenilaianMatriksManagementService
     */
    protected $service = PenilaianMatriksManagementService::class;
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
        $this->indexReturnData(new PenilaianMatriks);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PenilaianMatriks, 'pertanyaan_penilaian');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PenilaianMatriks);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PenilaianMatriks);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PenilaianMatriks);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PenilaianMatriks, [
            [
                'key' => 'pertanyaan_penilaian',
                'val' => fake()->randomElement([
                    'IN', 'PR', 'TL', 'SA'
                ])
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PenilaianMatriks);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PenilaianMatriks);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PenilaianMatriks);
    }
}
