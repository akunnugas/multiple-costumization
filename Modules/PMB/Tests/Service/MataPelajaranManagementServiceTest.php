<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\MataPelajaranManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\MataPelajaran;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class MataPelajaranManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var MataPelajaranManagementService
     */
    protected $service = MataPelajaranManagementService::class;
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
        $this->indexReturnData(new MataPelajaran);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new MataPelajaran, 'nama_mata_pelajaran');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new MataPelajaran);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new MataPelajaran);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new MataPelajaran);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new MataPelajaran, [
            [
                'key' => 'nama_mata_pelajaran',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new MataPelajaran);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new MataPelajaran);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new MataPelajaran);
    }
}
