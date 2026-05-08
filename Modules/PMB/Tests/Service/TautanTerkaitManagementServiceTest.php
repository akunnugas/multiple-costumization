<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\TautanTerkaitManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\TautanTerkait;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class TautanTerkaitManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var TautanTerkaitManagementService
     */
    protected $service = TautanTerkaitManagementService::class;
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
        $this->indexReturnData(new TautanTerkait);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new TautanTerkait, 'nama_tautan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new TautanTerkait);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new TautanTerkait);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new TautanTerkait);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new TautanTerkait, [
            [
                'key' => 'nama_tautan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new TautanTerkait);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new TautanTerkait);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new TautanTerkait);
    }
}
