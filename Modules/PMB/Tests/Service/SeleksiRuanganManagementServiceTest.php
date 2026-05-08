<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SeleksiRuanganManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SeleksiRuangan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SeleksiRuanganManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SeleksiRuanganManagementService
     */
    protected $service = SeleksiRuanganManagementService::class;
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
        $this->indexReturnData(new SeleksiRuangan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SeleksiRuangan, 'nama_ruangan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SeleksiRuangan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SeleksiRuangan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SeleksiRuangan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SeleksiRuangan, [
            [
                'key' => 'nama_ruangan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SeleksiRuangan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SeleksiRuangan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SeleksiRuangan);
    }
}
