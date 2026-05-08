<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\JenjangPendidikan;
use Modules\Gate\Models\User;
use Modules\Core\Services\JenjangPendidikanManagementService;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenjangPendidikanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = JenjangPendidikanManagementService::class;
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
        $this->indexReturnData(new JenjangPendidikan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenjangPendidikan, 'nama_jenjang');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenjangPendidikan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenjangPendidikan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenjangPendidikan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenjangPendidikan, [
            [
                'key' => 'nama_jenjang',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenjangPendidikan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenjangPendidikan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenjangPendidikan);
    }
}
