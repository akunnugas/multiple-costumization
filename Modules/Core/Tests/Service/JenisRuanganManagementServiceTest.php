<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\JenisRuanganManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\JenisRuangan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisRuanganManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var JenisRuanganManagementService
     */
    protected $service = JenisRuanganManagementService::class;
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
        $this->indexReturnData(new JenisRuangan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisRuangan, 'nama_jenis_ruangan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisRuangan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisRuangan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisRuangan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisRuangan, [
            [
                'key' => 'nama_jenis_ruangan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisRuangan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisRuangan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisRuangan);
    }
}
