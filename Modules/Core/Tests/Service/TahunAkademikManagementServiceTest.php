<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Models\TahunAkademik;
use Modules\Core\Services\TahunAkademikManagementService;
use Modules\Gate\Models\User;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class TahunAkademikManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var TahunAkademikManagementService
     */
    protected $service = TahunAkademikManagementService::class;
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
        $this->indexReturnData(new TahunAkademik);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new TahunAkademik, 'tahun', null, '=');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new TahunAkademik);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new TahunAkademik);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new TahunAkademik);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new TahunAkademik, [
            [
                'key' => 'tahun',
                'val' => fake()->name(),
            ],
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new TahunAkademik);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new TahunAkademik);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new TahunAkademik);
    }
}
