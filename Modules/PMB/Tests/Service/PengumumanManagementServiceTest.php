<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\PengumumanManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Pengumuman;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PengumumanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PengumumanManagementService
     */
    protected $service = PengumumanManagementService::class;
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
        $this->indexReturnData(new Pengumuman);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Pengumuman, 'judul_pengumuman');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Pengumuman);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Pengumuman);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Pengumuman);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Pengumuman, [
            [
                'key' => 'judul_pengumuman',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Pengumuman);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Pengumuman);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Pengumuman);
    }
}
