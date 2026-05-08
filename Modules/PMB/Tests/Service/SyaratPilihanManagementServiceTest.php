<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SyaratPilihanManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SyaratPilihan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SyaratPilihanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SyaratPilihanManagementService
     */
    protected $service = SyaratPilihanManagementService::class;
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
        $this->indexReturnData(new SyaratPilihan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SyaratPilihan, 'nama_pilihan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SyaratPilihan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SyaratPilihan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SyaratPilihan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SyaratPilihan, [
            [
                'key' => 'nama_pilihan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SyaratPilihan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SyaratPilihan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SyaratPilihan);
    }
}
