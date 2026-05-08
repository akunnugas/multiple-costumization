<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\PenilaianKlasterManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\PenilaianKlaster;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PenilaianKlasterManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenilaianKlasterManagementService
     */
    protected $service = PenilaianKlasterManagementService::class;
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
        $this->indexReturnData(new PenilaianKlaster);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PenilaianKlaster, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PenilaianKlaster);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PenilaianKlaster);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PenilaianKlaster);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PenilaianKlaster, [
            [
                'key' => 'name',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PenilaianKlaster);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PenilaianKlaster);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PenilaianKlaster);
    }
}
