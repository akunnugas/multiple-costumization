<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\BankManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Bank;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class BankManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = BankManagementService::class;
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
        $this->indexReturnData(new Bank);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Bank, 'nama_bank');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Bank);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Bank);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Bank);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Bank, [
            [
                'key' => 'nama_bank',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Bank);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Bank);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Bank);
    }
}
