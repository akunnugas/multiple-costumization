<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\Pekerjaan;
use Modules\Core\Services\PekerjaanManagementService;
use Tests\ServiceTestCase;
use Modules\Gate\Models\User;
use Tests\Traits\ManagementServiceTest;

class PekerjaanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = PekerjaanManagementService::class;
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
        $this->indexReturnData(new Pekerjaan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Pekerjaan, 'nama_pekerjaan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Pekerjaan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Pekerjaan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Pekerjaan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Pekerjaan, [
            [
                'key' => 'nama_pekerjaan',
                'val' => fake()->jobTitle()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Pekerjaan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Pekerjaan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Pekerjaan);
    }
}
