<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SyaratJenisManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SyaratJenis;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SyaratJenisManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SyaratJenisManagementService
     */
    protected $service = SyaratJenisManagementService::class;
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
        $this->indexReturnData(new SyaratJenis);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SyaratJenis, 'nama_jenis_syarat');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SyaratJenis);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SyaratJenis);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SyaratJenis);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SyaratJenis, [
            [
                'key' => 'nama_jenis_syarat',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SyaratJenis);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SyaratJenis);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SyaratJenis);
    }
}
