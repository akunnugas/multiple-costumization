<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SyaratPendaftaranManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SyaratPendaftaran;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SyaratPendaftaranManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SyaratPendaftaranManagementService
     */
    protected $service = SyaratPendaftaranManagementService::class;
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
        $this->indexReturnData(new SyaratPendaftaran);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SyaratPendaftaran, 'apakah_upload', true, '=');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SyaratPendaftaran);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SyaratPendaftaran);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SyaratPendaftaran);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SyaratPendaftaran, [
            [
                'key' => 'jumlah_dokumen',
                'val' => fake()->randomDigit()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SyaratPendaftaran);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SyaratPendaftaran);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SyaratPendaftaran);
    }
}
