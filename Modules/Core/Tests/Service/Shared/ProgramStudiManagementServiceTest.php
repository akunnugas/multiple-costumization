<?php

namespace Modules\Core\Tests\Service\Shared;

use Modules\Core\Models\Shared\ProgramStudi;
use Modules\Core\Services\Shared\ProgramStudiManagementService;
use Modules\Gate\Models\User;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class ProgramStudiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var ProgramStudiManagementService
     */
    protected $service = ProgramStudiManagementService::class;
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
        $this->indexReturnData(new ProgramStudi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new ProgramStudi, 'nama_prodi');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new ProgramStudi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new ProgramStudi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new ProgramStudi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new ProgramStudi, [
            [
                'key' => 'nama_prodi',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new ProgramStudi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new ProgramStudi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new ProgramStudi);
    }
}
