<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\GedungManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\Gedung;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class GedungManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var GedungManagementService
     */
    protected $service = GedungManagementService::class;
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
        $this->indexReturnData(new Gedung);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Gedung, 'nama_gedung');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Gedung);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Gedung);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Gedung);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Gedung, [
            [
                'key' => 'nama_gedung',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Gedung);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Gedung);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Gedung);
    }
}
