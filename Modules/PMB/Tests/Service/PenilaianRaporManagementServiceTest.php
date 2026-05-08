<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\PenilaianRaporManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\PenilaianRapor;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PenilaianRaporManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenilaianRaporManagementService
     */
    protected $service = PenilaianRaporManagementService::class;
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
        $this->indexReturnData(new PenilaianRapor);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PenilaianRapor, 'nama_penilaian');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PenilaianRapor);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PenilaianRapor);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PenilaianRapor);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PenilaianRapor, [
            [
                'key' => 'nama_penilaian',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PenilaianRapor);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PenilaianRapor);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PenilaianRapor);
    }
}
