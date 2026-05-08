<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SeleksiKomponenManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SeleksiKomponen;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SeleksiKomponenManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SeleksiKomponenManagementService
     */
    protected $service = SeleksiKomponenManagementService::class;
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
        $this->indexReturnData(new SeleksiKomponen);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SeleksiKomponen, 'nama_komponen');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SeleksiKomponen);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SeleksiKomponen);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SeleksiKomponen);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SeleksiKomponen, [
            [
                'key' => 'nama_komponen',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SeleksiKomponen);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SeleksiKomponen);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SeleksiKomponen);
    }
}
