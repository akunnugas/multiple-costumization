<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\KontenManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Konten;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class KontenManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var KontenManagementService
     */
    protected $service = KontenManagementService::class;
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
        $this->indexReturnData(new Konten);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Konten, 'judul_konten');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Konten);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Konten);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Konten);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Konten, [
            [
                'key' => 'judul_konten',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Konten);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Konten);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Konten);
    }
}
