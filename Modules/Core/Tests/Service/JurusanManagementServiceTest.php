<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\Jurusan;
use Modules\Gate\Models\User;
use Modules\Core\Services\JurusanManagementService;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JurusanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = JurusanManagementService::class;
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
        $this->indexReturnData(new Jurusan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Jurusan, 'nama_jurusan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Jurusan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Jurusan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Jurusan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Jurusan, [
            [
                'key' => 'nama_jurusan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Jurusan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Jurusan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Jurusan);
    }
}
