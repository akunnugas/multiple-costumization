<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\PegawaiManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Pegawai;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PegawaiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PegawaiManagementService
     */
    protected $service = PegawaiManagementService::class;
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
        $this->indexReturnData(new Pegawai);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Pegawai, 'nama');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Pegawai);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Pegawai);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Pegawai);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Pegawai, [
            [
                'key' => 'nama',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Pegawai);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Pegawai);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Pegawai);
    }
}
