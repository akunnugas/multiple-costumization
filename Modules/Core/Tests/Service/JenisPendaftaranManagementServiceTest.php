<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\JenisPendaftaran;
use Modules\Core\Services\JenisPendaftaranManagementService;
use Modules\Gate\Models\User;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisPendaftaranManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = JenisPendaftaranManagementService::class;
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
        $this->indexReturnData(new JenisPendaftaran);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisPendaftaran, 'nama_jenis_pendaftaran');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisPendaftaran);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisPendaftaran);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisPendaftaran);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisPendaftaran, [
            [
                'key' => 'nama_jenis_pendaftaran',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisPendaftaran);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisPendaftaran);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisPendaftaran);
    }
}
