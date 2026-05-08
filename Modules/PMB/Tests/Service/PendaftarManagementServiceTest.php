<?php

namespace Modules\PMB\Tests\Service;

use Modules\Gate\Models\User;
use Modules\PMB\Models\Pendaftar;
use Modules\PMB\Services\PendaftarManagementService;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PendaftarManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = PendaftarManagementService::class;
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
        $this->indexReturnData(new Pendaftar);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Pendaftar, 'id_periode_pendaftaran', null, '=');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Pendaftar);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Pendaftar);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Pendaftar);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Pendaftar, [
            [
                'key' => 'kode_pendaftar',
                'val' => fake()->name(),
            ],
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Pendaftar);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Pendaftar);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Pendaftar);
    }
}
