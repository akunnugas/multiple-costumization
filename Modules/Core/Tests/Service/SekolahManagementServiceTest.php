<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\SekolahManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Sekolah;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SekolahManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SekolahManagementService
     */
    protected $service = SekolahManagementService::class;
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
        $this->indexReturnData(new Sekolah);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Sekolah, 'nama_sekolah');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Sekolah);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Sekolah);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Sekolah);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Sekolah, [
            [
                'key' => 'nama_sekolah',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Sekolah);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Sekolah);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Sekolah);
    }
}
