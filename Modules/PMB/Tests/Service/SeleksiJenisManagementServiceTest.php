<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SeleksiJenisManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SeleksiJenis;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SeleksiJenisManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = SeleksiJenisManagementService::class;
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
        $this->indexReturnData(new SeleksiJenis);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SeleksiJenis, 'nama_jenis_seleksi');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SeleksiJenis);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SeleksiJenis);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SeleksiJenis);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SeleksiJenis, [
            [
                'key' => 'nama_jenis_seleksi',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SeleksiJenis);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SeleksiJenis);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SeleksiJenis);
    }
}
