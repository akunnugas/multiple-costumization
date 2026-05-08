<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\JenisPerguruanTinggiManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\JenisPerguruanTinggi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisPerguruanTinggiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = JenisPerguruanTinggiManagementService::class;
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
        $this->indexReturnData(new JenisPerguruanTinggi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisPerguruanTinggi, 'nama_jenis_pt');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisPerguruanTinggi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisPerguruanTinggi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisPerguruanTinggi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisPerguruanTinggi, [
            [
                'key' => 'nama_jenis_pt',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisPerguruanTinggi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisPerguruanTinggi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisPerguruanTinggi);
    }
}
