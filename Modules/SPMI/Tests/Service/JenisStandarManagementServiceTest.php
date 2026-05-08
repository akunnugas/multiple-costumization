<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\JenisStandarManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\JenisStandar;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisStandarManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = JenisStandarManagementService::class;
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
        $this->indexReturnData(new JenisStandar);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisStandar, 'nama_jenis_standar');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisStandar);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisStandar);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisStandar);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisStandar, [
            [
                'key' => 'nama_jenis_standar',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisStandar);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisStandar);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisStandar);
    }
}
